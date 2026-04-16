import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('stickyNote', (config) => ({
        updateUrl: config.updateUrl,
        content: config.initialContent ?? '',
        positionX: Number(config.initialPositionX ?? 24),
        positionY: Number(config.initialPositionY ?? 96),
        isCollapsed: Boolean(config.initialCollapsed),
        isDragging: false,
        isSaving: false,
        isSaved: false,
        saveTimer: null,
        dragOffsetX: 0,
        dragOffsetY: 0,

        get panelStyle() {
            return `transform: translate3d(${this.positionX}px, ${this.positionY}px, 0);`;
        },

        get statusLabel() {
            if (this.isDragging) {
                return 'Dragging';
            }

            if (this.isSaving) {
                return 'Saving...';
            }

            if (this.isSaved) {
                return 'Saved';
            }

            return 'Autosave on';
        },

        init() {
            this.clampPosition();

            window.addEventListener('resize', () => {
                this.clampPosition();
            });
        },

        startDrag(event) {
            if (event.target.closest('button')) {
                return;
            }

            this.isDragging = true;
            this.isSaved = false;

            this.dragOffsetX = event.clientX - this.positionX;
            this.dragOffsetY = event.clientY - this.positionY;

            const move = (moveEvent) => {
                this.positionX = moveEvent.clientX - this.dragOffsetX;
                this.positionY = moveEvent.clientY - this.dragOffsetY;
                this.clampPosition();
            };

            const stop = () => {
                this.isDragging = false;
                window.removeEventListener('pointermove', move);
                window.removeEventListener('pointerup', stop);
                this.queueSave(true);
            };

            window.addEventListener('pointermove', move);
            window.addEventListener('pointerup', stop, { once: true });
        },

        toggleCollapse() {
            this.isCollapsed = ! this.isCollapsed;
            this.clampPosition();
            this.queueSave(true);
        },

        queueSave(immediate = false) {
            this.isSaved = false;

            if (this.saveTimer) {
                window.clearTimeout(this.saveTimer);
            }

            if (immediate) {
                this.save();
                return;
            }

            this.saveTimer = window.setTimeout(() => {
                this.save();
            }, 450);
        },

        async save() {
            if (this.saveTimer) {
                window.clearTimeout(this.saveTimer);
                this.saveTimer = null;
            }

            this.isSaving = true;

            try {
                await window.axios.put(this.updateUrl, {
                    content: this.content,
                    position_x: Math.round(this.positionX),
                    position_y: Math.round(this.positionY),
                    is_collapsed: this.isCollapsed,
                });

                this.isSaved = true;
            } catch (error) {
                this.isSaved = false;
                console.error('Sticky note autosave failed.', error);
            } finally {
                this.isSaving = false;
            }
        },

        clampPosition() {
            const collapsedHeight = 68;
            const expandedHeight = 320;
            const noteWidth = window.innerWidth < 640 ? Math.min(window.innerWidth - 24, 280) : 320;
            const noteHeight = this.isCollapsed ? collapsedHeight : expandedHeight;
            const maxX = Math.max(12, window.innerWidth - noteWidth - 12);
            const maxY = Math.max(12, window.innerHeight - noteHeight - 12);

            this.positionX = Math.min(Math.max(12, this.positionX), maxX);
            this.positionY = Math.min(Math.max(12, this.positionY), maxY);
        },
    }));
});

Alpine.start();
