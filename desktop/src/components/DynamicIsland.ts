import { invoke } from '@tauri-apps/api/core';
import { Companion } from './Companion';
import { AppUpdater } from './AppUpdater';

export function mountIsland(root: HTMLElement) {
  root.innerHTML = `<div class="island"><button class="toggle" aria-label="Open calendar" aria-expanded="false" aria-controls="calendar-panel"><svg aria-hidden="true" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="16" rx="3"/><path d="M7 3v4M17 3v4M3 11h18M8 15h2M14 15h2"/></svg></button><div id="calendar-panel" hidden></div></div>`;
  const toggle = root.querySelector<HTMLButtonElement>('.toggle')!;
  const panel = root.querySelector<HTMLElement>('#calendar-panel')!;
  panel.innerHTML = '<div class="calendar-content"></div><footer class="app-updater" aria-label="App updates"></footer>';
  const calendar = new Companion(panel.querySelector<HTMLElement>('.calendar-content')!);
  const updater = new AppUpdater(panel.querySelector<HTMLElement>('.app-updater')!);
  let open = false;
  let changing = false;
  const setOpen = async () => {
    if (changing) return;
    changing = true;
    try {
      await invoke('resize_widget', { expanded: !open });
      open = !open;
      root.classList.toggle('expanded', open);
      panel.hidden = !open;
      toggle.setAttribute('aria-expanded', String(open));
      toggle.setAttribute('aria-label', open ? 'Collapse calendar' : 'Open calendar');
      if (open) { calendar.open(); void updater.check(); } else calendar.close();
    } catch {
      toggle.title = 'Unable to resize widget. Please restart the app.';
    } finally { changing = false; }
  };
  toggle.addEventListener('click', () => { void setOpen(); });
  document.addEventListener('keydown', event => { if (event.key === 'Escape' && open) void setOpen(); });
}
