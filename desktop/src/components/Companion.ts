import { invoke } from '@tauri-apps/api/core';
import { Calendar } from './Calendar';
import { WorkspaceBrowser, type WorkspaceData } from './WorkspaceBrowser';
import { mountStickyNote } from './StickyNote';

interface Account { id: number; name: string; email: string }
interface AccountState { account: Account | null; saved: Account[] }
const message = (error: unknown) => error === 'unauthenticated' ? 'This saved login expired. Enter your password to sign in again.'
  : error === 'invalid_credentials' ? 'Sign-in failed. Check your details or try again later.'
  : error === 'storage' ? 'Windows could not access saved accounts. Try again, or sign in without saving.'
  : error === 'account_limit' ? 'You can save up to 10 accounts. Remove one first, or sign in without saving.'
  : 'Unable to connect to server';

function button(label: string, action: () => void) {
  const result = document.createElement('button'); result.type = 'button'; result.textContent = label;
  result.addEventListener('click', action); return result;
}

export class Companion {
  private active = false;
  private generation = 0;
  private calendar?: Calendar;
  private tab: 'calendar' | 'workspace' | 'notes' = 'calendar';
  private account: Account | null = null;
  private busy = false;
  constructor(private root: HTMLElement) {}
  open(section: 'calendar' | 'workspace' | 'notes') { this.tab = section; this.active = true; void this.loadAccounts(); }
  close() { this.active = false; this.generation++; this.calendar?.close(); this.root.replaceChildren(); }
  private valid(generation: number) { return this.active && generation === this.generation; }

  private async loadAccounts(forcePicker = false) {
    const generation = ++this.generation;
    this.calendar?.close(); this.root.textContent = 'Loading…';
    try {
      const state = await invoke<AccountState>('account_state');
      if (!this.valid(generation)) return;
      this.account = state.account;
      if (state.account && !forcePicker) this.showContent(); else this.picker(state.saved);
    } catch (error) {
      if (this.valid(generation)) this.loginForm('', message(error));
    }
  }

  private picker(accounts: Account[], notice = '') {
    this.root.innerHTML = '<section class="accounts"><h2>Choose an account</h2><div class="account-list"></div><p class="status" role="status"></p></section>';
    this.root.querySelector('.status')!.textContent = notice;
    const list = this.root.querySelector('.account-list')!;
    for (const account of accounts) {
      const row = document.createElement('div'); row.className = 'account-card';
      row.append(button(`Continue as ${account.name} · ${account.email}`, () => {
        void this.authenticate(() => invoke<Account>('resume_account', { id: account.id }), account.email);
      }), button(`Remove ${account.email}`, () => { void this.remove(account.id); }));
      list.append(row);
    }
    this.root.querySelector('section')!.append(button('Use another account', () => this.loginForm()));
    if (!accounts.length) this.loginForm('', notice);
  }

  private async remove(id: number) {
    if (this.busy) return;
    this.busy = true;
    const generation = this.generation;
    this.disable(true);
    try {
      const revoked = await invoke<boolean>('remove_account', { id });
      const state = await invoke<AccountState>('account_state');
      if (this.valid(generation)) this.picker(state.saved, revoked ? 'Account removed from this device.' : 'Removed from this device. Server revocation could not be confirmed.');
    } catch (error) { if (this.valid(generation)) this.status(message(error)); }
    finally { this.busy = false; if (this.valid(generation)) this.disable(false); }
  }

  private disable(disabled: boolean) { this.root.querySelectorAll<HTMLButtonElement>('button').forEach(item => item.disabled = disabled); }
  private status(text: string) { const element = this.root.querySelector('.status'); if (element) element.textContent = text; }
  private async authenticate(action: () => Promise<Account>, email = '') {
    if (this.busy) return;
    this.busy = true;
    const generation = this.generation;
    this.disable(true); this.status('Signing in…');
    try {
      this.account = await action();
      if (this.valid(generation)) this.showContent();
    } catch (error) {
      if (this.valid(generation)) {
        if (error === 'unauthenticated') this.loginForm(email, message(error));
        else this.status(message(error));
      }
    } finally { this.busy = false; if (this.valid(generation)) this.disable(false); }
  }

  private loginForm(email = '', notice = '') {
    this.root.innerHTML = `<form class="login"><h2>Sign in to AAQZ</h2>
      <label>Email<input name="email" type="email" autocomplete="username" required></label>
      <label>Password<input name="password" type="password" autocomplete="current-password" required></label>
      <label class="save-account"><input name="save" type="checkbox" checked>Save account on this device</label>
      <p class="status" role="status"></p><button type="submit">Sign in</button></form>`;
    const form = this.root.querySelector('form')!;
    (form.elements.namedItem('email') as HTMLInputElement).value = email;
    this.status(notice || 'Use your existing AAQZ account.');
    form.append(button('Back to saved accounts', () => { void this.loadAccounts(true); }));
    form.addEventListener('submit', event => {
      event.preventDefault();
      const data = new FormData(form);
      const password = String(data.get('password'));
      (form.elements.namedItem('password') as HTMLInputElement).value = '';
      void this.authenticate(() => invoke<Account>('login', { email: String(data.get('email')), password, saveAccount: data.has('save') }));
    });
  }

  private showContent() {
    this.calendar?.close();
    this.generation++;
    this.root.innerHTML = '<div class="account-heading"><span></span></div><div class="section-content"></div>';
    this.root.querySelector('.account-heading span')!.textContent = this.account?.name || 'AAQZ';
    this.root.querySelector('.account-heading')!.append(button('Switch account', () => { void this.switchAccount(); }));
    const content = this.root.querySelector<HTMLElement>('.section-content')!;
    if (this.tab === 'calendar') {
      this.calendar = new Calendar(content, () => { void this.loadAccounts(true); });
      this.calendar.open();
    } else void this.loadWorkspace(content);
  }

  private async switchAccount() {
    if (this.busy) return;
    this.busy = true;
    const generation = ++this.generation;
    this.calendar?.close(); this.root.textContent = 'Signing out…';
    try {
      await invoke('switch_account'); this.account = null;
      if (this.valid(generation)) await this.loadAccounts(true);
    } catch (error) { if (this.valid(generation)) this.loginForm('', message(error)); }
    finally { this.busy = false; }
  }

  private async loadWorkspace(content: HTMLElement) {
    const generation = this.generation;
    content.textContent = this.tab === 'notes' ? 'Loading sticky note…' : 'Loading workspace…';
    try {
      const data = await invoke<WorkspaceData>('workspace');
      if (!this.valid(generation)) return;
      if (this.tab === 'notes') mountStickyNote(content, data.sticky_note);
      else new WorkspaceBrowser(content, data, () => this.valid(generation), () => { void this.loadAccounts(true); });
    } catch (error) {
      if (!this.valid(generation)) return;
      if (error === 'unauthenticated') { void this.loadAccounts(true); return; }
      content.textContent = message(error);
      content.append(button(this.tab === 'notes' ? 'Retry sticky note' : 'Retry workspace', () => { void this.loadWorkspace(content); }));
    }
  }

}
