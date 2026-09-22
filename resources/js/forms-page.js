export default (rows, submissions, shareUrl) => ({
    savedRows: rows, rows: [], submissions, shareUrl, editor: false, active: null,
    warning: null, busy: false, error: '', notice: '', copied: false, returnFocus: null,
    selectedId: null, preview: false,
    get fieldCount() { return this.savedRows.filter(row => row.title.trim()).length; },
    get dirty() { return this.editor && JSON.stringify(this.rows) !== JSON.stringify(this.savedRows); },
    isChoice(type) { return ['dropdown', 'radio button', 'checkbox'].includes(type); },
    openEditor() {
        this.returnFocus = document.activeElement;
        this.rows = JSON.parse(JSON.stringify(this.savedRows));
        this.error = ''; this.warning = null; this.editor = true;
        this.selectedId = this.rows[0]?.id ?? null; this.preview = false;
        this.$nextTick(() => this.$refs.editorTitle.focus());
    },
    addField() {
        if (this.rows.length >= 100) return;
        const row = { id: this.nextId(), title: '', type: 'text', options_text: '', required: false };
        const index = this.rows.findIndex(item => item.id === this.selectedId);
        this.rows.splice(index < 0 ? this.rows.length : index + 1, 0, row);
        this.selectQuestion(row.id);
    },
    nextId() { return Math.max(Date.now(), ...this.rows.map(row => row.id + 1)); },
    selectQuestion(id) {
        this.selectedId = id; this.preview = false;
        this.$nextTick(() => this.$root.querySelector(`#field-label-${id}`)?.focus());
    },
    duplicateField(index) {
        if (this.rows.length >= 100) return;
        const row = { ...this.rows[index], id: this.nextId() };
        this.rows.splice(index + 1, 0, row);
        this.selectQuestion(row.id);
    },
    options(row) { return row.options_text ? row.options_text.split('\n') : ['']; },
    setOption(row, index, value) {
        const options = this.options(row); options[index] = value; row.options_text = options.join('\n');
    },
    addOption(row) {
        const options = this.options(row); options.push(''); row.options_text = options.join('\n');
        this.$nextTick(() => this.$root.querySelector(`#option-${row.id}-${options.length - 1}`)?.focus());
    },
    removeOption(row, index) {
        const options = this.options(row); options.splice(index, 1); row.options_text = options.join('\n');
    },
    moveField(index, direction) {
        const next = index + direction;
        if (next < 0 || next >= this.rows.length) return;
        [this.rows[index], this.rows[next]] = [this.rows[next], this.rows[index]];
    },
    removeField(id) {
        const row = this.rows.find(row => row.id === id);
        if (row?.title.trim() || row?.options_text.trim()) this.warning = { type: 'remove', id };
        else this.rows = this.rows.filter(row => row.id !== id);
    },
    confirmWarning() {
        if (this.warning?.type === 'discard') { this.warning = null; this.finishClose(); }
        else { this.rows = this.rows.filter(row => row.id !== this.warning.id); this.warning = null; }
    },
    close() {
        if (this.busy) return;
        if (this.warning) { this.warning = null; return; }
        if (this.dirty) { this.warning = { type: 'discard' }; return; }
        this.finishClose();
    },
    finishClose() {
        this.editor = false; this.active = null; this.warning = null;
        this.$nextTick(() => this.returnFocus?.focus());
    },
    openResponse(response) {
        this.returnFocus = document.activeElement; this.active = response;
        this.$nextTick(() => this.$refs.responseTitle.focus());
    },
    async copyLink() {
        try {
            await window.copyTextToClipboard(this.shareUrl);
            this.copied = true;
            clearTimeout(this.copyTimer);
            this.copyTimer = setTimeout(() => { this.copied = false; }, 2200);
        } catch { this.notice = 'Copy the link from the address field.'; this.$refs.shareLink.select(); }
    },
    async save() {
        if (this.busy) return;
        this.error = '';
        if (this.rows.some(row => !row.title.trim())) { this.error = 'Add a title to each question, or remove empty questions.'; this.preview = false; return; }
        if (this.rows.some(row => this.isChoice(row.type) && !row.options_text.trim())) { this.error = 'Add at least one option to each choice question.'; this.preview = false; return; }
        this.busy = true;
        try {
            const response = await fetch(this.$root.dataset.saveUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ rows: JSON.stringify(this.rows) }),
            });
            const result = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(response.status === 419 ? 'Your session expired. Keep this tab open and sign in again in another tab, then retry.' : (result.errors ? Object.values(result.errors).flat().join(' ') : result.message || 'Unable to save. Please try again.'));
            this.savedRows = result.rows;
            this.notice = 'Form saved.';
            this.finishClose();
        } catch (error) { this.error = error.message; }
        finally { this.busy = false; }
    },
    trap(event) {
        if (event.key !== 'Tab') return;
        const items = [...event.currentTarget.querySelectorAll('button, input, select, textarea, a[href], [tabindex="0"]')].filter(el => !el.disabled && el.getClientRects().length);
        const first = items[0], last = items.at(-1);
        if (!first) return;
        if (event.shiftKey && (document.activeElement === first || !items.includes(document.activeElement))) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && (document.activeElement === last || !items.includes(document.activeElement))) { event.preventDefault(); first.focus(); }
    },
});
