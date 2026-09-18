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

    public function test_workflow_status_follows_explicit_review_status(): void
    {
        // Admin sets back to pending after an upload: badge must show Pending,
        // not stay stuck on Under Review.
        $letter = $this->makeLetter($this->agent, [
            'review_status' => 'pending',
            'signed_pdf_upload_count' => 1,
            'signed_pdf_uploaded_at' => now(),
        ]);
        $this->assertSame('pending', $letter->workflowStatus()['key']);

        // Admin sets to Under Review with no upload yet: badge must show it.
        $letter = $this->makeLetter($this->agent, array_merge(
            ['sanction_number' => 102],
            ['review_status' => 'under_review', 'signed_pdf_upload_count' => 0]
        ));
        $this->assertSame('under_review', $letter->workflowStatus()['key']);

        // End-to-end: admin flips under_review -> pending, agent page shows Pending.
        $letter = $this->makeLetter($this->agent, ['sanction_number' => 103]);
        $this->actingAs($this->agent)->post(route('agent.sanctions.upload', $letter), [
            'signed_pdf' => UploadedFile::fake()->create('signed.pdf', 500, 'application/pdf'),
        ]);
        $this->assertSame('under_review', $letter->fresh()->review_status);

        $this->actingAs($this->admin)->post(route('sanctions.status', $letter), [
            'review_status' => 'pending',
        ])->assertSessionHas('success');

        $this->assertSame('pending', $letter->fresh()->workflowStatus()['key']);
        // Resetting to pending revokes the review, so reviewed_at/by are cleared
        // and the "Reviewed" progress step goes back to incomplete.
        $this->assertNull($letter->fresh()->reviewed_at);
        $this->assertNull($letter->fresh()->reviewed_by);
        $this->actingAs($this->agent)
            ->get(route('agent.sanctions.index'))
            ->assertOk()
            ->assertSee('Pending');
    }

    public function test_reviewed_step_is_grey_when_status_is_pending(): void
    {
        // Legacy row: review happened once (reviewed_at set) but status is back
        // to pending — the "Reviewed" progress pill must render incomplete.
        $letter = $this->makeLetter($this->agent, [
            'review_status' => 'pending',
            'signed_pdf_upload_count' => 1,
            'signed_pdf_uploaded_at' => now(),
            'signed_pdf_path' => 'signed-sanction-letters/old.pdf',
            'reviewed_at' => now(),
            'reviewed_by' => $this->admin->id,
        ]);

        $html = $this->actingAs($this->agent)
            ->get(route('agent.sanctions.show', $letter))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/bg-slate-50 text-slate-400 border border-slate-200[^>]*>\s*⚖ Reviewed/s',
            $html
        );
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

    public function test_agent_can_upload_multiple_files_in_one_go(): void
    {
        $letter = $this->makeLetter($this->agent);

        $this->actingAs($this->agent)->post(route('agent.sanctions.upload', $letter), [
            'signed_pdf' => [
                UploadedFile::fake()->create('page1.pdf', 500, 'application/pdf'),
                UploadedFile::fake()->create('page2.pdf', 500, 'application/pdf'),
                UploadedFile::fake()->image('scan.jpg', 800, 600),
            ],
        ])->assertSessionHas('success');

        $letter->refresh();

        $this->assertSame('under_review', $letter->review_status);
        $this->assertSame(3, $letter->signed_pdf_upload_count);
        $this->assertSame(3, SanctionLetterUpload::where('sanction_letter_id', $letter->id)->count());
        $this->assertNotNull($letter->signed_pdf_path);

        // More than 5 files in one upload is rejected and stores nothing.
        $letter2 = $this->makeLetter($this->agent, ['sanction_number' => 104]);
        $this->actingAs($this->agent)->post(route('agent.sanctions.upload', $letter2), [
            'signed_pdf' => array_map(
                fn($i) => UploadedFile::fake()->create("p{$i}.pdf", 100, 'application/pdf'),
                range(1, 6)
            ),
        ])->assertSessionHasErrors('signed_pdf');

        $this->assertSame(0, SanctionLetterUpload::where('sanction_letter_id', $letter2->id)->count());
        $this->assertSame(0, $letter2->fresh()->signed_pdf_upload_count ?? 0);
    }

    public function test_agent_can_preview_each_uploaded_file(): void
    {
        $letter = $this->makeLetter($this->agent);

        $this->actingAs($this->agent)->post(route('agent.sanctions.upload', $letter), [
            'signed_pdf' => [
                UploadedFile::fake()->create('page1.pdf', 500, 'application/pdf'),
                UploadedFile::fake()->create('page2.pdf', 500, 'application/pdf'),
            ],
        ])->assertSessionHas('success');

        $uploads = SanctionLetterUpload::where('sanction_letter_id', $letter->id)->get();
        $this->assertCount(2, $uploads);

        // Agent page lists every uploaded file with its own preview link.
        $html = $this->actingAs($this->agent)
            ->get(route('agent.sanctions.show', $letter))
            ->assertOk()
            ->getContent();
        foreach ($uploads as $upload) {
            $this->assertStringContainsString(e($upload->original_name), $html);
            $this->actingAs($this->agent)
                ->get(route('agent.sanctions.signed-file', [$letter, $upload]))
                ->assertOk();
        }

        // Inline preview panel + shared popup shell for mobile viewing.
        $this->assertStringContainsString('agentSignedPreviewPanel', $html);
        $this->assertStringContainsString('filePreviewModal', $html);
        // File rows must shrink inside the container (no fixed wide filename).
        $this->assertStringNotContainsString('max-width: 280px', $html);

        // Another agent cannot preview these files.
        $other = User::factory()->create(['role_id' => $this->agentRole->id]);
        $this->actingAs($other)
            ->get(route('agent.sanctions.signed-file', [$letter, $uploads->first()]))
            ->assertForbidden();

        // An upload from a different letter is rejected.
        $otherLetter = $this->makeLetter($this->agent, ['sanction_number' => 105]);
        $this->actingAs($this->agent)
            ->get(route('agent.sanctions.signed-file', [$otherLetter, $uploads->first()]))
            ->assertNotFound();
    }

    public function test_admin_review_page_supports_per_file_preview(): void
    {
        $letter = $this->makeLetter($this->agent);
        $this->actingAs($this->agent)->post(route('agent.sanctions.upload', $letter), [
            'signed_pdf' => [
                UploadedFile::fake()->create('a.pdf', 500, 'application/pdf'),
                UploadedFile::fake()->create('b.pdf', 500, 'application/pdf'),
            ],
        ]);

        $this->actingAs($this->admin)
            ->get(route('sanctions.review', $letter))
            ->assertOk()
            // In-panel preview shell with per-file hooks for the history rows.
            ->assertSee('signedPreviewFrame', false)
            ->assertSee('signedPreviewPanel', false)
            ->assertSee('signed-history-view', false)
            ->assertSee('signedBackToLatest', false)
            ->assertSee('filePreviewModal', false)
            // Mobile-responsive shell: stacked grid + scrollable history table.
            ->assertSee('rv-main-grid', false)
            ->assertSee('rv-table-scroll', false)
            ->assertSee('a.pdf', false)
            ->assertSee('b.pdf', false);
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

    public function test_agent_cannot_upload_twice_without_admin_reupload_request(): void
    {
        $letter = $this->makeLetter($this->agent);

        $this->actingAs($this->agent)->post(route('agent.sanctions.upload', $letter), [
            'signed_pdf' => UploadedFile::fake()->create('first.pdf', 500, 'application/pdf'),
        ])->assertSessionHas('success');

        // Admin flips the status back to pending: agent still must not upload again.
        $this->actingAs($this->admin)->post(route('sanctions.status', $letter), [
            'review_status' => 'pending',
        ])->assertSessionHas('success');

        $letter->refresh();
        $this->assertSame('pending', $letter->review_status);
        $this->assertFalse($letter->canUpload());

        $this->actingAs($this->agent)->post(route('agent.sanctions.upload', $letter), [
            'signed_pdf' => UploadedFile::fake()->create('second.pdf', 500, 'application/pdf'),
        ])->assertSessionHas('error', 'Upload is not available for this letter right now.');

        $this->assertSame(1, $letter->fresh()->signed_pdf_upload_count);

        // The upload form stays hidden on the agent page (waiting state instead).
        $this->actingAs($this->agent)
            ->get(route('agent.sanctions.show', $letter))
            ->assertOk()
            ->assertDontSee('Upload Signed Copy')
            ->assertSee('waiting for admin review');

        // Admin explicitly requests a re-upload: agent may upload exactly once more.
        $this->actingAs($this->admin)
            ->post(route('sanctions.reupload-required', $letter), ['review_comment' => 'Blurry'])
            ->assertSessionHas('success');

        $this->assertTrue($letter->fresh()->canUpload());

        $this->actingAs($this->agent)->post(route('agent.sanctions.upload', $letter), [
            'signed_pdf' => UploadedFile::fake()->create('second.pdf', 500, 'application/pdf'),
        ])->assertSessionHas('success');

        $this->assertSame(2, $letter->fresh()->signed_pdf_upload_count);
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
