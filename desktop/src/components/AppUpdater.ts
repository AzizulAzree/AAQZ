import { checkForUpdate, installUpdate, type AppUpdate } from '../api/updater';

export class AppUpdater {
  private update: AppUpdate | null = null;
  private busy = false;
  private status: HTMLElement;
  private button: HTMLButtonElement;

  constructor(root: HTMLElement) {
    root.innerHTML = '<span class="update-status" role="status"></span><button type="button" hidden>Update now</button>';
    this.status = root.querySelector('span')!;
    this.button = root.querySelector('button')!;
    this.button.addEventListener('click', () => {
      if (this.update) void this.install(); else void this.check();
    });
  }

  async check() {
    if (this.busy) return;
    this.busy = true;
    this.button.hidden = true;
    this.status.textContent = 'Checking for app updates…';
    try {
      const previous = this.update;
      this.update = null;
      await previous?.close();
      this.update = await checkForUpdate();
      this.status.textContent = this.update ? `Version ${this.update.version} available` : 'App is up to date';
      this.button.textContent = 'Update now';
      this.button.hidden = !this.update;
    } catch {
      this.status.textContent = 'Could not check for app updates';
      this.button.textContent = 'Retry';
      this.button.hidden = false;
    } finally {
      this.busy = false;
      this.button.disabled = false;
    }
  }

  private async install() {
    if (this.busy || !this.update) return;
    this.busy = true;
    this.button.disabled = true;
    this.status.textContent = 'Downloading update…';
    let downloaded = 0;
    let total = 0;
    try {
      await installUpdate(this.update, event => {
        if (event.event === 'Started') total = event.data.contentLength ?? 0;
        if (event.event === 'Progress') {
          downloaded += event.data.chunkLength;
          if (total > 0) this.status.textContent = `Downloading update… ${Math.min(100, Math.floor(downloaded / total * 100))}%`;
        }
        if (event.event === 'Finished') this.status.textContent = 'Installing update and restarting…';
      });
    } catch {
      this.status.textContent = 'Unable to finish update. Please try again.';
      this.button.textContent = 'Retry update';
      this.button.disabled = false;
      this.busy = false;
    }
  }
}
