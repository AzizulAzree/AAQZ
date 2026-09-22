import test from 'node:test';
import assert from 'node:assert/strict';
import formsPage from '../../resources/js/forms-page.js';

function editor() {
    globalThis.document = { activeElement: { focus() {} }, querySelector: () => ({ content: 'test-token' }) };
    const state = formsPage([{ id: 1, title: 'Name', type: 'text', options_text: '' }], [], '/ordering/example');
    state.$refs = { editorTitle: { focus() {} } };
    state.$root = { dataset: { saveUrl: '/form' }, querySelector: () => null };
    state.$nextTick = fn => fn();
    state.openEditor();
    return state;
}

test('cancel warns and discards without changing the saved form', () => {
    const state = editor();
    state.rows[0].title = 'Changed';
    state.close();
    assert.equal(state.warning.type, 'discard');
    assert.equal(state.editor, true);
    state.confirmWarning();
    state.openEditor();
    assert.equal(state.rows[0].title, 'Name');
});

test('fields reorder by stable id and removal requires confirmation', () => {
    const state = editor();
    state.addField();
    const added = state.rows[1].id;
    state.moveField(1, -1);
    assert.equal(state.rows[0].id, added);
    state.removeField(1);
    assert.equal(state.rows.length, 2);
    state.confirmWarning();
    assert.deepEqual(state.rows.map(row => row.id), [added]);
});

test('failed save preserves the draft and successful save updates it', async () => {
    const state = editor();
    state.rows[0].title = 'Changed';
    globalThis.fetch = async () => ({ ok: false, status: 503, json: async () => ({ message: 'Try again' }) });
    await state.save();
    assert.equal(state.editor, true);
    assert.equal(state.rows[0].title, 'Changed');
    assert.equal(state.savedRows[0].title, 'Name');
    assert.equal(state.busy, false);
    globalThis.fetch = async () => ({ ok: true, json: async () => ({ rows: state.rows }) });
    await state.save();
    assert.equal(state.editor, false);
    assert.equal(state.savedRows[0].title, 'Changed');
});

test('duplicate and inline options keep independent question data', () => {
    const state = editor();
    state.rows[0].type = 'radio button';
    state.rows[0].required = true;
    state.setOption(state.rows[0], 0, 'First');
    state.addOption(state.rows[0]);
    state.setOption(state.rows[0], 1, 'Second');
    state.duplicateField(0);
    assert.equal(state.rows.length, 2);
    assert.notEqual(state.rows[0].id, state.rows[1].id);
    assert.equal(state.rows[1].required, true);
    state.removeOption(state.rows[1], 0);
    assert.equal(state.rows[0].options_text, 'First\nSecond');
    assert.equal(state.rows[1].options_text, 'Second');
    state.selectedId = state.rows[0].id;
    state.addField();
    assert.equal(state.rows[1].title, '');
    assert.equal(state.rows[2].options_text, 'Second');
});
