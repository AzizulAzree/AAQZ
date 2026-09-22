<x-app-layout>
    <div class="fm-page" x-data="formsPage(@js($formRows), @js($submissions), @js($shareUrl))" data-save-url="{{ route('forms.store') }}"
         @keydown.escape.window="if (editor || active) close()" @beforeunload.window="if (dirty) { $event.preventDefault(); $event.returnValue = ''; }">
        <div :inert="editor || !!active">
            <header class="fm-heading">
                <div><p class="fm-eyebrow">COLLECT & CONNECT</p><h1>Forms</h1><p>Your customer form and the responses it receives.</p></div>
                <button class="fm-button fm-primary" type="button" @click="openEditor()"><x-project-icon name="edit"/> Edit form</button>
            </header>
            <p class="fm-notice" role="status" x-show="notice" x-text="notice" x-cloak></p>
            @if (session('status') === 'form-saved')<p class="fm-notice" role="status">Form saved.</p>@endif
            @if (session('status') === 'form-storage-unavailable')<p class="fm-error" role="alert">Your form could not be saved. Please try again later.</p>@endif
            <section class="fm-share" aria-labelledby="fm-form-title">
                <div class="fm-form-summary"><span class="fm-symbol"><x-project-icon name="form"/></span><div><h2 id="fm-form-title">Customer form</h2><p><span x-text="fieldCount"></span> fields <span aria-hidden="true">·</span> <span x-text="fieldCount ? 'Ready for responses' : 'Add fields to get started'"></span></p></div></div>
                <div class="fm-share-controls"><label class="sr-only" for="fm-share-url">Customer form link</label><div class="fm-url"><x-project-icon name="link"/><input id="fm-share-url" x-ref="shareLink" readonly value="{{ $shareUrl }}" @focus="$el.select()"></div><button class="fm-button" type="button" @click="copyLink()" x-text="copied ? 'Copied' : 'Copy link'" aria-live="polite">Copy link</button><a class="fm-open" href="{{ $shareUrl }}" target="_blank" rel="noopener">Open form <x-project-icon name="arrow"/></a></div>
            </section>
            <section class="fm-responses" aria-labelledby="fm-responses-title">
                <div class="fm-section-heading"><h2 id="fm-responses-title">Responses <span>{{ $submissionPages?->total() ?? 0 }}</span></h2><p>Newest first</p></div>
                @if (count($submissions))
                    <div class="fm-list-labels" aria-hidden="true"><span>Customer</span><span>Received</span><span>Answers</span><span></span></div>
                    <div class="fm-response-list">
                        <template x-for="response in submissions" :key="response.id">
                            <button class="fm-response" type="button" @click="openResponse(response)">
                                <span class="fm-customer"><span class="fm-avatar" aria-hidden="true" x-text="response.customer.slice(0,1).toUpperCase()"></span><span><strong x-text="response.customer"></strong><small x-text="'Response #' + response.id"></small></span></span>
                                <span class="fm-received"><span x-text="response.submitted_at_display"></span><small x-text="response.day_submitted"></small></span>
                                <span class="fm-answer-count" x-text="response.answer_count + ' answered'"></span><x-project-icon name="chevron"/>
                            </button>
                        </template>
                    </div>
                    @if ($submissionPages?->hasPages())
                        <nav class="fm-pagination" aria-label="Response pages">
                            @if ($submissionPages->previousPageUrl())<a class="fm-button" href="{{ $submissionPages->previousPageUrl() }}">Previous</a>@else<span></span>@endif
                            <span>{{ $submissionPages->currentPage() }} / {{ $submissionPages->lastPage() }}</span>
                            @if ($submissionPages->nextPageUrl())<a class="fm-button" href="{{ $submissionPages->nextPageUrl() }}">Next</a>@else<span></span>@endif
                        </nav>
                    @endif
                @else
                    <div class="fm-empty"><span class="fm-symbol"><x-project-icon name="form"/></span><h3>No responses yet</h3><p x-text="fieldCount ? 'Share your form to receive your first response.' : 'Add your fields, then share the form with your customers.'"></p><button type="button" class="fm-button" @click="fieldCount ? copyLink() : openEditor()" x-text="fieldCount ? (copied ? 'Copied' : 'Copy form link') : 'Create your form'"></button></div>
                @endif
            </section>
        </div>
        <template x-if="editor">
            <div class="fm-overlay" @click.self="close()">
                <section class="fm-dialog fm-editor" role="dialog" aria-modal="true" aria-labelledby="fm-editor-title" @keydown="trap($event)">
                    @include('form.question-editor')
                    <footer class="fm-dialog-footer">
                        <p class="fm-error" x-show="error" x-text="error" role="alert"></p>
                        <div class="fm-warning" x-show="warning" role="alert" x-cloak><p x-text="warning?.type === 'discard' ? 'Discard your unsaved changes?' : 'Delete this question? Previously submitted answers will stay available.'"></p><div><button class="fm-button" type="button" @click="warning = null">Keep editing</button><button class="fm-button fm-danger" type="button" @click="confirmWarning()" x-text="warning?.type === 'discard' ? 'Discard changes' : 'Delete question'"></button></div></div>
                        <div class="fm-footer-actions" x-show="!warning"><span x-text="dirty ? 'Unsaved changes' : 'All changes saved'"></span><div><button class="fm-button" type="button" @click="close()" :disabled="busy">Cancel</button><button class="fm-button fm-primary" type="button" @click="save()" :disabled="busy" x-text="busy ? 'Saving…' : 'Save form'"></button></div></div>
                    </footer>
                </section>
            </div>
        </template>
        <template x-if="active">
            <div class="fm-overlay" @click.self="close()"><section class="fm-dialog fm-reader" role="dialog" aria-modal="true" aria-labelledby="fm-response-title" @keydown="trap($event)"><header class="fm-dialog-heading"><div><p class="fm-eyebrow" x-text="'RESPONSE #' + active.id"></p><h2 id="fm-response-title" x-ref="responseTitle" tabindex="-1" x-text="active.customer"></h2><p x-text="active.submitted_at_display"></p></div><button class="fm-icon-button" type="button" aria-label="Close response" @click="close()"><span aria-hidden="true">×</span></button></header><dl class="fm-response-details"><template x-for="(answer, index) in active.answers" :key="index"><div><dt x-text="answer.title"></dt><dd x-text="answer.value || 'No answer'" :class="{ 'fm-muted': !answer.value }"></dd></div></template></dl></section></div>
        </template>
    </div>
</x-app-layout>

