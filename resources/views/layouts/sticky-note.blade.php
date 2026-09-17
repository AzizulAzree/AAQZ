@php
    $stickyNote = auth()->user()?->stickyNote;
@endphp

<div
    x-data="stickyNote({
        updateUrl: @js(route('sticky-note.update')),
        initialContent: @js($stickyNote?->content ?? ''),
        initialPositionX: @js($stickyNote?->position_x ?? 24),
        initialPositionY: @js($stickyNote?->position_y ?? 96),
        initialCollapsed: @js((bool) ($stickyNote?->is_collapsed ?? false)),
    })"
    x-init="init()"
    @selectionchange.document="rememberSelection()"
    x-cloak
    class="sticky-note-shell"
    :style="panelStyle"
>
    <section class="sticky-note-card" :class="{ 'sticky-note-card-collapsed': isCollapsed, 'sticky-note-card-dragging': isDragging }">
        <header
            class="sticky-note-header"
            @pointerdown="startDrag($event)"
        >
            <div>
                <p class="sticky-note-kicker">{{ __('Sticky note') }}</p>
            </div>

            <div class="sticky-note-actions">
                <button
                    type="button"
                    class="sticky-note-icon-button"
                    @click.stop="toggleCollapse()"
                    :aria-expanded="(! isCollapsed).toString()"
                    :aria-label="isCollapsed ? '{{ __('Expand note') }}' : '{{ __('Collapse note') }}'"
                    aria-controls="sticky-note-body"
                >
                    <img
                        :src="isCollapsed ? @js(asset('images/sticky-note-expand-bear.svg')) : @js(asset('images/sticky-note-collapse-bear.svg'))"
                        alt=""
                        class="sticky-note-icon-image"
                    >
                </button>
            </div>
        </header>

        <div id="sticky-note-body" class="sticky-note-body" x-show="! isCollapsed" x-transition.opacity.duration.150ms>
            <label class="sr-only" for="sticky-note-editor">{{ __('Sticky note content') }}</label>
            <div
                id="sticky-note-editor"
                x-ref="editor"
                class="sticky-note-editor"
                contenteditable="true"
                role="textbox"
                aria-label="{{ __('Sticky note content') }}"
                aria-multiline="true"
                data-placeholder="{{ __('Write a note…') }}"
                @input="queueSaveFromEditor()"
                @blur="rememberSelection(); queueSave(true)"
                @keyup="rememberSelection()"
                @mouseup="rememberSelection()"
                @touchend="rememberSelection()"
                @paste="handlePaste($event)"
            ></div>
            <div class="sticky-note-toolbar">
                <div class="sticky-note-formatting" role="group" aria-label="{{ __('Text formatting') }}">
                    <template x-for="option in formatOptions" :key="option.command">
                        <button type="button" class="sticky-note-toolbar-button" :aria-label="option.label" :aria-pressed="option.command === 'removeFormat' ? null : Boolean(activeFormats[option.command]).toString()" @mousedown.prevent="rememberSelection()" @click="applyFormat(option.command)">
                            <span x-show="! option.command.includes('List')" class="sticky-note-format-symbol" :data-format="option.command" aria-hidden="true" x-text="option.icon"></span>
                            <svg x-show="option.command.includes('List')" class="sticky-note-list-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true">
                                <path d="M8 5h9M8 10h9M8 15h9" />
                                <path x-show="option.command === 'insertUnorderedList'" d="M3 5h.5M3 10h.5M3 15h.5" stroke-width="2.5" />
                                <text x-show="option.command === 'insertOrderedList'" x="1" y="7" fill="currentColor" stroke="none" font-size="7">1</text>
                                <text x-show="option.command === 'insertOrderedList'" x="1" y="16" fill="currentColor" stroke="none" font-size="7">2</text>
                            </svg>
                        </button>
                    </template>
                </div>
                <p class="sticky-note-status" role="status" x-text="statusLabel"></p>
            </div>
        </div>
    </section>
</div>
