import { invoke } from '@tauri-apps/api/core';

export class StartupSetting {
  private checkbox: HTMLInputElement;
  private status: HTMLElement;
  private busy = false;
  private enabled = false;

  constructor(root: HTMLElement) {
    root.innerHTML = '<h3>Settings</h3><label title="Open the widget automatically when you sign in to Windows"><input type="checkbox" disabled> Start with Windows</label><span role="status"></span>';
    this.checkbox = root.querySelector('input')!;
    this.status = root.querySelector('span')!;
    this.checkbox.addEventListener('change', () => { void this.change(); });
  }

  async refresh() {
    if (this.busy) return;
    this.busy = true;
    this.checkbox.disabled = true;
    try {
      this.enabled = await invoke<boolean>('autostart_status');
      this.checkbox.checked = this.enabled;
      this.status.textContent = '';
      this.checkbox.disabled = false;
    } catch {
      this.status.textContent = 'Unable to read setting. Reopen to retry.';
    } finally { this.busy = false; }
  }

  private async change() {
    this.busy = true;
    this.checkbox.disabled = true;
    this.status.textContent = '';
    try {
      this.enabled = await invoke<boolean>('set_autostart', { enabled: this.checkbox.checked });
    } catch {
      this.status.textContent = 'Could not save. Please try again.';
    } finally {
      this.checkbox.checked = this.enabled;
      this.checkbox.disabled = false;
      this.busy = false;
    }
  }
}
