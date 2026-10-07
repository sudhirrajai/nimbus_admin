<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeedbackForm;
use App\Models\FeedbackInvitation;
use App\Models\FeedbackSubmission;
use App\Models\Testimonial;
use App\Models\User;
use App\Notifications\FeedbackInvitationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AdminFeedbackController extends Controller
{
    /**
     * Display all feedback forms, responses, and invitation metrics.
     */
    public function index(Request $request)
    {
        $forms = FeedbackForm::withCount('submissions')
            ->with(['creator:id,name,email'])
            ->latest()
            ->get()
            ->map(function ($form) {
                return [
                    'id' => $form->id,
                    'uuid' => $form->uuid,
                    'title' => $form->title,
                    'slug' => $form->slug,
                    'description' => $form->description,
                    'category' => $form->category,
                    'questions' => $form->questions ?? [],
                    'is_active' => $form->is_active,
                    'allow_anonymous' => $form->allow_anonymous,
                    'collect_company' => $form->collect_company,
                    'collect_role' => $form->collect_role,
                    'success_title' => $form->success_title,
                    'success_message' => $form->success_message,
                    'public_url' => $form->public_url,
                    'submissions_count' => $form->submissions_count,
                    'average_rating' => $form->average_rating,
                    'created_at' => $form->created_at->format('M d, Y'),
                ];
            });

        $submissions = FeedbackSubmission::with(['form:id,title,slug,questions', 'invitation:id,token,recipient_email', 'testimonial:id,quote'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $invitations = FeedbackInvitation::with(['form:id,title', 'user:id,name,email'])
            ->latest()
            ->limit(30)
            ->get()
            ->map(function ($invitation) {
                return [
                    'id' => $invitation->id,
                    'uuid' => $invitation->uuid,
                    'form_title' => $invitation->form?->title ?? 'General Form',
                    'recipient_name' => $invitation->recipient_name,
                    'recipient_email' => $invitation->recipient_email,
                    'status' => $invitation->status,
                    'invite_url' => $invitation->invite_url,
                    'sent_at' => $invitation->sent_at ? $invitation->sent_at->format('M d, Y H:i') : null,
                    'completed_at' => $invitation->completed_at ? $invitation->completed_at->format('M d, Y H:i') : null,
                ];
            });

        $users = User::select('id', 'name', 'email', 'company_name')->orderBy('name')->get();

        $totalSubmissions = FeedbackSubmission::count();
        $averageRating = round((float) FeedbackSubmission::avg('rating'), 1) ?: 5.0;
        $activeFormsCount = FeedbackForm::where('is_active', true)->count();
        $promotedTestimonialsCount = FeedbackSubmission::where('is_testimonial', true)->count();

        return Inertia::render('Admin/Feedback/Index', [
            'forms' => $forms,
            'submissions' => $submissions,
            'invitations' => $invitations,
            'users' => $users,
            'metrics' => [
                'total_submissions' => $totalSubmissions,
                'average_rating' => $averageRating,
                'active_forms' => $activeFormsCount,
                'promoted_testimonials' => $promotedTestimonialsCount,
            ],
        ]);
    }

    /**
     * Create a new feedback form.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'category' => 'required|string|max:60',
            'description' => 'nullable|string|max:1000',
            'questions' => 'nullable|array',
            'is_active' => 'required|boolean',
            'allow_anonymous' => 'required|boolean',
            'collect_company' => 'required|boolean',
            'collect_role' => 'required|boolean',
            'success_title' => 'nullable|string|max:150',
            'success_message' => 'nullable|string|max:1000',
        ]);

        $slugBase = Str::slug($validated['title']);
        $slug = $slugBase;
        $counter = 1;
        while (FeedbackForm::where('slug', $slug)->exists()) {
            $slug = "{$slugBase}-{$counter}";
            $counter++;
        }

        $form = FeedbackForm::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'questions' => $validated['questions'] ?? [],
            'is_active' => $validated['is_active'],
            'allow_anonymous' => $validated['allow_anonymous'],
            'collect_company' => $validated['collect_company'],
            'collect_role' => $validated['collect_role'],
            'success_title' => $validated['success_title'] ?: 'Thank you for your feedback!',
            'success_message' => $validated['success_message'] ?? 'Your feedback helps us continuously improve our cloud services.',
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', "Feedback form '{$form->title}' created successfully.");
    }

    /**
     * Update an existing feedback form.
     */
    public function update(Request $request, FeedbackForm $feedbackForm)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'category' => 'required|string|max:60',
            'description' => 'nullable|string|max:1000',
            'questions' => 'nullable|array',
            'is_active' => 'required|boolean',
            'allow_anonymous' => 'required|boolean',
            'collect_company' => 'required|boolean',
            'collect_role' => 'required|boolean',
            'success_title' => 'nullable|string|max:150',
            'success_message' => 'nullable|string|max:1000',
        ]);

        $feedbackForm->update($validated);

        return back()->with('success', "Feedback form '{$feedbackForm->title}' updated successfully.");
    }

    /**
     * Delete a feedback form.
     */
    public function destroy(FeedbackForm $feedbackForm)
    {
        $title = $feedbackForm->title;
        $feedbackForm->delete();

        return back()->with('success', "Feedback form '{$title}' deleted successfully.");
    }

    /**
     * Toggle active status of a form.
     */
    public function toggleActive(FeedbackForm $feedbackForm)
    {
        $feedbackForm->update(['is_active' => !$feedbackForm->is_active]);

        $state = $feedbackForm->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Feedback form '{$feedbackForm->title}' has been {$state}.");
    }

    /**
     * AI generation endpoint for questions based on admin prompt or templates.
     */
    public function generateAiQuestions(Request $request)
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:1000',
            'category' => 'nullable|string|max:60',
        ]);

        $prompt = trim($validated['prompt']);
        $category = $validated['category'] ?? 'managed_hosting';

        // Check for Gemini API key or OpenAI API key in env or settings
        $geminiKey = env('GEMINI_API_KEY');
        $openAiKey = env('OPENAI_API_KEY');

        if ($geminiKey) {
            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$geminiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => "You are an expert UX and customer satisfaction feedback architect for a cloud hosting and server management platform (Roook Hosting, offering managed cloud hosting and the Nimbus server management panel).\n" .
                                              "Generate 4 to 6 customer feedback questions based on this prompt: \"{$prompt}\" (Category: {$category}).\n" .
                                              "Include a mix of star rating, text, textarea, and single choice if appropriate.\n" .
                                              "Respond ONLY with a valid JSON array of objects with keys: id (string, e.g. q_1), label (string), type ('rating'|'text'|'textarea'|'select'|'radio'), required (boolean), placeholder (string or null), options (array of strings if type is select or radio). Do not output markdown code fences or other text."
                                ]
                            ]
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $jsonText = $response->json('candidates.0.content.parts.0.text');
                    $cleanJson = trim(preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($jsonText)));
                    $parsed = json_decode($cleanJson, true);
                    if (is_array($parsed) && count($parsed) > 0) {
                        return response()->json(['questions' => $parsed]);
                    }
                }
            } catch (\Throwable $e) {
                // Fall back to intelligent algorithmic synthesis
            }
        } elseif ($openAiKey) {
            try {
                $response = Http::withToken($openAiKey)->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are an expert UX feedback architect for a cloud hosting platform. Generate a JSON array of 4-6 questions with keys: id, label, type (rating|text|textarea|select|radio), required (boolean), placeholder, options (array if select/radio).'
                        ],
                        [
                            'role' => 'user',
                            'content' => "Generate feedback questions for: {$prompt} (Category: {$category}). Respond ONLY with valid JSON array."
                        ]
                    ],
                    'response_format' => ['type' => 'json_object'],
                ]);

                if ($response->successful()) {
                    $content = $response->json('choices.0.message.content');
                    $parsed = json_decode($content, true);
                    $questions = $parsed['questions'] ?? (is_array($parsed) ? $parsed : null);
                    if (is_array($questions) && count($questions) > 0) {
                        return response()->json(['questions' => $questions]);
                    }
                }
            } catch (\Throwable $e) {
                // Fall back
            }
        }

        // Intelligent Synthesis Engine (Always works reliably offline or without API key)
        $lower = strtolower($prompt);
        $questions = [];

        // Overall rating question
        if (str_contains($lower, 'support') || str_contains($lower, 'ticket') || str_contains($lower, 'service')) {
            $questions[] = [
                'id' => 'q_rating_support',
                'label' => 'How satisfied are you with our technical support and response time?',
                'type' => 'rating',
                'required' => true,
                'placeholder' => null,
                'options' => [],
            ];
        } else {
            $questions[] = [
                'id' => 'q_rating_exp',
                'label' => 'How would you rate your overall experience with Nimbus Cloud Infrastructure?',
                'type' => 'rating',
                'required' => true,
                'placeholder' => null,
                'options' => [],
            ];
        }

        // Specific questions based on prompt keywords
        if (str_contains($lower, 'speed') || str_contains($lower, 'performance') || str_contains($lower, 'uptime') || str_contains($lower, 'hosting')) {
            $questions[] = [
                'id' => 'q_performance',
                'label' => 'How would you rate your server uptime, TTFB, and overall speed performance?',
                'type' => 'select',
                'required' => true,
                'options' => [
                    'Blazing fast / Exceeded expectations',
                    'Stable and very fast',
                    'Average / Meets expectations',
                    'Experienced occasional slow downs',
                ],
            ];
        }

        if (str_contains($lower, 'panel') || str_contains($lower, 'dashboard') || str_contains($lower, 'self-host') || str_contains($lower, 'ui')) {
            $questions[] = [
                'id' => 'q_panel_usability',
                'label' => 'How intuitive do you find the Nimbus server control panel and dashboard?',
                'type' => 'radio',
                'required' => false,
                'options' => [
                    'Extremely straightforward & clean',
                    'Easy to use with good documentation',
                    'Took some time to learn',
                    'Needs improvement',
                ],
            ];
        }

        if (str_contains($lower, 'migration') || str_contains($lower, 'cpanel') || str_contains($lower, 'onboard')) {
            $questions[] = [
                'id' => 'q_migration',
                'label' => 'How smooth was the onboarding / server migration process for your applications?',
                'type' => 'rating',
                'required' => false,
                'placeholder' => null,
                'options' => [],
            ];
        }

        $questions[] = [
            'id' => 'q_best_feature',
            'label' => 'What feature or aspect of our service do you appreciate the most?',
            'type' => 'text',
            'required' => false,
            'placeholder' => 'e.g. 99.9% uptime, rapid SSH access, 24/7 technical assistance...',
            'options' => [],
        ];

        $questions[] = [
            'id' => 'q_improvements',
            'label' => 'What is one thing we could do or feature we could add to make Nimbus even better for you?',
            'type' => 'textarea',
            'required' => false,
            'placeholder' => 'Share any suggestions, tools, integrations, or workflows you would like to see...',
            'options' => [],
        ];

        $questions[] = [
            'id' => 'q_recommend',
            'label' => 'How likely are you to recommend Nimbus Cloud to a colleague or client?',
            'type' => 'select',
            'required' => true,
            'options' => [
                '10 - Extremely likely (Passionate promoter)',
                '9 - Very likely',
                '8 - Likely',
                '7 - Neutral',
                '6 or below - Unlikely',
            ],
        ];

        return response()->json([
            'questions' => $questions,
        ]);
    }

    /**
     * Send email invitation to client with personal note.
     */
    public function sendInvitation(Request $request, FeedbackForm $feedbackForm)
    {
        $validated = $request->validate([
            'recipient_email' => 'required|email|max:150',
            'recipient_name' => 'nullable|string|max:100',
            'user_id' => 'nullable|exists:users,id',
            'personal_note' => 'nullable|string|max:1000',
        ]);

        $recipientEmail = trim($validated['recipient_email']);
        $recipientName = trim($validated['recipient_name'] ?? '');
        $userId = $validated['user_id'] ?? null;

        // If user_id provided, sync name
        if ($userId && empty($recipientName)) {
            $user = User::find($userId);
            if ($user) {
                $recipientName = $user->name;
            }
        }

        $invitation = FeedbackInvitation::create([
            'feedback_form_id' => $feedbackForm->id,
            'user_id' => $userId,
            'recipient_email' => $recipientEmail,
            'recipient_name' => $recipientName ?: null,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        // Send email notification
        try {
            // Use route-notifiable for raw email
            $notifiable = (new User())->forceFill([
                'email' => $recipientEmail,
                'name' => $recipientName ?: 'Client',
            ]);

            $notifiable->notify(new FeedbackInvitationNotification(
                $feedbackForm,
                $invitation,
                $validated['personal_note'] ?? null
            ));

            return back()->with('success', "Feedback invitation successfully sent to {$recipientEmail}.");
        } catch (\Throwable $e) {
            return back()->with('success', "Invitation generated (Link: {$invitation->invite_url}), but mail transport reported: " . $e->getMessage());
        }
    }

    /**
     * 1-Click Promote feedback to Testimonial.
     */
    public function promoteToTestimonial(Request $request, FeedbackSubmission $submission)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'role' => 'nullable|string|max:100',
            'company' => 'nullable|string|max:120',
            'location' => 'nullable|string|max:100',
            'quote' => 'required|string|max:2000',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        // Create or update Testimonial
        $testimonial = Testimonial::create([
            'name' => $validated['name'],
            'role' => $validated['role'] ?: 'Client',
            'company' => $validated['company'] ?? null,
            'location' => $validated['location'] ?? null,
            'quote' => $validated['quote'],
            'rating' => $validated['rating'],
            'is_active' => true,
            'sort_order' => Testimonial::max('sort_order') + 1,
        ]);

        $submission->update([
            'testimonial_id' => $testimonial->id,
            'is_testimonial' => true,
            'status' => 'promoted_to_testimonial',
        ]);

        return back()->with('success', "Client review promoted to landing page testimonial successfully!");
    }

    /**
     * Delete a single submission.
     */
    public function destroySubmission(FeedbackSubmission $submission)
    {
        $submission->delete();
        return back()->with('success', 'Feedback submission deleted successfully.');
    }
}
