import test from 'node:test';
import assert from 'node:assert/strict';
import calendarDashboard from '../../resources/js/calendar-dashboard.js';

test('day-list scope sets the root dialog selection without overwriting its entry', () => {
    const item = { id: 7, title: 'Review', original_date: '2026-09-15', details: 'Notes', follow_up_enabled: true, follow_up_days: 3 };
    const state = calendarDashboard({ initialDate: '2026-09-15', days: [] });
    state.$nextTick = () => {};
    globalThis.document = { activeElement: {} };
    const loop = { entry: item };
    const scope = new Proxy(state, {
        get(target, key) { return key in loop ? loop[key] : target[key]; },
        set(target, key, value) { if (key in loop) loop[key] = value; else target[key] = value; return true; }
    });
    scope.open('detail', loop.entry);
    assert.equal(state.selectedEntry.title, 'Review');
    assert.equal(state.modal, 'detail');
    assert.equal(loop.entry, item);
    state.open('edit', state.selectedEntry);
    assert.equal(state.form.entry_date, '2026-09-15');
    state.form.entry_date = '2026-09-20';
    assert.equal(item.original_date, '2026-09-15');
    state.close();
    scope.open('detail', loop.entry);
    assert.equal(state.selectedEntry.id, 7);
    state.close();
    state.open('detail', null);
    assert.equal(state.modal, null);
});

test('holiday filters distinguish national dates, state dates and observances without altering entries', () => {
    const entries = [{id:1,title:'My work'}];
    const state = calendarDashboard({initialDate:'2026-09-16',days:[{date:'2026-09-16',entries}]});
    state.holidayItems = [
        {date:'2026-09-16', name:'National', kind:'holiday', nationwide:true, states:['SGR','KUL']},
        {date:'2026-09-16', name:'State only', kind:'holiday', nationwide:false, states:['SGR']},
        {date:'2026-09-16', name:'Awareness day', kind:'observance', nationwide:true, states:[]}
    ];
    assert.deepEqual(state.holidaysFor('2026-09-16').map(h=>h.name), ['National','Awareness day']);
    state.holidayRegion = 'SGR';
    assert.equal(state.holidaysFor('2026-09-16').length,3);
    state.holidayRegion = 'KUL';
    assert.equal(state.holidaysFor('2026-09-16').length,2);
    assert.equal(state.day.entries.length,1);
    assert.equal(state.day.entries[0].title,'My work');
});
