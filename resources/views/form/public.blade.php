<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Customer form</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="fm-public antialiased">
<main class="fm-public-shell">
    <header class="fm-public-heading"><span class="fm-symbol"><x-project-icon name="form"/></span><h1>Customer form</h1><p>Send your details to {{ $owner->name }}.</p></header>
    @if (session('status') === 'submitted')<p class="fm-notice" role="status">Thank you. Your response has been received.</p>@endif
    @if (in_array(session('status'), ['submission-storage-unavailable', 'form-not-ready']))<p class="fm-error" role="alert">This form is temporarily unavailable. Please try again later.</p>@endif
    @if (count($formRows) === 0)
        <section class="fm-public-form fm-empty"><h2>This form isn’t ready yet</h2><p>Please check back later.</p></section>
    @else
        <form method="POST" action="{{ route('forms.public.submit', ['user' => $owner, 'token' => request()->route('token')]) }}" class="fm-public-form" x-data="{ busy: false }" @submit="busy = true">
            @csrf
            @foreach ($formRows as $row)
                <fieldset class="fm-public-fields">
                    @if (in_array($row['type'], ['radio button', 'checkbox']))
                        <legend>{{ $row['title'] }} @if ($row['required'])<span class="fq-required-star" aria-label="Required">*</span>@endif</legend>
                        @foreach ($row['options'] as $option)
                            <label class="fm-choice"><input @required($row['required'] && $row['type'] === 'radio button') type="{{ $row['type'] === 'checkbox' ? 'checkbox' : 'radio' }}" name="answers[{{ $row['id'] }}]{{ $row['type'] === 'checkbox' ? '[]' : '' }}" value="{{ $option }}" @checked($row['type'] === 'checkbox' ? in_array($option, (array) old('answers.'.$row['id'], []), true) : old('answers.'.$row['id']) === $option)><span>{{ $option }}</span></label>
                        @endforeach
                    @else
                        <label for="field-{{ $row['id'] }}">{{ $row['title'] }} @if ($row['required'])<span class="fq-required-star" aria-label="Required">*</span>@endif</label>
                        @if ($row['type'] === 'dropdown')
                            <select @required($row['required']) id="field-{{ $row['id'] }}" name="answers[{{ $row['id'] }}]"><option value="">Select an option</option>@foreach ($row['options'] as $option)<option value="{{ $option }}" @selected(old('answers.'.$row['id']) === $option)>{{ $option }}</option>@endforeach</select>
                        @else
                            <input @required($row['required']) id="field-{{ $row['id'] }}" type="{{ in_array($row['type'], ['money', 'number']) ? 'number' : $row['type'] }}" name="answers[{{ $row['id'] }}]" value="{{ old('answers.'.$row['id']) }}" @if ($row['type'] === 'money') step="0.01" min="0" inputmode="decimal" @elseif ($row['type'] === 'number') step="1" inputmode="numeric" @endif>
                        @endif
                    @endif
                    <x-input-error class="mt-2" :messages="$errors->get('answers.'.$row['id'])"/><x-input-error class="mt-2" :messages="$errors->get('answers.'.$row['id'].'.*')"/>
                </fieldset>
            @endforeach
            <div class="fm-public-submit"><button class="fm-button fm-primary" type="submit" :disabled="busy" x-text="busy ? 'Sending…' : 'Send response'">Send response</button></div>
        </form>
    @endif
</main>
</body>
</html>

