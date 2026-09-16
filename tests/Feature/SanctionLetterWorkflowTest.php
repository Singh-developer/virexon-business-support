<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{Role, User, SanctionLetter, SanctionLetterUpload};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SanctionLetterWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Role $agentRole;
    private Role $adminRole;
    private User $agent;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agentRole = Role::create(['name' => 'Agent', 'slug' => 'agent']);
        $this->adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin']);

        $this->agent = User::factory()->create(['role_id' => $this->agentRole->id]);
        $this->admin = User::factory()->create(['role_id' => $this->adminRole->id]);
    }

    private function makeLetter(User $agent, array $overrides = []): SanctionLetter
    {
        Storage::fake('public');
        Storage::fake('local');

        $pdf = UploadedFile::fake()->create('letter.pdf', 100, 'application/pdf');
        $path = Storage::disk('public')->putFile('sanction-letters', $pdf);

        return SanctionLetter::create(array_merge([
            'user_id'         => $agent->id,
            'sanction_number' => 101,
            'title'           => 'Sanction Letter',
            'subject'         => 'Notice of sanction',
            'greeting'        => 'Dear Agent,',
            'body'            => 'Body text',
            'closing'         => 'Sincerely,',
            'pdf_path'        => $path,
            'status'          => 'sent',
            'sent_at'         => now(),
        ], $overrides));
    }

    public function test_agent_index_and_show_list_only_own_letters(): void
    {
        $letter = $this->makeLetter($this->agent);

        $this->actingAs($this->agent)
            ->get(route('agent.sanctions.index'))
            ->assertOk()
            ->assertSee('Notice of sanction');

        $this->actingAs($this->agent)
            ->get(route('agent.sanctions.show', $letter))
            ->assertOk()
            ->assertSee('Notice of sanction');

        $other = User::factory()->create(['role_id' => $this->agentRole->id]);
        $this->actingAs($other)
            ->get(route('agent.sanctions.show', $letter))
            ->assertForbidden();
    }

    public function test_agent_download_marks_letter_as_downloaded(): void
    {
        $letter = $this->makeLetter($this->agent);

        $this->actingAs($this->agent)
            ->get(route('agent.sanctions.download', $letter))
            ->assertOk();

        $this->assertNotNull($letter->fresh()->downloaded_at);
    }

    public function test_agent_upload_puts_letter_under_review_and_notifies_admins(): void
    {
        $letter = $this->makeLetter($this->agent);

        $signed = UploadedFile::fake()->create('signed-letter.pdf', 500, 'application/pdf');

        $this->actingAs($this->agent)
            ->post(route('agent.sanctions.upload', $letter), ['signed_pdf' => $signed])
            ->assertSessionHas('success');

        $letter->refresh();

        $this->assertSame('under_review', $letter->review_status);
        $this->assertSame(1, $letter->signed_pdf_upload_count);
        $this->assertNotNull($letter->signed_pdf_path);
        $this->assertNotNull($letter->signed_pdf_uploaded_at);
        $this->assertNull($letter->reviewed_at);
        $this->assertNull($letter->review_comment);

        $this->assertDatabaseHas('sanction_letter_uploads', [
            'sanction_letter_id' => $letter->id,
            'uploaded_by'        => $this->agent->id,
            'original_name'      => 'signed-letter.pdf',
        ]);

        $this->assertSame(1, $this->admin->notifications()->count());
    }

    public function test_agent_cannot_reupload_while_under_review(): void
    {
        $letter = $this->makeLetter($this->agent);

        $this->actingAs($this->agent)->post(route('agent.sanctions.upload', $letter), [
            'signed_pdf' => UploadedFile::fake()->create('one.pdf', 500, 'application/pdf'),
        ]);

        $this->actingAs($this->agent)->post(route('agent.sanctions.upload', $letter), [
            'signed_pdf' => UploadedFile::fake()->create('two.pdf', 500, 'application/pdf'),
        ])->assertSessionHas('error', 'Upload is not available for this letter right now.');

        $this->assertSame(1, $letter->fresh()->signed_pdf_upload_count);
    }

    public function test_admin_approval_completes_workflow_and_notifies_agent(): void
    {
        $letter = $this->makeLetter($this->agent);
        $this->actingAs($this->agent)->post(route('agent.sanctions.upload', $letter), [
            'signed_pdf' => UploadedFile::fake()->create('signed.pdf', 500, 'application/pdf'),
        ]);

        $letter->refresh();

        $this->actingAs($this->admin)
            ->from(route('sanctions.review', $letter))
            ->post(route('sanctions.approve', $letter), ['review_comment' => 'Looks good.'])
            ->assertSessionHas('success');

        $letter->refresh();

        $this->assertSame('approved', $letter->review_status);
        $this->assertNotNull($letter->reviewed_at);
        $this->assertSame($this->admin->id, $letter->reviewed_by);
        $this->assertSame('Looks good.', $letter->review_comment);
        $this->assertSame('approved', $letter->workflowStatus()['key']);
        $this->assertFalse($letter->canUpload());

        $this->assertSame(1, $this->agent->notifications()->count());
        $this->assertStringContainsString('Approved', $this->agent->notifications()->first()->data['message']);
    }

    public function test_reupload_loop_until_approved(): void
    {
        $letter = $this->makeLetter($this->agent);

        $this->actingAs($this->agent)->post(route('agent.sanctions.upload', $letter), [
            'signed_pdf' => UploadedFile::fake()->create('first.pdf', 500, 'application/pdf'),
        ]);
        $letter->refresh();

        // Admin rejects first attempt and requests a re-upload with a reason.
        $this->actingAs($this->admin)
            ->post(route('sanctions.reupload-required', $letter), ['review_comment' => 'Signature blurred'])
            ->assertSessionHas('success');

        $letter->refresh();
        $this->assertSame('reupload_required', $letter->review_status);
        $this->assertTrue($letter->canUpload());
        $this->assertSame(1, $this->agent->notifications()->count());

        // Agent uploads again.
        $this->actingAs($this->agent)
            ->from(route('agent.sanctions.show', $letter))
            ->post(route('agent.sanctions.upload', $letter), [
                'signed_pdf' => UploadedFile::fake()->create('second.pdf', 500, 'application/pdf'),
            ])
            ->assertSessionHas('success');

        $letter->refresh();
        $this->assertSame('under_review', $letter->review_status);
        $this->assertSame(2, $letter->signed_pdf_upload_count);
        $this->assertNull($letter->review_comment);
        $this->assertSame(2, SanctionLetterUpload::where('sanction_letter_id', $letter->id)->count());

        // Admin approves the second upload.
        $this->actingAs($this->admin)->post(route('sanctions.approve', $letter));
        $this->assertSame('approved', $letter->fresh()->review_status);
    }

    public function test_admin_index_with_filter_and_review_page_render(): void
    {
        $letter = $this->makeLetter($this->agent);
        $this->actingAs($this->agent)->post(route('agent.sanctions.upload', $letter), [
            'signed_pdf' => UploadedFile::fake()->create('signed.pdf', 500, 'application/pdf'),
        ]);

        $this->actingAs($this->admin)
            ->get(route('sanctions.index'))
            ->assertOk()
            ->assertSee($letter->sanction_letter_no);

        $this->actingAs($this->admin)
            ->get(route('sanctions.index', ['status' => 'under_review']))
            ->assertOk();

        $this->actingAs($this->admin)
            ->get(route('sanctions.review', $letter))
            ->assertOk();

        $letter->refresh();
        $this->actingAs($this->admin)
            ->get(route('sanctions.index', ['status' => 'approved']))
            ->assertOk()
            ->assertSee('Approved');
    }

    public function test_signed_pdf_preview_is_served_inline(): void
    {
        $letter = $this->makeLetter($this->agent);
        $this->actingAs($this->agent)->post(route('agent.sanctions.upload', $letter), [
            'signed_pdf' => UploadedFile::fake()->create('signed.pdf', 500, 'application/pdf'),
        ]);

        $upload = SanctionLetterUpload::first();

        $this->actingAs($this->agent)
            ->get(route('agent.sanctions.signed-pdf', $letter))
            ->assertOk();

        $this->actingAs($this->admin)
            ->get(route('sanctions.signed-pdf', [$letter, $upload]))
            ->assertOk();

        $this->actingAs($this->admin)
            ->get(route('sanctions.signed-download', [$letter, $upload]))
            ->assertOk();
    }

    public function test_guardian_relation_dropdown_is_persisted_and_rendered_in_pdf(): void
    {
        $payload = [
            'user_id'                 => $this->agent->id,
            'title'                   => 'Sanction Letter',
            'subject'                 => 'Subject line',
            'greeting'                => 'Dear Agent,',
            'body'                    => 'Body text',
            'closing'                 => 'Yours faithfully,',
            'signature_name'          => 'Admin',
            'signature_designation'   => 'Manager',
            'approved_amount'         => 100000,
            'disbursement_mode'       => 'Bank Transfer',
            'process_fee'             => 500,
            'tenure'                  => 12,
            'monthly_principal_settlement'     => 8334,
            'monthly_portal_service_charges'   => 200,
            'sanction_date'           => now()->format('Y-m-d'),
            'letter_address'          => 'Test Address',
            'guardian_relation'       => 'W/o',
        ];

        $this->actingAs($this->admin)
            ->post(route('sanctions.preview'), $payload)
            ->assertOk();

        $this->actingAs($this->admin)
            ->post(route('sanctions.store'), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('sanction_letters', ['user_id' => $this->agent->id]);
        $letter = SanctionLetter::where('user_id', $this->agent->id)->first();
        $this->assertSame('W/o', $letter->dynamic_fields['guardian_relation']);
    }
}
