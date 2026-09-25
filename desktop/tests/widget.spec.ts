import { expect, test } from '@playwright/test';

test.beforeEach(async ({ page }) => {
  await page.clock.install({ time: new Date(2026, 8, 22, 12) });
  // UI-only fixtures: these never enter the application or any database.
  await page.addInitScript(() => {
    const state = window as any;
    state.calls = [];
    state.mode = 'success';
    state.updateMode = 'latest';
    state.account = { id: 1, name: 'Fixture', email: 'fixture@example.test' };
    state.saved = [];
    state.__TAURI_INTERNALS__ = { transformCallback: () => 1, unregisterCallback: () => {}, invoke: async (command: string, args: any) => {
      state.calls.push({ command, args });
      if (command === 'plugin:updater|check') {
        if (state.updateMode === 'error') throw 'connection';
        return state.updateMode === 'latest' ? null : { rid: 1, currentVersion: '0.2.0', version: '0.3.0' };
      }
      if (command === 'plugin:updater|download_and_install') {
        if (state.installFails) throw 'invalid signature';
        args.onEvent.onmessage({ event: 'Started', data: { contentLength: 100 } });
        args.onEvent.onmessage({ event: 'Progress', data: { chunkLength: 100 } });
        args.onEvent.onmessage({ event: 'Finished' });
        return;
      }
      if (command === 'account_state') return { account: state.account, saved: state.saved };
      if (command === 'switch_account') { state.account = null; return; }
      if (command === 'remove_account') { state.saved = state.saved.filter((a: any) => a.id !== args.id); return true; }
      if (command === 'resume_account') {
        if (state.resumeExpired) throw 'unauthenticated';
        state.account = state.saved.find((a: any) => a.id === args.id); state.mode = 'success'; return state.account;
      }
      if (command === 'workspace') return { workspaces: [{ id: 1, name: '<b>Owned</b>', nodes: [
        { id: 10, parent_id: null, name: 'Projects', type: 'folder' },
        { id: 11, parent_id: 10, name: 'Brief', type: 'note' },
        { id: 12, parent_id: 10, name: 'Docs', type: 'shortcut', url: 'https://example.com/docs' },
      ] }], sticky_note: 'Remember this' };
      if (command === 'note') {
        if (state.delayNote) return new Promise(resolve => { state.resolveNote = resolve; });
        return { name: 'Brief', content: '<script>private note</script>' };
      }
      if (command === 'login') { state.mode = 'success'; state.account = { id: 1, name: 'Fixture', email: args.email }; return state.account; }
      if (command !== 'calendar') return;
      if (state.mode === 'auth') throw 'unauthenticated';
      if (state.mode === 'error') throw 'connection';
      return { events: [{ id: 1, title: '<b>Test entry</b>', date: `${args.month}-22` }] };
    } };
  });
  await page.goto('/');
});

test('starts collapsed, opens, marks dates, displays plain-text events and refetches', async ({ page }) => {
  await expect(page.locator('#calendar-panel')).toBeHidden();
  expect(await page.evaluate(() => (window as any).calls)).toEqual([]);
  await page.getByRole('button', { name: 'Open calendar' }).click();
  await expect(page.getByRole('heading', { name: 'September 2026' })).toBeVisible();
  await expect(page.locator('.today')).toHaveText('22');
  await expect(page.locator('.has-events')).toHaveText('22');
  await expect(page.locator('.events li')).toHaveText('<b>Test entry</b>');
  await expect(page.locator('.events b')).toHaveCount(0);
  await page.getByRole('button', { name: 'Next month' }).click();
  await expect(page.getByRole('heading', { name: 'October 2026' })).toBeVisible();
  await page.locator('.has-events').click();
  await expect(page.locator('.events li')).toHaveText('<b>Test entry</b>');
  await page.getByRole('button', { name: 'Previous month' }).click();
  await page.getByRole('button', { name: 'Collapse calendar' }).click();
  await expect(page.locator('#calendar-panel')).toBeHidden();
  await page.getByRole('button', { name: 'Open calendar' }).click();
  expect(await page.evaluate(() => (window as any).calls.filter((c: any) => c.command === 'calendar').length)).toBe(4);
});

