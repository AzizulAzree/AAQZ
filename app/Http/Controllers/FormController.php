<?php

namespace App\Http\Controllers;

use App\Models\OrderingSmartForm;
use App\Models\OrderingSmartFormSubmission;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class FormController extends Controller
{
    public function index(): View
    {
        $user = request()->user();
        $savedForm = Schema::hasTable('ordering_smart_forms')
            ? $user->orderingSmartForm
            : null;
        $submissions = Schema::hasTable('ordering_smart_form_submissions')
            ? $user->orderingSmartFormSubmissions()->take(20)->get()
            : collect();

        return view('form.index', [
            'shareUrl' => $this->shareUrl($user),
            'formRows' => $this->normalizedRows($savedForm?->fields),
            'submissions' => $this->submissionTableRows($submissions->all()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('ordering_smart_forms')) {
            return redirect()
                ->route('forms.index')
                ->with('status', 'form-storage-unavailable');
        }

        $validated = $request->validate([
            'rows' => ['nullable', 'string'],
        ]);

        $rows = collect(json_decode((string) ($validated['rows'] ?? '[]'), true))
            ->filter(fn ($row) => is_array($row))
            ->map(function (array $row, int $index): array {
                $type = (string) ($row['type'] ?? 'text');
                $allowedTypes = ['date', 'text', 'money', 'number', 'dropdown', 'radio button', 'checkbox'];
                $optionsText = trim((string) ($row['options_text'] ?? ''));
                $optionLines = collect(preg_split('/\r\n|\r|\n/', $optionsText ?: '') ?: [])
                    ->map(fn ($value) => trim((string) $value))
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'id' => (int) ($row['id'] ?? ($index + 1)),
                    'title' => trim((string) ($row['title'] ?? '')),
                    'type' => in_array($type, $allowedTypes, true) ? $type : 'text',
                    'options_text' => in_array($type, ['dropdown', 'radio button', 'checkbox'], true)
                        ? implode("\n", $optionLines)
                        : '',
                    'options' => in_array($type, ['dropdown', 'radio button', 'checkbox'], true)
                        ? $optionLines
                        : [],
                ];
            })
            ->values()
            ->all();

        $rows = $this->normalizedRows($rows);

        OrderingSmartForm::query()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['fields' => $rows],
        );

        return redirect()
            ->route('forms.index')
            ->with('status', 'form-saved');
    }

    public function show(User $user, string $token): View
    {
        abort_unless(hash_equals($this->shareToken($user), $token), 404);

        $savedForm = Schema::hasTable('ordering_smart_forms')
            ? $user->orderingSmartForm
            : null;

        return view('form.public', [
            'owner' => $user,
            'formRows' => $this->publicRows($savedForm?->fields),
        ]);
    }

    public function submit(Request $request, User $user, string $token): RedirectResponse
    {
        abort_unless(hash_equals($this->shareToken($user), $token), 404);

        if (! Schema::hasTable('ordering_smart_form_submissions')) {
            return redirect()
                ->route('forms.public', ['user' => $user, 'token' => $token])
                ->with('status', 'submission-storage-unavailable');
        }

        $savedForm = Schema::hasTable('ordering_smart_forms')
            ? $user->orderingSmartForm
            : null;
        $formRows = $this->publicRows($savedForm?->fields);

        if ($formRows === []) {
            return redirect()
                ->route('forms.public', ['user' => $user, 'token' => $token])
                ->with('status', 'form-not-ready');
        }

        $rules = [];

        foreach ($formRows as $row) {
            $key = 'answers.'.$row['id'];

            if ($row['type'] === 'checkbox') {
                $rules[$key] = ['nullable', 'array'];
                foreach ($row['options'] as $option) {
                    // placeholder to keep rule branch aligned
                }
                $rules[$key.'.*'] = ['string', 'in:'.implode(',', $row['options'])];
                continue;
            }

            if ($row['type'] === 'dropdown' || $row['type'] === 'radio button') {
                $rules[$key] = ['nullable', 'string', 'in:'.implode(',', $row['options'])];
                continue;
            }

            if ($row['type'] === 'number') {
                $rules[$key] = ['nullable', 'integer'];
                continue;
            }

            if ($row['type'] === 'money') {
                $rules[$key] = ['nullable', 'numeric'];
                continue;
            }

            if ($row['type'] === 'date') {
                $rules[$key] = ['nullable', 'date'];
                continue;
            }

            $rules[$key] = ['nullable', 'string'];
        }

        $validated = $request->validate($rules);
        $answers = $validated['answers'] ?? [];

        $payload = collect($formRows)
            ->map(function (array $row) use ($answers): array {
                return [
                    'id' => $row['id'],
                    'title' => $row['title'],
                    'type' => $row['type'],
                    'value' => $answers[$row['id']] ?? null,
                ];
            })
            ->values()
            ->all();

        OrderingSmartFormSubmission::query()->create([
            'user_id' => $user->id,
            'payload' => $payload,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return redirect()
            ->route('forms.public', ['user' => $user, 'token' => $token])
            ->with('status', 'submitted');
    }

    private function shareUrl(User $user): string
    {
        return URL::route('forms.public', [
            'user' => $user,
            'token' => $this->shareToken($user),
        ]);
    }

    private function shareToken(User $user): string
    {
        return substr(hash_hmac(
            'sha256',
            'ordering-smart-form|'.$user->getKey().'|'.$user->email,
            (string) config('app.key')
        ), 0, 24);
    }

    private function normalizedRows(?array $rows): array
    {
        $rows = collect($rows ?? [])
            ->filter(fn ($row) => is_array($row))
            ->map(function (array $row, int $index): array {
                $type = (string) ($row['type'] ?? 'text');
                $options = collect($row['options'] ?? [])
                    ->map(fn ($value) => trim((string) $value))
                    ->filter()
                    ->values()
                    ->all();

                $optionsText = trim((string) ($row['options_text'] ?? implode("\n", $options)));

                return [
                    'id' => (int) ($row['id'] ?? ($index + 1)),
                    'title' => trim((string) ($row['title'] ?? '')),
                    'type' => $type !== '' ? $type : 'text',
                    'options_text' => in_array($type, ['dropdown', 'radio button', 'checkbox'], true)
                        ? $optionsText
                        : '',
                ];
            })
            ->values()
            ->all();

        if ($rows === []) {
            return [
                ['id' => 1, 'title' => '', 'type' => 'text', 'options_text' => ''],
            ];
        }

        return $rows;
    }

    private function publicRows(?array $rows): array
    {
        return collect($this->normalizedRows($rows))
            ->map(function (array $row): array {
                $options = collect(preg_split('/\r\n|\r|\n/', (string) ($row['options_text'] ?? '')) ?: [])
                    ->map(fn ($value) => trim((string) $value))
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'id' => $row['id'],
                    'title' => $row['title'],
                    'type' => $row['type'],
                    'options' => $options,
                ];
            })
            ->filter(fn (array $row) => $row['title'] !== '')
            ->values()
            ->all();
    }

    private function submissionTableRows(array $submissions): array
    {
        return collect($submissions)
            ->map(function (OrderingSmartFormSubmission $submission): array {
                $payload = collect($submission->payload ?? []);
                $customerAnswer = $payload->first(function (array $answer): bool {
                    $title = strtolower(trim((string) ($answer['title'] ?? '')));

                    return str_contains($title, 'customer') || str_contains($title, 'name');
                });

                $fallbackAnswer = $payload->first(function (array $answer): bool {
                    $value = $answer['value'] ?? null;

                    if (is_array($value)) {
                        return count(array_filter($value, fn ($item) => trim((string) $item) !== '')) > 0;
                    }

                    return trim((string) $value) !== '';
                });

                return [
                    'customer' => $this->displayAnswerValue($customerAnswer['value'] ?? null)
                        ?: $this->displayAnswerValue($fallbackAnswer['value'] ?? null)
                        ?: 'Unknown',
                    'day_submitted' => $this->relativeSubmissionDay($submission->submitted_at),
                    'submitted_at' => $submission->submitted_at,
                    'submitted_at_display' => $submission->submitted_at?->format('d M Y, h:i A'),
                    'answer_count' => $payload
                        ->filter(fn (array $answer): bool => trim((string) ($answer['title'] ?? '')) !== '')
                        ->count(),
                    'answers' => $payload
                        ->map(fn (array $answer): array => [
                            'title' => (string) ($answer['title'] ?? ''),
                            'value' => $this->displayAnswerValue($answer['value'] ?? null),
                        ])
                        ->filter(fn (array $answer): bool => $answer['title'] !== '')
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    private function displayAnswerValue(mixed $value): string
    {
        if (is_array($value)) {
            return implode(', ', array_filter(array_map(fn ($item) => trim((string) $item), $value)));
        }

        return trim((string) $value);
    }

    private function relativeSubmissionDay(mixed $submittedAt): string
    {
        if (! $submittedAt) {
            return 'Unknown';
        }

        if ($submittedAt->isToday()) {
            return 'Today';
        }

        if ($submittedAt->isYesterday()) {
            return 'Yesterday';
        }

        $days = now()->startOfDay()->diffInDays($submittedAt->copy()->startOfDay());

        return 'Last '.$days.' days';
    }
}
