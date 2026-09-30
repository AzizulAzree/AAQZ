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
      if (command === 'autostart_status') { if (state.startupReadFails) throw 'read'; return state.startupEnabled ?? false; }
      if (command === 'set_autostart') { if (state.startupFails) throw 'write'; state.startupEnabled = args.enabled; return state.startupEnabled; }
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
  await page.getByRole('button', { name: 'Open workspace', exact: true }).click();
  await expect(page.getByText('<b>Owned</b>', { exact: true })).toBeVisible();
  await page.getByText('Projects', { exact: true }).click();
  expect(await page.evaluate(() => (window as any).calls.some((c: any) => c.command === 'note' || c.command === 'open_shortcut'))).toBe(false);
  await page.getByText('Note · Brief', { exact: true }).click();
  await expect(page.getByText('<script>private note</script>', { exact: true })).toBeVisible();
  await page.screenshot({ path: 'test-results/workspace.png' });
  await expect(page.locator('.section-content script')).toHaveCount(0);
  await page.getByRole('button', { name: '↗ Docs' }).click();
  expect(await page.evaluate(() => (window as any).calls.find((c: any) => c.command === 'open_shortcut').args.url)).toBe('https://example.com/docs');
  await page.getByRole('button', { name: 'Collapse workspace' }).click();
  await page.getByRole('button', { name: 'Open workspace' }).click();
  await expect(page.getByText('Projects', { exact: true })).toBeVisible();
  expect(await page.evaluate(() => (window as any).calls.filter((c: any) => c.command === 'workspace').length)).toBe(2);
});

test('late private note response cannot appear after switching accounts', async ({ page }) => {
  await page.evaluate(() => { (window as any).delayNote = true; });
  await page.getByRole('button', { name: 'Open calendar' }).click();
  await page.getByRole('button', { name: 'Open workspace', exact: true }).click();
  await page.getByText('Projects', { exact: true }).click();
  await page.getByText('Note · Brief', { exact: true }).click();
  await expect(page.getByText('Loading note…')).toBeVisible();
  await page.getByRole('button', { name: 'Switch account' }).click();
  await page.evaluate(() => (window as any).resolveNote({ name: 'Brief', content: 'Old private note' }));
  await expect(page.getByText('Old private note')).toHaveCount(0);
  await expect(page.getByLabel('Email')).toBeVisible();
});

test('separate icons open only the selected view and toggle closed', async ({ page }) => {
  await page.getByRole('button', { name: 'Open workspace', exact: true }).click();
  await expect(page.getByText('Projects', { exact: true })).toBeVisible();
  await expect(page.locator('.days')).toHaveCount(0);
  expect(await page.evaluate(() => (window as any).calls.filter((c: any) => c.command === 'calendar').length)).toBe(0);
  await page.getByRole('button', { name: 'Open calendar', exact: true }).click();
  await expect(page.locator('.days')).toBeVisible();
  await expect(page.getByText('Projects', { exact: true })).toHaveCount(0);
  await expect(page.locator('.widget-tabs')).toHaveCount(0);
  await page.getByRole('button', { name: 'Collapse calendar', exact: true }).click();
  await expect(page.locator('#calendar-panel')).toBeHidden();
  await page.screenshot({ path: 'test-results/separate-icons.png' });
});

test('workspace selection survives saved-account sign-in', async ({ page }) => {
  await page.evaluate(() => { Object.assign(window as any, { account: null, saved: [{ id: 1, name: 'One', email: 'one@example.test' }] }); });
  await page.getByRole('button', { name: 'Open workspace', exact: true }).click();
  await page.getByRole('button', { name: 'Continue as One', exact: false }).click();
  await expect(page.getByText('Projects', { exact: true })).toBeVisible();
  await expect(page.locator('.days')).toHaveCount(0);
  await page.keyboard.press('Escape');
  await expect(page.locator('#calendar-panel')).toBeHidden();
});

test('hover reveals half-hidden icons without loading data and tucks on leave', async ({ page }) => {
  await page.setViewportSize({ width: 128, height: 80 });
  await page.emulateMedia({ reducedMotion: 'reduce' });
  await expect(page.locator('.island')).toHaveCSS('transform', 'matrix(1, 0, 0, 1, 0, -26)');
  await page.screenshot({ path: 'test-results/tucked-widget.png' });
  await page.mouse.move(64, 12);
  await expect(page.locator('#app')).toHaveClass('revealed');
  await expect(page.locator('.island')).toHaveCSS('transform', 'matrix(1, 0, 0, 1, 0, 8)');
  await page.screenshot({ path: 'test-results/revealed-widget.png' });
  expect(await page.evaluate(() => (window as any).calls.every((c: any) => c.command === 'resize_widget'))).toBe(true);
  await page.mouse.move(127, 79);
  await page.clock.fastForward(250);
  await expect(page.locator('#app')).not.toHaveClass(/revealed/);
  expect(await page.evaluate(() => (window as any).calls.at(-1).args)).toEqual({ expanded: false, revealed: false });
});

