<?php

namespace Tests\Feature;

use App\Models\FeedbackForm;
use App\Models\FeedbackInvitation;
use App\Models\FeedbackSubmission;
use App\Models\Testimonial;
use App\Models\User;
use App\Notifications\FeedbackSubmissionThankYouNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class FeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_public_feedback_form_by_slug(): void
    {
        $form = FeedbackForm::firstOrCreate(
            ['slug' => 'test-managed-hosting'],
            [
                'title' => 'Test Managed Hosting Review',
                'category' => 'managed_hosting',
                'questions' => [
                    ['id' => 'q1', 'label' => 'Uptime review', 'type' => 'rating', 'required' => true]
                ],
                'is_active' => true,
            ]
        );

        $response = $this->get(route('feedback.show', ['identifier' => $form->slug]));
        $response->assertStatus(200);
    }

    public function test_can_submit_client_feedback(): void
    {
        $form = FeedbackForm::firstOrCreate(
            ['slug' => 'client-test-form'],
            [
                'title' => 'Client Experience Review',
                'category' => 'managed_hosting',
                'questions' => [
                    ['id' => 'q_uptime', 'label' => 'Server Uptime', 'type' => 'rating', 'required' => true]
                ],
                'is_active' => true,
                'allow_anonymous' => false,
            ]
        );

        Notification::fake();

        $response = $this->post(route('feedback.submit', ['identifier' => $form->slug]), [
            'rating' => 5,
            'client_name' => 'John Doe',
            'client_email' => 'john@acme.test',
            'client_company' => 'Acme Labs',
            'client_role' => 'CTO',
            'feedback' => 'Nimbus managed hosting has been completely flawless. Fast TTFB and awesome support!',
            'suggestions' => 'Add automated Discord alerts',
            'answers' => [
                'q_uptime' => 5,
            ],
        ]);

        $response->assertSessionHas('submitted', true);

        $this->assertDatabaseHas('feedback_submissions', [
            'feedback_form_id' => $form->id,
            'client_email' => 'john@acme.test',
            'rating' => 5,
            'client_company' => 'Acme Labs',
        ]);

        Notification::assertSentOnDemand(
            FeedbackSubmissionThankYouNotification::class,
            function ($notification, $channels, $notifiable) {
                return $notifiable->routes['mail'] === 'john@acme.test';
            }
        );
    }

    public function test_admin_can_generate_ai_questions(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin_test@vmcore.test'],
            [
                'name' => 'Admin Test',
                'password' => bcrypt('password'),
                'is_admin' => true,
            ]
        );
        $admin->is_admin = true;
        $admin->save();

        $response = $this->actingAs($admin)->postJson(route('admin.feedback.generate-ai'), [
            'prompt' => 'Review server speed, uptime, and cPanel migration',
            'category' => 'managed_hosting',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['questions']);
        $this->assertNotEmpty($response->json('questions'));
    }

    public function test_admin_can_promote_feedback_to_testimonial(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin_test2@vmcore.test'],
            [
                'name' => 'Admin Test 2',
                'password' => bcrypt('password'),
                'is_admin' => true,
            ]
        );
        $admin->is_admin = true;
        $admin->save();

        $form = FeedbackForm::create([
            'title' => 'Form for Testimonial',
            'slug' => 'form-testimonial',
            'category' => 'managed_hosting',
            'is_active' => true,
        ]);
        $submission = FeedbackSubmission::create([
            'feedback_form_id' => $form->id,
            'client_name' => 'Sarah Connor',
            'client_email' => 'sarah@skynet.test',
            'client_company' => 'Cyberdyne',
            'client_role' => 'VP of Infrastructure',
            'rating' => 5,
            'feedback' => 'Nimbus is the best managed cloud host we have ever operated on.',
            'status' => 'new',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.feedback.submissions.promote', ['submission' => $submission->id]), [
            'name' => 'Sarah Connor',
            'role' => 'VP of Infrastructure',
            'company' => 'Cyberdyne',
            'quote' => 'Nimbus is the best managed cloud host we have ever operated on.',
            'rating' => 5,
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('testimonials', [
            'name' => 'Sarah Connor',
            'company' => 'Cyberdyne',
            'rating' => 5,
            'is_active' => true,
        ]);

        $submission->refresh();
        $this->assertTrue($submission->is_testimonial);
        $this->assertEquals('promoted_to_testimonial', $submission->status);
    }

    public function test_admin_can_send_invitation_and_client_can_submit_via_token(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_invite@vmcore.test',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $form = FeedbackForm::create([
            'title' => 'VIP Client Review',
            'slug' => 'vip-review',
            'category' => 'managed_hosting',
            'is_active' => true,
        ]);

        $inviteResponse = $this->actingAs($admin)->post(route('admin.feedback.send-invitation', ['feedbackForm' => $form->id]), [
            'recipient_name' => 'Michael Scott',
            'recipient_email' => 'michael@dunder.test',
            'personal_note' => 'Please let us know how your cloud server is running!',
        ]);

        $inviteResponse->assertSessionHas('success');

        $invitation = FeedbackInvitation::where('recipient_email', 'michael@dunder.test')->first();
        $this->assertNotNull($invitation);

        // Client views form via token
        $viewResponse = $this->get(route('feedback.show', ['identifier' => $invitation->token]));
        $viewResponse->assertStatus(200);

        // Client submits feedback via token
        $submitResponse = $this->post(route('feedback.submit', ['identifier' => $invitation->token]), [
            'rating' => 5,
            'client_name' => 'Michael Scott',
            'client_email' => 'michael@dunder.test',
            'client_company' => 'Dunder Mifflin',
            'feedback' => 'That was what she said! Also 100% server uptime.',
        ]);

        $submitResponse->assertSessionHas('submitted', true);

        $invitation->refresh();
        $this->assertEquals('submitted', $invitation->status);
        $this->assertNotNull($invitation->completed_at);
    }
}
