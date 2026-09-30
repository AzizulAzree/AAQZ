import { invoke } from '@tauri-apps/api/core';
import { Companion } from './Companion';
import { StartupSetting } from './StartupSetting';
import { AppUpdater } from './AppUpdater';
import { mountAppearance } from './AppearanceSetting';

type Section = 'calendar' | 'workspace' | 'notes';
export function mountIsland(root: HTMLElement) {
  root.innerHTML = `<div class="island"><div class="launchers" role="group" aria-label="Widget views"><button class="toggle" data-section="calendar" title="Calendar" aria-label="Open calendar" aria-expanded="false" aria-controls="calendar-panel"><svg aria-hidden="true" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="16" rx="3"/><path d="M7 3v4M17 3v4M3 11h18M8 15h2M14 15h2"/></svg></button><button class="toggle" data-section="workspace" title="Workspace" aria-label="Open workspace" aria-expanded="false" aria-controls="calendar-panel"><svg aria-hidden="true" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7a2 2 0 0 1 2-2h5l2 3h7a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/></svg></button><button class="toggle" data-section="notes" title="My notes" aria-label="Open My notes" aria-expanded="false" aria-controls="calendar-panel"><svg aria-hidden="true" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9Z"/><path d="M14 3v6h6M8 13h8M8 17h6"/></svg></button></div><div id="calendar-panel" hidden></div></div>`;
  const toggles = root.querySelectorAll<HTMLButtonElement>('.toggle');
  const island = root.querySelector<HTMLElement>('.island')!;
  const panel = root.querySelector<HTMLElement>('#calendar-panel')!;
  panel.innerHTML = `<div class="calendar-content"></div><section id="widget-settings" class="startup-setting" aria-label="Widget settings" hidden><h3>Settings</h3><div class="appearance-container"></div><div class="startup-container"></div></section><footer class="widget-footer"><div class="app-updater" aria-label="App updates"></div><button type="button" class="settings-toggle" title="Settings" aria-label="Open settings" aria-expanded="false" aria-controls="widget-settings"><svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9.5 3-.5 2a7.5 7.5 0 0 0-2 1.2L5 5.7 2.5 10l1.5 1.5a7.5 7.5 0 0 0 0 2L2.5 15 5 19.3l2-.5A7.5 7.5 0 0 0 9 20l.5 2h5l.5-2a7.5 7.5 0 0 0 2-1.2l2 .5 2.5-4.3-1.5-1.5a7.5 7.5 0 0 0 0-2L21.5 10 19 5.7l-2 .5A7.5 7.5 0 0 0 15 5l-.5-2Z"/><circle cx="12" cy="12.5" r="3"/></svg></button></footer>`;
  const companion = new Companion(panel.querySelector<HTMLElement>('.calendar-content')!);
  const updater = new AppUpdater(panel.querySelector<HTMLElement>('.app-updater')!);
  const startup = new StartupSetting(panel.querySelector<HTMLElement>('.startup-container')!);
  mountAppearance(panel.querySelector<HTMLElement>('.appearance-container')!);
  const settings = panel.querySelector<HTMLElement>('#widget-settings')!;
  const settingsToggle = panel.querySelector<HTMLButtonElement>('.settings-toggle')!;
  const closeSettings = () => {
    settings.hidden = true;
    settingsToggle.setAttribute('aria-expanded', 'false');
    settingsToggle.setAttribute('aria-label', 'Open settings');
  };
  settingsToggle.addEventListener('click', () => {
    if (!settings.hidden) { closeSettings(); return; }
    settings.hidden = false;
    settingsToggle.setAttribute('aria-expanded', 'true');
    settingsToggle.setAttribute('aria-label', 'Close settings');
    void startup.refresh();
  });
  let selected: Section | null = null;
  let changing = false;
  let dismissPending = false;
  let hovering = false;
  let keyboardFocus = false;
  let tuckTimer: ReturnType<typeof setTimeout> | undefined;
  let layout = Promise.resolve();
  // Serialize native resizing so rapid pointer movement cannot apply an old size last.
  const resize = (expanded: boolean, revealed: boolean) => {
    const next = layout.then(() => invoke<void>('resize_widget', { expanded, revealed }));
    layout = next.catch(() => {});
    return next;
  };
  const reveal = async () => {
    clearTimeout(tuckTimer);
    if (selected || changing) return;
    try {
      await resize(false, true);
      if (!selected && (hovering || keyboardFocus)) root.classList.add('revealed');
    } catch { toggles.forEach(toggle => toggle.title = 'Unable to reveal widget. Please restart the app.'); }
  };
  const tuck = () => {
    clearTimeout(tuckTimer);
    if (selected || hovering || keyboardFocus || changing) return;
    root.classList.remove('revealed');
    tuckTimer = setTimeout(() => {
      if (!selected && !hovering && !keyboardFocus && !changing) void resize(false, false).catch(() => {});
    }, 220);
  };
  root.addEventListener('pointerenter', () => { hovering = true; void reveal(); });
  root.addEventListener('pointerleave', () => { hovering = false; tuck(); });
  document.addEventListener('keydown', event => {
    if (event.key === 'Tab') { keyboardFocus = true; void reveal(); }
  });
  root.addEventListener('pointerdown', () => { keyboardFocus = false; });
  root.addEventListener('focusout', () => {
    queueMicrotask(() => { if (!root.contains(document.activeElement)) { keyboardFocus = false; tuck(); } });
  });
  const select = async (section: Section) => {
    if (changing) return;
    changing = true;
    clearTimeout(tuckTimer);
    const next = selected === section ? null : section;
    try {
      if ((selected === null) !== (next === null)) await resize(next !== null, hovering || keyboardFocus);
      const wasClosed = selected === null;
      selected = next;
      root.classList.toggle('expanded', selected !== null);
      root.classList.toggle('revealed', selected === null && (hovering || keyboardFocus));
      panel.hidden = selected === null;
      closeSettings();
      toggles.forEach(toggle => {
        const active = toggle.dataset.section === selected;
        toggle.setAttribute('aria-expanded', String(active));
        toggle.setAttribute('aria-label', `${active ? 'Collapse' : 'Open'} ${toggle.dataset.section === 'notes' ? 'My notes' : toggle.dataset.section}`);
      });
      companion.close();
      if (selected) { companion.open(selected); if (wasClosed) void updater.check(); }
    } catch {
      toggles.forEach(toggle => toggle.title = 'Unable to resize widget. Please restart the app.');
    } finally {
      changing = false;
      if (dismissPending) { dismissPending = false; dismiss(); }
      else if (!selected && !hovering && !keyboardFocus) tuck();
    }
  };
  const dismiss = () => {
    hovering = false;
    keyboardFocus = false;
    if (changing) { dismissPending = true; return; }
    if (selected) void select(selected);
    else tuck();
  };
  // Other apps/the desktop cause focus loss; transparent window margins are
  // handled separately because clicking them can leave this window focused.
  window.addEventListener('blur', dismiss);
  document.addEventListener('pointerdown', event => {
    if (event.target instanceof Node && !island.contains(event.target)) dismiss();
  });
  toggles.forEach(toggle => toggle.addEventListener('click', () => { void select(toggle.dataset.section as Section); }));
  document.addEventListener('keydown', event => {
    if (event.key !== 'Escape') return;
    if (!settings.hidden) { closeSettings(); settingsToggle.focus(); }
    else if (selected) void select(selected);
  });
}
