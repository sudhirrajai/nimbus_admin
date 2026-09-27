<?php

namespace App\Http\Controllers;

use App\Models\FeedbackForm;
use App\Models\FeedbackInvitation;
use App\Models\FeedbackSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FeedbackController extends Controller
{
    /**
     * Resolve the form and invitation from an identifier (slug or token).
     */
    protected function resolveFormAndInvitation(string $identifier): array
    {
        // First check if identifier matches an invitation token
        $invitation = FeedbackInvitation::with('form')->where('token', $identifier)->first();

        if ($invitation) {
            return [$invitation->form, $invitation];
        }

        // Otherwise check if identifier matches a form slug
        $form = FeedbackForm::where('slug', $identifier)->first();

        return [$form, null];
    }

    /**
     * Display the feedback form to the client.
     */
    public function show(string $identifier)
    {
        [$form, $invitation] = $this->resolveFormAndInvitation($identifier);

        if (!$form) {
            abort(404, 'Feedback form not found.');
        }

        if (!$form->is_active) {
            return Inertia::render('Feedback/Show', [
                'form' => [
                    'title' => $form->title,
                    'is_active' => false,
                ],
                'isClosed' => true,
            ]);
        }

        $isAlreadySubmitted = false;
        if ($invitation && $invitation->completed_at) {
            $isAlreadySubmitted = true;
        }

        // Prefill client details if available
        $currentUser = Auth::user();
        $prefill = [
            'name' => $invitation?->recipient_name ?? $currentUser?->name ?? '',
            'email' => $invitation?->recipient_email ?? $currentUser?->email ?? '',
            'company' => $currentUser?->company_name ?? '',
        ];

        return Inertia::render('Feedback/Show', [
            'form' => [
                'id' => $form->id,
                'uuid' => $form->uuid,
                'slug' => $form->slug,
                'title' => $form->title,
                'description' => $form->description,
                'category' => $form->category,
                'questions' => $form->questions ?? [],
                'is_active' => $form->is_active,
                'allow_anonymous' => $form->allow_anonymous,
                'collect_company' => $form->collect_company,
                'collect_role' => $form->collect_role,
                'success_title' => $form->success_title,
                'success_message' => $form->success_message,
            ],
            'identifier' => $identifier,
            'isInvitation' => !is_null($invitation),
            'isAlreadySubmitted' => $isAlreadySubmitted,
            'prefill' => $prefill,
        ]);
    }

    /**
     * Submit client feedback.
     */
    public function submit(Request $request, string $identifier)
    {
        [$form, $invitation] = $this->resolveFormAndInvitation($identifier);

        if (!$form || !$form->is_active) {
            return back()->with('error', 'This feedback form is currently closed or unavailable.');
        }

        if ($invitation && $invitation->completed_at) {
            return back()->with('error', 'You have already submitted your response for this invitation.');
        }

        $rules = [
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:5000',
            'suggestions' => 'nullable|string|max:5000',
            'client_company' => 'nullable|string|max:150',
            'client_role' => 'nullable|string|max:150',
            'answers' => 'nullable|array',
        ];

        if ($form->allow_anonymous) {
            $rules['client_name'] = 'nullable|string|max:120';
            $rules['client_email'] = 'nullable|email|max:150';
        } else {
            $rules['client_name'] = 'required|string|max:120';
            $rules['client_email'] = 'required|email|max:150';
        }

        // Validate required questions dynamically
        if (!empty($form->questions) && is_array($form->questions)) {
            foreach ($form->questions as $index => $question) {
                if (!empty($question['required'])) {
                    $qKey = $question['id'] ?? ('q_' . $index);
                    $rules["answers.{$qKey}"] = 'required';
                }
            }
        }

        $validated = $request->validate($rules, [
            'rating.required' => 'Please provide an overall star rating.',
            'client_name.required' => 'Please provide your name.',
            'client_email.required' => 'Please provide your email address.',
            'answers.*.required' => 'Please answer the required question.',
        ]);

        $submission = FeedbackSubmission::create([
            'feedback_form_id' => $form->id,
            'feedback_invitation_id' => $invitation?->id,
            'user_id' => $invitation?->user_id ?? Auth::id(),
            'client_name' => $validated['client_name'] ?? ($form->allow_anonymous ? 'Anonymous Client' : 'Client'),
            'client_email' => $validated['client_email'] ?? ($form->allow_anonymous ? 'anonymous@feedback.local' : 'client@feedback.local'),
            'client_company' => $validated['client_company'] ?? null,
            'client_role' => $validated['client_role'] ?? null,
            'rating' => $validated['rating'],
            'feedback' => $validated['feedback'] ?? null,
            'suggestions' => $validated['suggestions'] ?? null,
            'answers' => $request->input('answers', []),
            'status' => 'new',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($invitation) {
            $invitation->update([
                'status' => 'submitted',
                'completed_at' => now(),
            ]);
        }

        return back()->with('submitted', true);
    }
}
