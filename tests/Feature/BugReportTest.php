<?php

namespace Tests\Feature;

use App\Models\BugReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BugReportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test submitting a bug report via API works, stores database fields,
     * and uploads files to the public storage.
     */
    public function test_can_submit_bug_report_via_api()
    {
        Storage::fake('public');

        $screenshot = UploadedFile::fake()->image('screenshot.png');
        $img1 = UploadedFile::fake()->image('attachment1.jpg');
        $img2 = UploadedFile::fake()->image('attachment2.jpg');

        $response = $this->postJson('/api/v1/bug-reports', [
            'license_key' => 'FREE-ABC123XYZ',
            'domain'      => 'nimbus-panel.local',
            'admin_name'  => 'John Doe',
            'admin_email' => 'john@example.com',
            'message'     => 'An error occurred during system check.',
            'screenshot'  => $screenshot,
            'images'      => [$img1, $img2],
            'ip_address'  => '192.168.1.1',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'status',
            'message',
            'report' => [
                'id',
                'license_key',
                'domain',
                'admin_name',
                'admin_email',
                'message',
                'screenshot_path',
                'images',
                'ip_address',
                'status',
                'created_at',
                'updated_at',
            ]
        ]);

        $report = BugReport::first();
        $this->assertNotNull($report);
        $this->assertEquals('FREE-ABC123XYZ', $report->license_key);
        $this->assertEquals('nimbus-panel.local', $report->domain);
        $this->assertEquals('John Doe', $report->admin_name);
        $this->assertEquals('john@example.com', $report->admin_email);
        $this->assertEquals('An error occurred during system check.', $report->message);
        $this->assertEquals('192.168.1.1', $report->ip_address);
        $this->assertEquals('pending', $report->status);

        // Check file storage
        $this->assertNotNull($report->screenshot_path);
        Storage::disk('public')->assertExists($report->screenshot_path);

        $this->assertCount(2, $report->images);
        foreach ($report->images as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }

    /**
     * Test admin dashboard actions: access, update status, and report deletion.
     */
    public function test_admin_can_view_update_status_and_delete_reports()
    {
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);

        $report = BugReport::create([
            'message' => 'Something is broken.',
            'screenshot_path' => UploadedFile::fake()->image('screen.png')->store('bug-reports', 'public'),
            'images' => [UploadedFile::fake()->image('att.png')->store('bug-reports', 'public')],
            'status' => 'pending',
        ]);

        // Non-admin is restricted
        $user = User::factory()->create(['is_admin' => false]);
        $response = $this->actingAs($user)->get('/admin/reports');
        $response->assertRedirect('/dashboard');

        // Admin can view
        $response = $this->actingAs($admin)->get('/admin/reports');
        $response->assertStatus(200);

        // Admin can update status
        $response = $this->actingAs($admin)->post("/admin/reports/{$report->id}/status", [
            'status' => 'resolved'
        ]);
        $response->assertRedirect();
        $this->assertEquals('resolved', $report->fresh()->status);

        // Admin can delete report and files are deleted from storage
        $screenshotPath = $report->screenshot_path;
        $imagePath = $report->images[0];
        
        Storage::disk('public')->assertExists($screenshotPath);
        Storage::disk('public')->assertExists($imagePath);

        $response = $this->actingAs($admin)->delete("/admin/reports/{$report->id}");
        $response->assertRedirect();
        
        $this->assertDatabaseMissing('bug_reports', ['id' => $report->id]);
        Storage::disk('public')->assertMissing($screenshotPath);
        Storage::disk('public')->assertMissing($imagePath);
    }
}
