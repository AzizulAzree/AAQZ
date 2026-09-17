export default (workspaces) => ({
    workspaces, workspaceId: workspaces[0]?.id ?? '', folderId: null, page: 0,
    note: null, loading: false, error: '', requestId: 0, returnFocus: null,
    get workspace() { return this.workspaces.find(w => w.id === Number(this.workspaceId)); },
    get folder() { return this.workspace?.nodes.find(n => n.id === this.folderId); },
    get items() { return (this.workspace?.nodes ?? []).filter(n => n.parent_id === this.folderId); },
    get visibleItems() { return this.items.slice(this.page * 6, this.page * 6 + 6); },
    get pageCount() { return Math.ceil(this.items.length / 6); },
    get workspaceUrl() { return this.workspace ? this.workspace.url + (this.folderId ? `&folder=${this.folderId}` : '') : '/project'; },
    changeWorkspace() { this.folderId = null; this.page = 0; },
    openFolder(item) { this.folderId = item.id; this.page = 0; },
    back() { this.folderId = this.folder?.parent_id ?? null; this.page = 0; },
    async openNote(item) {
        this.returnFocus = document.activeElement;
        this.note = { ...item, content: '' }; this.loading = true; this.error = '';
        const requestId = ++this.requestId;
        this.$nextTick(() => document.getElementById('dw-note-close')?.focus());
        try {
            const response = await fetch(item.url, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error('This note could not be loaded. Please try again.');
            const result = await response.json();
            if (requestId === this.requestId) this.note = { ...item, ...result };
        } catch (error) {
            if (requestId === this.requestId) this.error = error.message;
        } finally {
            if (requestId === this.requestId) this.loading = false;
        }
    },
    closeNote() { ++this.requestId; this.note = null; this.loading = false; this.error = ''; this.$nextTick(() => this.returnFocus?.focus()); },
    trap(event) {
        if (event.key !== 'Tab') return;
        const elements = [...event.currentTarget.querySelectorAll('button,[tabindex="0"]')].filter(el => el.offsetParent !== null);
        const first = elements[0], last = elements.at(-1);
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
        if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
    },
});