test('server failure remains contained and calendar can collapse', async ({ page }) => {
  await page.evaluate(() => { (window as any).mode = 'error'; });
  await page.getByRole('button', { name: 'Open calendar' }).click();
  await expect(page.locator('.calendar-content [role="status"]')).toHaveText('Unable to connect to server');
  await page.keyboard.press('Escape');
  await expect(page.locator('#calendar-panel')).toBeHidden();
});

test('only checks on expansion, never polls or installs automatically', async ({ page }) => {
  await page.evaluate(() => { (window as any).updateMode = 'available'; });
  await page.getByRole('button', { name: 'Open calendar' }).click();
  await expect(page.getByRole('button', { name: 'Update now' })).toBeVisible();
  await page.clock.fastForward('10:00');
  expect(await page.evaluate(() => (window as any).calls.filter((c: any) => c.command === 'calendar').length)).toBe(1);
  expect(await page.evaluate(() => (window as any).calls.filter((c: any) => c.command === 'plugin:updater|check').length)).toBe(1);
  expect(await page.evaluate(() => (window as any).calls.some((c: any) => c.command === 'plugin:updater|download_and_install'))).toBe(false);
  await page.getByRole('button', { name: 'Collapse calendar' }).click();
  await page.clock.fastForward('10:00');
  expect(await page.evaluate(() => (window as any).calls.filter((c: any) => c.command === 'calendar').length)).toBe(1);
  await page.getByRole('button', { name: 'Open calendar' }).click();
  await expect(page.getByRole('button', { name: 'Update now' })).toBeVisible();
  expect(await page.evaluate(() => (window as any).calls.filter((c: any) => c.command === 'calendar').length)).toBe(2);
  expect(await page.evaluate(() => (window as any).calls.filter((c: any) => c.command === 'plugin:updater|check').length)).toBe(2);
});

test('one click installs an available update and requests restart', async ({ page }) => {
  await page.evaluate(() => { (window as any).updateMode = 'available'; });
  await page.getByRole('button', { name: 'Open calendar' }).click();
  await page.getByRole('button', { name: 'Update now' }).click();
  await expect(page.locator('.update-status')).toHaveText('Installing update and restarting…');
  expect(await page.evaluate(() => (window as any).calls.filter((c: any) => c.command === 'plugin:updater|download_and_install').length)).toBe(1);
  expect(await page.evaluate(() => (window as any).calls.some((c: any) => c.command === 'plugin:process|restart'))).toBe(true);
});

test('failed check can retry without interrupting calendar', async ({ page }) => {
  await page.evaluate(() => { (window as any).updateMode = 'error'; });
  await page.getByRole('button', { name: 'Open calendar' }).click();
  await expect(page.locator('.update-status')).toHaveText('Could not check for app updates');
  await expect(page.locator('.events li')).toHaveText('<b>Test entry</b>');
  await page.evaluate(() => { (window as any).updateMode = 'latest'; });
  await page.getByRole('button', { name: 'Retry', exact: true }).click();
  await expect(page.locator('.update-status')).toHaveText('App is up to date');
});

test('failed download or verification does not restart and allows retry', async ({ page }) => {
  await page.evaluate(() => { (window as any).updateMode = 'available'; (window as any).installFails = true; });
  await page.getByRole('button', { name: 'Open calendar' }).click();
  await page.getByRole('button', { name: 'Update now' }).click();
  await expect(page.locator('.update-status')).toHaveText('Unable to finish update. Please try again.');
  await expect(page.getByRole('button', { name: 'Retry update' })).toBeEnabled();
  expect(await page.evaluate(() => (window as any).calls.some((c: any) => c.command === 'plugin:process|restart'))).toBe(false);
});

test('expired session requires login before retrieving calendar', async ({ page }) => {
  await page.evaluate(() => { (window as any).mode = 'auth'; });
  await page.getByRole('button', { name: 'Open calendar' }).click();
  await page.getByLabel('Email').fill('fixture@example.test');
  await page.getByLabel('Password').fill('test-password');
  await page.getByRole('button', { name: 'Sign in', exact: true }).click();
  await expect(page.getByRole('heading', { name: 'September 2026' })).toBeVisible();
});

