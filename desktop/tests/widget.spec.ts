import { expect, test } from '@playwright/test';

test.beforeEach(async ({ page }) => {
  await page.clock.install({ time: new Date(2026, 8, 22, 12) });
  // UI-only fixtures: these never enter the application or any database.
  await page.addInitScript(() => {
    const state = window as any;
    state.calls = [];
    state.mode = 'success';
    state.__TAURI_INTERNALS__ = { invoke: async (command: string, args: any) => {
      state.calls.push({ command, args });
      if (command === 'login') { state.mode = 'success'; return; }
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
  await expect(page.getByRole('status')).toHaveText('Unable to connect to server');
  await page.keyboard.press('Escape');
  await expect(page.locator('#calendar-panel')).toBeHidden();
});

test('expired session requires login before retrieving calendar', async ({ page }) => {
  await page.evaluate(() => { (window as any).mode = 'auth'; });
  await page.getByRole('button', { name: 'Open calendar' }).click();
  await page.getByLabel('Email').fill('fixture@example.test');
  await page.getByLabel('Password').fill('test-password');
  await page.getByRole('button', { name: 'Sign in', exact: true }).click();
  await expect(page.getByRole('heading', { name: 'September 2026' })).toBeVisible();
});
