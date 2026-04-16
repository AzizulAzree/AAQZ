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
                <p class="sticky-note-status" x-text="statusLabel"></p>
            </div>

            <div class="sticky-note-actions">
                <button
                    type="button"
                    class="sticky-note-icon-button"
                    @click.stop="toggleCollapse()"
                    :aria-expanded="(! isCollapsed).toString()"
                    :title="isCollapsed ? '{{ __('Expand note') }}' : '{{ __('Collapse note') }}'"
                >
                    <img
                        :src="isCollapsed ? @js(asset('images/sticky-note-expand-bear.svg')) : @js(asset('images/sticky-note-collapse-bear.svg'))"
                        :alt="isCollapsed ? '{{ __('Expand note') }}' : '{{ __('Collapse note') }}'"
                        class="sticky-note-icon-image"
                    >
                </button>
            </div>
        </header>

        <div class="sticky-note-body" x-show="! isCollapsed" x-transition.opacity.duration.150ms>
            <label class="sr-only" for="sticky-note-editor">{{ __('Sticky note content') }}</label>
            <textarea
                id="sticky-note-editor"
                class="sticky-note-editor"
                x-model="content"
                @input="queueSave()"
                rows="10"
                placeholder="{{ __('Write anything here. Changes save automatically.') }}"
            ></textarea>
        </div>
    </section>
</div>
