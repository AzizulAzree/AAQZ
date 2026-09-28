import { invoke } from '@tauri-apps/api/core';
import { Companion } from './Companion';
import { AppUpdater } from './AppUpdater';

type Section = 'calendar' | 'workspace';
export function mountIsland(root: HTMLElement) {
  root.innerHTML = `<div class="island"><div class="launchers" role="group" aria-label="Widget views"><button class="toggle" data-section="calendar" title="Calendar" aria-label="Open calendar" aria-expanded="false" aria-controls="calendar-panel"><svg aria-hidden="true" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="16" rx="3"/><path d="M7 3v4M17 3v4M3 11h18M8 15h2M14 15h2"/></svg></button><button class="toggle" data-section="workspace" title="Workspace" aria-label="Open workspace" aria-expanded="false" aria-controls="calendar-panel"><svg aria-hidden="true" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7a2 2 0 0 1 2-2h5l2 3h7a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/></svg></button></div><div id="calendar-panel" hidden></div></div>`;
  const toggles = root.querySelectorAll<HTMLButtonElement>('.toggle');
  const panel = root.querySelector<HTMLElement>('#calendar-panel')!;
  panel.innerHTML = '<div class="calendar-content"></div><footer class="app-updater" aria-label="App updates"></footer>';
  const companion = new Companion(panel.querySelector<HTMLElement>('.calendar-content')!);
  const updater = new AppUpdater(panel.querySelector<HTMLElement>('.app-updater')!);
  let selected: Section | null = null;
  let changing = false;
  const select = async (section: Section) => {
    if (changing) return;
    changing = true;
    const next = selected === section ? null : section;
    try {
      if ((selected === null) !== (next === null)) await invoke('resize_widget', { expanded: next !== null });
      const wasClosed = selected === null;
      selected = next;
      root.classList.toggle('expanded', selected !== null);
      panel.hidden = selected === null;
      toggles.forEach(toggle => {
        const active = toggle.dataset.section === selected;
        toggle.setAttribute('aria-expanded', String(active));
        toggle.setAttribute('aria-label', `${active ? 'Collapse' : 'Open'} ${toggle.dataset.section}`);
      });
      companion.close();
      if (selected) { companion.open(selected); if (wasClosed) void updater.check(); }
    } catch {
      toggles.forEach(toggle => toggle.title = 'Unable to resize widget. Please restart the app.');
    } finally { changing = false; }
  };
  toggles.forEach(toggle => toggle.addEventListener('click', () => { void select(toggle.dataset.section as Section); }));
  document.addEventListener('keydown', event => { if (event.key === 'Escape' && selected) void select(selected); });
}
