<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserDetail;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AgentImportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create(['name' => 'Super Admin', 'slug' => 'super-admin']);
        $this->admin = User::factory()->create(['role_id' => $adminRole->id]);

        Role::create(['name' => 'Agent', 'slug' => 'agent']);
    }

    private function importCsv(string $csv): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->admin)
            ->post(route('agents.import'), [
                'csv_file' => UploadedFile::fake()->createWithContent('agents.csv', $csv),
            ]);
    }

    private function csv(array $rows): string
    {
        $rows = array_merge([
            'name,email,phone,status,pan_number',
        ], $rows);

        return implode("\n", $rows);
    }

    public function test_import_creates_agent_with_pan_at_last4_password(): void
    {
        $csv = $this->csv([
            'John Doe,john@demo.com,9876543210,active,ABCDE1234F',
            'Jane Doe,jane@demo.com,8123456789,active,MNOPQ7823K',
        ]);

        $this->importCsv($csv)
            ->assertSessionHas('success', 'Import complete! 2 imported, 0 skipped.');

        $john = User::where('email', 'john@demo.com')->first();
        $this->assertNotNull($john);
        $this->assertSame('9876543210', $john->phone);
        $this->assertTrue(Hash::check('ABCDE1234F@3210', $john->password));

        $jane = User::where('email', 'jane@demo.com')->first();
        $this->assertNotNull($jane);
        $this->assertTrue(Hash::check('MNOPQ7823K@6789', $jane->password));

        $this->assertSame('ABCDE1234F', $john->detail->pan_number);
        $this->assertSame('9876543210', $john->detail->mobile);
    }

    public function test_import_uppercases_pan_in_password_and_details(): void
    {
        $csv = $this->csv([
            'John Doe,john@demo.com,9876543210,active,abcde1234f',
        ]);

        $this->importCsv($csv)
            ->assertSessionHas('success', 'Import complete! 1 imported, 0 skipped.');

        $john = User::where('email', 'john@demo.com')->first();
        $this->assertTrue(Hash::check('ABCDE1234F@3210', $john->password));
        $this->assertSame('ABCDE1234F', $john->detail->pan_number);
    }

    public function test_import_accepts_91_prefixed_phone(): void
    {
        $csv = $this->csv([
            'John Doe,john@demo.com,+91 98765 43210,active,ABCDE1234F',
        ]);

        $this->importCsv($csv)
            ->assertSessionHas('success', 'Import complete! 1 imported, 0 skipped.');

        $john = User::where('email', 'john@demo.com')->first();
        $this->assertSame('9876543210', $john->phone);
        $this->assertTrue(Hash::check('ABCDE1234F@3210', $john->password));
    }

    public function test_import_rejects_invalid_pan_and_skips_row(): void
    {
        $csv = $this->csv([
            'John Doe,john@demo.com,9876543210,active,ABC123',
        ]);

        $this->importCsv($csv)
            ->assertSessionHas('success', 'Import complete! 0 imported, 1 skipped.')
            ->assertSessionHas('import_errors', function ($errors) {
                $this->assertTrue(str_contains($errors[0], 'Row 2 (john@demo.com): Invalid PAN'));

                return true;
            });

        $this->assertNull(User::where('email', 'john@demo.com')->first());
    }

    public function test_import_rejects_invalid_phone_and_skips_row(): void
    {
        $csv = $this->csv([
            'John Doe,john@demo.com,12345,active,ABCDE1234F',
        ]);

        $this->importCsv($csv)
            ->assertSessionHas('success', 'Import complete! 0 imported, 1 skipped.')
            ->assertSessionHas('import_errors', function ($errors) {
                $this->assertTrue(str_contains($errors[0], 'Row 2 (john@demo.com): Invalid phone'));

                return true;
            });

        $this->assertNull(User::where('email', 'john@demo.com')->first());
    }

    public function test_import_requires_pan_and_phone_for_new_agents(): void
    {
        $csv = $this->csv([
            'John Doe,john@demo.com,,active,',
        ]);

        $this->importCsv($csv)
            ->assertSessionHas('success', 'Import complete! 0 imported, 1 skipped.')
            ->assertSessionHas('import_errors', function ($errors) {
                $this->assertTrue(str_contains($errors[0], 'PAN number is required'));

                return true;
            });

        $this->assertNull(User::where('email', 'john@demo.com')->first());
    }

    public function test_import_skips_duplicate_pan_within_file(): void
    {
        $csv = $this->csv([
            'John Doe,john@demo.com,9876543210,active,ABCDE1234F',
            'Jane Doe,jane@demo.com,8123456789,active,ABCDE1234F',
        ]);

        $this->importCsv($csv)
            ->assertSessionHas('success', 'Import complete! 1 imported, 1 skipped.')
            ->assertSessionHas('import_errors', function ($errors) {
                $this->assertTrue(str_contains($errors[0], 'Row 3 (jane@demo.com): PAN ABCDE1234F is duplicated in this file.'));

                return true;
            });

        $this->assertNull(User::where('email', 'jane@demo.com')->first());
    }

    public function test_import_rejects_pan_belonging_to_existing_agent(): void
    {
        $existing = User::factory()->create(['role_id' => Role::where('slug', 'agent')->value('id')]);
        UserDetail::create([
            'user_id' => $existing->id,
            'pan_number' => 'ABCDE1234F',
        ]);

        $csv = $this->csv([
            'John Doe,john@demo.com,9876543210,active,ABCDE1234F',
        ]);

        $this->importCsv($csv)
            ->assertSessionHas('success', 'Import complete! 0 imported, 1 skipped.')
            ->assertSessionHas('import_errors', function ($errors) {
                $this->assertTrue(str_contains($errors[0], 'PAN ABCDE1234F already belongs to another agent.'));

                return true;
            });

        $this->assertNull(User::where('email', 'john@demo.com')->first());
    }

    public function test_import_skips_already_existing_email_without_error(): void
    {
        User::factory()->create(['role_id' => Role::where('slug', 'agent')->value('id'), 'email' => 'john@demo.com']);

        $csv = $this->csv([
            'John Doe,john@demo.com,9876543210,active,ABCDE1234F',
        ]);

        $this->importCsv($csv)
            ->assertSessionHas('success', 'Import complete! 0 imported, 1 skipped.')
            ->assertSessionMissing('import_errors');
    }
}