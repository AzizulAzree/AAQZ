import test from 'node:test';
import assert from 'node:assert/strict';
import projectBrowser from '../../resources/js/project-browser.js';

function editor() {
    globalThis.document = { activeElement: { closest: () => null, focus() {} }, getElementById: () => null, querySelector: () => ({ content: 'test-token' }) };
    const state = projectBrowser([{ id: 1, folders: [] }], []);
    state.$nextTick = callback => callback();
    state.$root = { dataset: { storeNodeUrl: '/project/nodes' } };
    state.folderId = 2;
    return state;
}

test('saved notes open for reading and editing is an explicit action', () => {
    const state = editor();
    state.openNote({ name: 'Title', content: 'Saved text' });
    assert.equal(state.noteEditing, false);
    assert.equal(state.noteDirty, false);
    state.editNote();
    assert.equal(state.noteEditing, true);
    assert.equal(state.noteContent, 'Saved text');
    state.openNote();
    assert.equal(state.noteEditing, true);
    state.openManage('edit', { name: 'Title', content: 'Saved text' }, 'note');
    assert.equal(state.noteEditing, true);
});

test('closing a changed note keeps its draft until discard is confirmed', () => {
    const state = editor();
    state.openNote({ name: 'Original', content: 'Original body' });
    assert.equal(state.noteDirty, false);
    state.noteContent = 'Unsaved text';
    state.closeModals();
    assert.equal(state.noteOpen, true);
    assert.equal(state.discardNote, true);
    state.closeModals();
    assert.equal(state.discardNote, false);
    assert.equal(state.noteContent, 'Unsaved text');
    state.discardNoteChanges();
    assert.equal(state.noteOpen, false);
    assert.equal(state.noteDirty, false);
});

test('creating a note submits its folder and plain text to the create route', async () => {
    const state = editor();
    state.openNote(); state.noteName = 'Title'; state.noteContent = '<b>Plain text</b>\nNext line';
    let submitted;
    globalThis.fetch = async (url, options) => { submitted = { url, ...options }; return { ok: true, json: async () => ({ redirect: '/project?folder=2' }) }; };
    let destination;
    globalThis.location = { assign: url => { destination = url; } };
    await state.saveNote();
    assert.equal(submitted.url, '/project/nodes');
    assert.equal(submitted.method, 'POST');
    assert.deepEqual(JSON.parse(submitted.body), { type: 'note', workspace_id: 1, parent_id: 2, name: 'Title', content: '<b>Plain text</b>\nNext line' });
    assert.equal(destination, '/project?folder=2');
    assert.equal(state.noteDirty, false);
});

test('failed edits retain the draft and show the server validation error', async () => {
    const state = editor();
    state.openNote({ name: 'Title', content: 'Saved', manage_url: '/project/nodes/3' });
    state.noteContent = 'Changed';
    globalThis.fetch = async (url, options) => {
        assert.equal(url, '/project/nodes/3'); assert.equal(options.method, 'PATCH');
        return { ok: false, json: async () => ({ errors: { content: ['Unable to save note.'] } }) };
    };
    await state.saveNote();
    assert.equal(state.noteError, 'Unable to save note.');
    assert.equal(state.noteContent, 'Changed');
    assert.equal(state.noteDirty, true);
    assert.equal(state.busy, false);
});
