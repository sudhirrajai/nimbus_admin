@extends('emails.layout', ['subject' => $subject ?? 'Thank you for your feedback - Roook Hosting'])

@section('content')
    <h2 style="margin: 0 0 16px; font-size: 20px; font-weight: 700; color: #0f172a;">
        Hi {{ $submission->client_name ?: 'there' }},
    </h2>

    <p style="margin: 0 0 16px; font-size: 15px; color: #334155; line-height: 1.6;">
        Thank you for taking the time to review your experience with <strong>{{ $form->title }}</strong>! Your input is genuinely appreciated and helps our engineering team continuously refine Roook cloud hosting, panel features, and infrastructure performance.
    </p>

    <p style="margin: 0 0 20px; font-size: 14px; color: #64748b;">
        Below is a copy of your submitted response for your personal records:
    </p>

    <!-- Response Summary Card -->
    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 24px; margin: 0 0 28px;">
        <div style="border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 11px; font-weight: 700; color: #10b981; text-transform: uppercase; letter-spacing: 0.05em;">
                Submission Record
            </span>
            <span style="font-size: 11px; font-family: monospace; color: #64748b;">
                Ref #FB-{{ str_pad($submission->id, 5, '0', STR_PAD_LEFT) }}
            </span>
        </div>

        <!-- Overall Star Rating -->
        <div style="margin-bottom: 18px;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; margin-bottom: 4px;">
                Overall Rating
            </div>
            <div style="font-size: 18px; color: #f59e0b; font-weight: 700;">
                @for($i = 1; $i <= 5; $i++)
                    {{ $i <= $submission->rating ? '★' : '☆' }}
                @endfor
                <span style="font-size: 14px; color: #0f172a; margin-left: 6px; font-weight: 600;">
                    ({{ $submission->rating }} / 5)
                </span>
            </div>
        </div>

        <!-- Feedback / Review -->
        @if(!empty($submission->feedback))
            <div style="margin-bottom: 18px;">
                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; margin-bottom: 4px;">
                    Your Review &amp; Comments
                </div>
                <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 14px; font-size: 14px; color: #1e293b; line-height: 1.6; white-space: pre-line;">
                    {{ $submission->feedback }}
                </div>
            </div>
        @endif

        <!-- Suggestions -->
        @if(!empty($submission->suggestions))
            <div style="margin-bottom: 18px;">
                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; margin-bottom: 4px;">
                    Feature Suggestions &amp; Improvements
                </div>
                <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 14px; font-size: 14px; color: #1e293b; line-height: 1.6; white-space: pre-line;">
                    {{ $submission->suggestions }}
                </div>
            </div>
        @endif

        <!-- Dynamic Questions & Answers -->
        @if(!empty($form->questions) && is_array($form->questions) && !empty($submission->answers))
            <div style="margin-top: 20px; border-top: 1px dashed #cbd5e1; padding-top: 16px;">
                <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #0f172a; margin-bottom: 12px;">
                    Detailed Question Answers
                </div>
                @foreach($form->questions as $index => $question)
                    @php
                        $qKey = $question['id'] ?? ('q_' . $index);
                        $answer = $submission->answers[$qKey] ?? null;
                    @endphp
                    @if($answer !== null && $answer !== '')
                        <div style="margin-bottom: 12px;">
                            <div style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 2px;">
                                {{ $question['label'] }}
                            </div>
                            <div style="font-size: 13px; color: #0f172a;">
                                @if(($question['type'] ?? '') === 'rating')
                                    <span style="color: #f59e0b; font-weight: 700;">
                                        @for($s = 1; $s <= 5; $s++)
                                            {{ $s <= (int)$answer ? '★' : '☆' }}
                                        @endfor
                                    </span>
                                    <span style="color: #64748b; font-size: 12px; margin-left: 4px;">({{ $answer }} / 5)</span>
                                @else
                                    <span style="background-color: #ffffff; border: 1px solid #e2e8f0; padding: 3px 8px; border-radius: 4px; display: inline-block;">
                                        {{ is_array($answer) ? implode(', ', $answer) : $answer }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        <!-- Client Signature Metadata -->
        <div style="margin-top: 18px; border-top: 1px solid #e2e8f0; padding-top: 12px; font-size: 12px; color: #64748b;">
            @if(!empty($submission->client_company))
                <span>Company: <strong>{{ $submission->client_company }}</strong> &bull; </span>
            @endif
            @if(!empty($submission->client_role))
                <span>Role: <strong>{{ $submission->client_role }}</strong> &bull; </span>
            @endif
            <span>Submitted: {{ $submission->created_at ? $submission->created_at->format('M d, Y, h:i A') : date('M d, Y') }}</span>
        </div>
    </div>

    <p style="margin: 0 0 20px; font-size: 14px; color: #334155; line-height: 1.6;">
        If you have any urgent server questions or require direct support from our DevOps engineers, you can open a support ticket at any time through your dashboard.
    </p>

    <div class="button-wrapper" style="text-align: center; margin: 28px 0;">
        <a href="{{ url('/dashboard') }}" class="btn-primary" style="background-color: #10B981; color: #ffffff !important; padding: 13px 30px; border-radius: 8px; font-weight: 700; text-decoration: none; display: inline-block; font-size: 13px;">
            Go to Roook Dashboard &rarr;
        </a>
    </div>
@endsection
