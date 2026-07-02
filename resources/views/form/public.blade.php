<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('Ordering Smart Form') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
        <main class="mx-auto flex min-h-screen max-w-4xl items-center px-4 py-12 sm:px-6 lg:px-8">
            <section class="w-full overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm">
                <div class="project-shell">
                    <div class="project-shell-copy">
                        <p class="project-shell-kicker">{{ __('Ordering Smart Form') }}</p>
                        <h1 class="project-shell-title">{{ __('Customer Order Request') }}</h1>
                        <p class="project-shell-description">
                            {{ __('Fill in the details below for :name. The saved form structure is shown here exactly from the shared ordering setup.', ['name' => $owner->name]) }}
                        </p>
                    </div>
                </div>

                <div class="border-t border-slate-200/80 bg-slate-50/70 px-5 py-5 sm:px-6">
                    @if (session('status') === 'submitted')
                        <p class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                            {{ __('Your response has been submitted.') }}
                        </p>
                    @elseif (session('status') === 'submission-storage-unavailable')
                        <p class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            {{ __('Submission storage is not ready yet.') }}
                        </p>
                    @elseif (session('status') === 'form-not-ready')
                        <p class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            {{ __('This form is not ready for submissions yet.') }}
                        </p>
                    @endif

                    @if (count($formRows) === 0)
                        <div class="rounded-2xl border border-slate-200 bg-white px-5 py-5 text-sm leading-6 text-slate-600 shadow-sm">
                            {{ __('This form is not ready yet. The owner has not added any visible fields yet.') }}
                        </div>
                    @else
                        <form method="POST" action="{{ route('forms.public.submit', ['user' => $owner, 'token' => request()->route('token')]) }}" class="space-y-4">
                            @csrf
                            @foreach ($formRows as $row)
                                <section class="rounded-2xl border border-slate-200 bg-white px-5 py-5 shadow-sm">
                                    <label for="field-{{ $row['id'] }}" class="block text-sm font-semibold text-slate-800">
                                        {{ $row['title'] }}
                                    </label>

                                    @if ($row['type'] === 'text')
                                        <input
                                            id="field-{{ $row['id'] }}"
                                            type="text"
                                            name="answers[{{ $row['id'] }}]"
                                            value="{{ old('answers.'.$row['id']) }}"
                                            class="mt-3 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-slate-300 focus:ring-slate-300"
                                        >
                                    @elseif ($row['type'] === 'number')
                                        <input
                                            id="field-{{ $row['id'] }}"
                                            type="number"
                                            name="answers[{{ $row['id'] }}]"
                                            value="{{ old('answers.'.$row['id']) }}"
                                            class="mt-3 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-slate-300 focus:ring-slate-300"
                                        >
                                    @elseif ($row['type'] === 'money')
                                        <input
                                            id="field-{{ $row['id'] }}"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            name="answers[{{ $row['id'] }}]"
                                            value="{{ old('answers.'.$row['id']) }}"
                                            placeholder="{{ __('Amount') }}"
                                            class="mt-3 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-slate-300 focus:ring-slate-300"
                                        >
                                    @elseif ($row['type'] === 'date')
                                        <input
                                            id="field-{{ $row['id'] }}"
                                            type="date"
                                            name="answers[{{ $row['id'] }}]"
                                            value="{{ old('answers.'.$row['id']) }}"
                                            class="mt-3 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-slate-300 focus:ring-slate-300"
                                        >
                                    @elseif ($row['type'] === 'dropdown')
                                        <select
                                            id="field-{{ $row['id'] }}"
                                            name="answers[{{ $row['id'] }}]"
                                            class="mt-3 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-slate-300 focus:ring-slate-300"
                                        >
                                            <option value="">{{ __('Select an option') }}</option>
                                            @foreach ($row['options'] as $option)
                                                <option value="{{ $option }}" @selected(old('answers.'.$row['id']) === $option)>{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    @elseif ($row['type'] === 'radio button')
                                        <div class="mt-3 space-y-2">
                                            @foreach ($row['options'] as $optionIndex => $option)
                                                <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                                    <input
                                                        type="radio"
                                                        name="answers[{{ $row['id'] }}]"
                                                        value="{{ $option }}"
                                                        @checked(old('answers.'.$row['id']) === $option)
                                                        class="border-slate-300 text-slate-800 focus:ring-slate-400"
                                                    >
                                                    <span>{{ $option }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @elseif ($row['type'] === 'checkbox')
                                        <div class="mt-3 space-y-2">
                                            @foreach ($row['options'] as $optionIndex => $option)
                                                <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                                    <input
                                                        type="checkbox"
                                                        name="answers[{{ $row['id'] }}][]"
                                                        value="{{ $option }}"
                                                        @checked(in_array($option, old('answers.'.$row['id'], []), true))
                                                        class="rounded border-slate-300 text-slate-800 focus:ring-slate-400"
                                                    >
                                                    <span>{{ $option }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @endif

                                    <x-input-error class="mt-2" :messages="$errors->get('answers.'.$row['id'])" />
                                    <x-input-error class="mt-2" :messages="$errors->get('answers.'.$row['id'].'.*')" />
                                </section>
                            @endforeach

                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                >
                                    {{ __('Submit') }}
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </section>
        </main>
    </body>
</html>
