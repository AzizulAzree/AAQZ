export default (workspaces, recent) => ({
    workspaces, recent, activeId: workspaces[0]?.id ?? null, folderId: null, query: '', mobileWorkspaceOpen: false,
    workspaceModalOpen: false, nodeModalOpen: false, nodeType: 'folder', nodeWorkspaceId: '', nodeParentId: '', nodeParentName: '',
    manage: null, manageName: '', manageUrl: '', manageDescription: '', manageError: '', busy: false, returnFocus: null,
    noteOpen: false, noteEditing: false, noteItem: null, noteName: '', noteContent: '', noteError: '', discardNote: false, addItemOpen: false,
    get noteDirty() { return this.noteOpen && (this.noteName !== (this.noteItem?.name ?? '') || this.noteContent !== (this.noteItem?.content ?? '')); },
    openNote(item = null, editing = false) {
        this.returnFocus = document.activeElement.closest('.pr-item-actions')?.querySelector('.pr-more-button') ?? document.activeElement;
        this.addItemOpen = false; this.noteItem = item; this.noteName = item?.name ?? ''; this.noteContent = item?.content ?? ''; this.noteError = ''; this.discardNote = false; this.noteOpen = true;
        this.noteEditing = !item || editing;
        this.$nextTick(() => document.getElementById(this.noteEditing ? 'pr-note-name' : 'pr-note-paper')?.focus());
    },
    editNote() {
        this.noteEditing = true;
        this.$nextTick(() => document.getElementById('pr-note-name')?.focus());
    },
    async saveNote() {
        if (this.busy) return;
        this.busy = true; this.noteError = '';
        try {
            const response = await fetch(this.noteItem?.manage_url ?? this.$root.dataset.storeNodeUrl, {
                method: this.noteItem ? 'PATCH' : 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ type: 'note', workspace_id: this.activeId, parent_id: this.folderId, name: this.noteName, content: this.noteContent })
            });
            const result = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(result.errors ? Object.values(result.errors).flat().join(' ') : 'Unable to save your note. Please try again.');
            this.noteOpen = false;
            location.assign(result.redirect);
        } catch (error) { this.noteError = error.message; this.busy = false; }
    },
    discardNoteChanges() {
        this.noteOpen = false; this.discardNote = false;
        this.$nextTick(() => this.returnFocus?.focus());
    },
    openManage(mode, item, kind) {
        if (kind === 'note' && mode === 'edit') { this.openNote(item, true); return; }
        this.returnFocus = document.activeElement.closest('.pr-item-actions')?.querySelector('.pr-more-button') ?? document.activeElement;
        this.manage = { mode, item, kind };
        this.manageName = item.name; this.manageUrl = item.url ?? ''; this.manageDescription = item.description ?? ''; this.manageError = '';
        this.$nextTick(() => document.getElementById(mode === 'edit' ? 'manage-name' : 'manage-cancel')?.focus());
    },
    get deleteWarning() {
        if (!this.manage) return '';
        const { item, kind } = this.manage;
        if (kind === 'link') return 'This removes the saved link from this workspace and Recently opened. The original website or document stays unchanged.';
        if (kind === 'note') return 'This permanently deletes this note and all of its text.';
        const count = nodes => nodes.reduce((sum, n) => sum + 1 + count(n.children ?? []), 0);
        const total = kind === 'workspace' ? item.folder_count + item.shortcut_count + (item.note_count ?? 0) : count(item.children ?? []);
        return `This permanently deletes this ${kind} and all ${total} items inside it, including nested folders, saved links, and notes. Linked websites and documents stay unchanged.`;
    },
    async submitManage() {
        if (this.busy || !this.manage) return;
        this.busy = true; this.manageError = '';
        try {
            const response = await fetch(this.manage.item.manage_url, {
                method: this.manage.mode === 'delete' ? 'DELETE' : 'PATCH',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify(this.manage.mode === 'delete' ? { confirmed: true } : { name: this.manageName, url: this.manageUrl, description: this.manageDescription })
            });
            const result = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(result.errors ? Object.values(result.errors).flat().join(' ') : result.message ?? 'Unable to save changes. Refresh the page and try again.');
            location.assign(result.redirect);
        } catch (error) { this.manageError = error.message; this.busy = false; }
    },
    init() {
        const params = new URLSearchParams(location.search);
        const id = Number(params.get('workspace'));
        if (this.workspaces.some(w => w.id === id)) this.activeId = id;
        const folder = Number(params.get('folder'));
        if (this.entries.some(n => n.id === folder && n.type === 'folder')) this.folderId = folder;
    },
    get active() { return this.workspaces.find(w => w.id === this.activeId); },
    get entries() {
        const walk = (nodes, trail = []) => nodes.flatMap(n => [{ ...n, trail }, ...(n.children ? walk(n.children, [...trail, { id: n.id, name: n.name }]) : [])]);
        return walk(this.active?.folders ?? []);
    },
    get folder() { return this.entries.find(n => n.id === this.folderId); },
    get crumbs() { return this.folder ? [...this.folder.trail, this.folder] : []; },
    get parentFolder() { return this.folder?.trail.at(-1) ?? null; },
    get items() {
        const query = this.query.trim().toLowerCase();
        if (query) return this.entries.filter(n => `${n.name} ${n.description ?? ''}`.toLowerCase().includes(query));
        return this.folder?.children ?? this.active?.folders ?? [];
    },
    get folders() { return this.items.filter(n => n.type === 'folder'); },
    get links() { return this.items.filter(n => n.type === 'shortcut'); },
    get notes() { return this.items.filter(n => n.type === 'note'); },
    navigate(workspaceId, folderId = null) {
        this.activeId = workspaceId; this.folderId = folderId; this.query = ''; this.mobileWorkspaceOpen = false; this.addItemOpen = false;
        const url = new URL(location.href);
        url.searchParams.set('workspace', workspaceId);
        folderId ? url.searchParams.set('folder', folderId) : url.searchParams.delete('folder');
        history.replaceState(null, '', url);
    },
    openWorkspaceModal() { this.workspaceModalOpen = true; this.$nextTick(() => document.getElementById('workspace-name')?.focus()); },
    openNodeModal(type) {
        this.addItemOpen = false;
        this.nodeType = type; this.nodeWorkspaceId = this.activeId; this.nodeParentId = this.folderId ?? ''; this.nodeParentName = this.folder?.name ?? this.active?.name ?? '';
        this.nodeModalOpen = true; this.$nextTick(() => document.getElementById('node-name')?.focus());
    },
    closeModals() {
        if (this.busy) return;
        if (this.noteOpen) {
            if (this.discardNote) { this.discardNote = false; this.$nextTick(() => document.getElementById('pr-note-name')?.focus()); return; }
            if (this.noteDirty) { this.discardNote = true; this.$nextTick(() => document.getElementById('pr-keep-editing')?.focus()); return; }
            this.discardNoteChanges(); return;
        }
        this.addItemOpen = false; this.workspaceModalOpen = false; this.nodeModalOpen = false; this.manage = null; this.$nextTick(() => this.returnFocus?.focus());
    },
    trapFocus(event) {
        if (event.key !== 'Tab') return;
        const items = [...event.currentTarget.querySelectorAll('button,input,textarea,a[href],[tabindex="0"]')].filter(el => el.offsetParent !== null && !el.disabled);
        const first = items[0], last = items[items.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
        if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
    }
});