test('open view stays visible when pointer leaves and keyboard can reveal icons', async ({ page }) => {
  await page.emulateMedia({ reducedMotion: 'reduce' });
  await page.keyboard.press('Tab');
  await expect(page.locator('#app')).toHaveClass('revealed');
  await page.keyboard.press('Enter');
  await expect(page.locator('#calendar-panel')).toBeVisible();
  await page.mouse.move(335, 515);
  await page.clock.fastForward(500);
  await expect(page.locator('#app')).toHaveClass('expanded');
  await expect(page.locator('.island')).toHaveCSS('transform', 'none');
});


test('startup is opt-in and remembers the Windows setting across panels', async ({ page }) => {
  await page.getByRole('button', { name: 'Open calendar' }).click();
  await page.getByRole('button', { name: 'Open settings' }).click();
  const toggle = page.getByRole('checkbox', { name: 'Start with Windows' });
  await expect(toggle).not.toBeChecked();
  expect(await page.evaluate(() => (window as any).calls.some((c: any) => c.command === 'set_autostart'))).toBe(false);
  await toggle.check();
  await page.getByRole('button', { name: 'Collapse calendar' }).click();
  await page.getByRole('button', { name: 'Open workspace' }).click();
  await page.getByRole('button', { name: 'Open settings' }).click();
  await expect(toggle).toBeChecked();
  await toggle.uncheck();
  expect(await page.evaluate(() => (window as any).startupEnabled)).toBe(false);
});

test('startup failures restore the checkbox and allow retry', async ({ page }) => {
  await page.evaluate(() => { (window as any).startupEnabled = true; (window as any).startupFails = true; });
  await page.getByRole('button', { name: 'Open calendar' }).click();
  await page.getByRole('button', { name: 'Open settings' }).click();
  const toggle = page.getByRole('checkbox', { name: 'Start with Windows' });
  await expect(toggle).toBeChecked();
  await toggle.click();
  await expect(toggle).toBeChecked();
  await expect(page.getByText('Could not save. Please try again.')).toBeVisible();
  await page.evaluate(() => { (window as any).startupFails = false; });
  await toggle.uncheck();
  await expect(toggle).not.toBeChecked();
});

test('startup read failure prevents overwriting an unknown setting', async ({ page }) => {
  await page.evaluate(() => { (window as any).startupReadFails = true; });
  await page.getByRole('button', { name: 'Open calendar' }).click();
  await page.getByRole('button', { name: 'Open settings' }).click();
  await expect(page.getByRole('checkbox', { name: 'Start with Windows' })).toBeDisabled();
  await page.evaluate(() => { (window as any).startupReadFails = false; (window as any).startupEnabled = true; });
  await page.getByRole('button', { name: 'Collapse calendar' }).click();
  await page.getByRole('button', { name: 'Open calendar' }).click();
  await page.getByRole('button', { name: 'Open settings' }).click();
  await expect(page.getByRole('checkbox', { name: 'Start with Windows' })).toBeChecked();
});


test('gear opens settings beside updater and Escape closes settings first', async ({ page }) => {
  await page.evaluate(() => { (window as any).updateMode = 'available'; });
  await page.getByRole('button', { name: 'Open calendar' }).click();
  await expect(page.getByRole('checkbox', { name: 'Start with Windows' })).toBeHidden();
  expect(await page.evaluate(() => (window as any).calls.some((c: any) => c.command === 'autostart_status'))).toBe(false);
  await expect(page.getByRole('button', { name: 'Update now' })).toBeVisible();
  const gear = page.getByRole('button', { name: 'Open settings' });
  const updateBox = await page.getByRole('button', { name: 'Update now' }).boundingBox();
  const gearBox = await gear.boundingBox();
  expect(gearBox!.x).toBeGreaterThan(updateBox!.x + updateBox!.width);
  await page.screenshot({ path: 'test-results/settings-gear.png' });
  await gear.click();
  await expect(page.getByRole('checkbox', { name: 'Start with Windows' })).toBeVisible();
  await expect(page.getByRole('button', { name: 'Update now' })).toBeVisible();
  await page.screenshot({ path: 'test-results/settings-open.png' });
  await page.keyboard.press('Escape');
  await expect(page.getByRole('checkbox', { name: 'Start with Windows' })).toBeHidden();
  await expect(page.getByRole('button', { name: 'Collapse calendar' })).toBeVisible();
  await expect(page.getByRole('button', { name: 'Open settings' })).toBeFocused();
  await page.getByRole('button', { name: 'Open settings' }).click();
  await page.getByRole('button', { name: 'Open workspace' }).click();
  await expect(page.getByRole('checkbox', { name: 'Start with Windows' })).toBeHidden();
  await page.getByRole('button', { name: 'Open settings' }).click();
  await page.getByRole('button', { name: 'Close settings' }).click();
  await expect(page.getByRole('checkbox', { name: 'Start with Windows' })).toBeHidden();
});
