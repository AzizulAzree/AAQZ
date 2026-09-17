import test from 'node:test';
import assert from 'node:assert/strict';
import widget from '../../resources/js/workspace-widget.js';

test('mixed items paginate and folder/workspace navigation resets the page', () => {
    const state = widget([{ id: 1, url: '/project?workspace=1', nodes: [{ id: 1, parent_id: null, type: 'folder' }, ...Array.from({ length: 8 }, (_, i) => ({ id: i + 2, parent_id: 1, type: i % 2 ? 'note' : 'shortcut' }))] }, { id: 2, nodes: [] }]);
    assert.equal(state.items.length, 1);
    state.openFolder(state.items[0]);
    assert.equal(state.visibleItems.length, 6);
    state.page = 1;
    assert.equal(state.visibleItems.length, 2);
    assert.equal(state.workspaceUrl, '/project?workspace=1&folder=1');
    state.back(); assert.equal(state.page, 0); assert.equal(state.folderId, null);
    state.workspaceId = '2'; state.changeWorkspace(); assert.equal(state.items.length, 0);
});

test('a late note response cannot reopen a closed reader', async () => {
    const state = widget([]); state.$nextTick = fn => fn();
    globalThis.document = { activeElement: { focus() {} }, getElementById: () => null };
    let resolve;
    globalThis.fetch = () => new Promise(done => { resolve = done; });
    const pending = state.openNote({ name: 'Note', url: '/note' });
    state.closeNote();
    resolve({ ok: true, json: async () => ({ content: 'Body' }) });
    await pending;
    assert.equal(state.note, null); assert.equal(state.loading, false);
});
