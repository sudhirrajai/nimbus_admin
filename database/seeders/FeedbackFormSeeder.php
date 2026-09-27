<?php

namespace Database\Seeders;

use App\Models\FeedbackForm;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FeedbackFormSeeder extends Seeder
{
    public function run(): void
    {
        if (FeedbackForm::count() > 0) {
            return;
        }

        FeedbackForm::create([
            'uuid' => (string) Str::uuid(),
            'title' => 'Managed Hosting Experience & Performance Review',
            'slug' => 'managed-hosting-review',
            'category' => 'managed_hosting',
            'description' => 'Help us improve our managed cloud servers, uptime reliability, and technical support. Your feedback directly shapes our roadmap.',
            'questions' => [
                [
                    'id' => 'q_rating_exp',
                    'label' => 'How would you rate your overall managed hosting experience with Nimbus?',
                    'type' => 'rating',
                    'required' => true,
                    'placeholder' => null,
                    'options' => [],
                ],
                [
                    'id' => 'q_uptime_speed',
                    'label' => 'How satisfied are you with your server uptime, response time, and reliability?',
                    'type' => 'select',
                    'required' => true,
                    'options' => [
                        'Exceeded expectations (100% stable & fast)',
                        'Very good and stable',
                        'Average / Meets basic requirements',
                        'Experienced issues or slowdowns',
                    ],
                ],
                [
                    'id' => 'q_panel_ease',
                    'label' => 'How easy is it to manage your server resources, SSL, and domains through our portal?',
                    'type' => 'radio',
                    'required' => false,
                    'options' => [
                        'Very smooth & intuitive',
                        'Good with clear controls',
                        'A bit complex but manageable',
                    ],
                ],
                [
                    'id' => 'q_best_aspect',
                    'label' => 'What do you appreciate the most about Nimbus Managed Hosting?',
                    'type' => 'text',
                    'required' => false,
                    'placeholder' => 'e.g. Instant support response, automated backups, zero downtime...',
                    'options' => [],
                ],
                [
                    'id' => 'q_recommend',
                    'label' => 'How likely are you to recommend Nimbus Cloud to a fellow business or developer?',
                    'type' => 'select',
                    'required' => true,
                    'options' => [
                        '10 - Extremely likely (Passionate promoter)',
                        '9 - Very likely',
                        '8 - Likely',
                        '7 - Neutral',
                        '6 or below - Unlikely',
                    ],
                ],
            ],
            'is_active' => true,
            'allow_anonymous' => false,
            'collect_company' => true,
            'collect_role' => true,
            'success_title' => 'Thank you for your valuable feedback!',
            'success_message' => 'Your review has been received by our core engineering team. We appreciate your partnership with Nimbus by VMCore.',
        ]);
    }
}