test('saved account picker resumes on click and removes only chosen account', async ({ page }) => {
  await page.evaluate(() => { Object.assign(window as any, { account: null, saved: [
    { id: 1, name: 'One', email: 'one@example.test' }, { id: 2, name: 'Two', email: 'two@example.test' },
  ] }); });
  await page.getByRole('button', { name: 'Open calendar' }).click();
  await expect(page.getByRole('heading', { name: 'Choose an account' })).toBeVisible();
  await page.screenshot({ path: 'test-results/account-picker.png' });
  expect(await page.evaluate(() => (window as any).calls.some((c: any) => c.command === 'resume_account'))).toBe(false);
  await page.getByRole('button', { name: 'Continue as One', exact: false }).click();
  await expect(page.getByRole('heading', { name: 'September 2026' })).toBeVisible();
  await page.getByRole('button', { name: 'Switch account' }).click();
  await expect(page.locator('.events')).toHaveCount(0);
  await page.getByRole('button', { name: 'Remove one@example.test' }).click();
  await expect(page.getByRole('button', { name: 'Continue as One', exact: false })).toHaveCount(0);
  await expect(page.getByRole('button', { name: 'Continue as Two', exact: false })).toBeVisible();
});

test('expired saved login prefills email and supports saving opt-out', async ({ page }) => {
  await page.evaluate(() => { Object.assign(window as any, { account: null, resumeExpired: true, saved: [{ id: 1, name: 'One', email: 'one@example.test' }] }); });
  await page.getByRole('button', { name: 'Open calendar' }).click();
  await page.getByRole('button', { name: 'Continue as One', exact: false }).click();
  await expect(page.getByLabel('Email')).toHaveValue('one@example.test');
  await expect(page.getByLabel('Password')).toHaveValue('');
  await page.getByLabel('Save account on this device').uncheck();
  await page.getByLabel('Password').fill('fixture');
  await page.getByRole('button', { name: 'Sign in', exact: true }).click();
  await expect(page.getByRole('button', { name: 'Switch account' })).toBeVisible();
  expect(await page.evaluate(() => (window as any).calls.find((c: any) => c.command === 'login').args.saveAccount)).toBe(false);
});

test('workspace tree loads notes safely and opens shortcuts only on click', async ({ page }) => {
  await page.getByRole('button', { name: 'Open calendar' }).click();
  await page.getByRole('button', { name: 'Workspace', exact: true }).click();
  await expect(page.getByText('<b>Owned</b>', { exact: true })).toBeVisible();
  await page.getByText('Projects', { exact: true }).click();
  expect(await page.evaluate(() => (window as any).calls.some((c: any) => c.command === 'note' || c.command === 'open_shortcut'))).toBe(false);
  await page.getByText('Note · Brief', { exact: true }).click();
  await expect(page.getByText('<script>private note</script>', { exact: true })).toBeVisible();
  await page.screenshot({ path: 'test-results/workspace.png' });
  await expect(page.locator('.section-content script')).toHaveCount(0);
  await page.getByRole('button', { name: '↗ Docs' }).click();
  expect(await page.evaluate(() => (window as any).calls.find((c: any) => c.command === 'open_shortcut').args.url)).toBe('https://example.com/docs');
  await page.getByRole('button', { name: 'Collapse calendar' }).click();
  await page.getByRole('button', { name: 'Open calendar' }).click();
  await expect(page.getByText('Projects', { exact: true })).toBeVisible();
  expect(await page.evaluate(() => (window as any).calls.filter((c: any) => c.command === 'workspace').length)).toBe(2);
});

test('late private note response cannot appear after switching accounts', async ({ page }) => {
  await page.evaluate(() => { (window as any).delayNote = true; });
  await page.getByRole('button', { name: 'Open calendar' }).click();
  await page.getByRole('button', { name: 'Workspace', exact: true }).click();
  await page.getByText('Projects', { exact: true }).click();
  await page.getByText('Note · Brief', { exact: true }).click();
  await expect(page.getByText('Loading note…')).toBeVisible();
  await page.getByRole('button', { name: 'Switch account' }).click();
  await page.evaluate(() => (window as any).resolveNote({ name: 'Brief', content: 'Old private note' }));
  await expect(page.getByText('Old private note')).toHaveCount(0);
  await expect(page.getByLabel('Email')).toBeVisible();
});
