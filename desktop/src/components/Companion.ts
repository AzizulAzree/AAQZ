import { invoke } from '@tauri-apps/api/core';
import { Calendar } from './Calendar';

interface Account { id: number; name: string; email: string }
interface AccountState { account: Account | null; saved: Account[] }
interface Node { id: number; parent_id: number | null; type: 'folder' | 'note' | 'shortcut'; name: string; url: string | null }
interface Workspace { id: number; name: string; nodes: Node[] }
interface WorkspaceData { workspaces: Workspace[]; sticky_note: string | null }
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
  private tab: 'calendar' | 'workspace' = 'calendar';
  private account: Account | null = null;
  private busy = false;
  constructor(private root: HTMLElement) {}
  open() { this.active = true; void this.loadAccounts(); }
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
    this.root.innerHTML = '<div class="account-heading"><span></span></div><nav class="widget-tabs" aria-label="Widget sections"></nav><div class="section-content"></div>';
    this.root.querySelector('.account-heading span')!.textContent = this.account?.name || 'AAQZ';
    this.root.querySelector('.account-heading')!.append(button('Switch account', () => { void this.switchAccount(); }));
    const nav = this.root.querySelector('nav')!;
    for (const [value, label] of [['calendar', 'Calendar'], ['workspace', 'Workspace']] as const) {
      const control = button(label, () => { this.tab = value; this.showContent(); });
      control.setAttribute('aria-pressed', String(this.tab === value)); nav.append(control);
    }
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
    content.textContent = 'Loading workspace…';
    try {
      const data = await invoke<WorkspaceData>('workspace');
      if (!this.valid(generation)) return;
      content.replaceChildren();
      const status = document.createElement('p'); status.className = 'status'; status.setAttribute('role', 'status'); content.append(status);
      if (data.sticky_note) {
        const sticky = document.createElement('details'); const heading = document.createElement('summary'); heading.textContent = 'My notes';
        const body = document.createElement('p'); body.className = 'note-body'; body.textContent = data.sticky_note;
        sticky.append(heading, body); content.append(sticky);
      }
      for (const workspace of data.workspaces) {
        const group = document.createElement('details'); group.open = true;
        const heading = document.createElement('summary'); heading.textContent = workspace.name; group.append(heading);
        this.nodes(group, workspace.nodes, null, new Set(), generation); content.append(group);
        if (!workspace.nodes.length) group.append(document.createTextNode('No folders yet.'));
      }
      if (!data.workspaces.length && !data.sticky_note) status.textContent = 'No workspace items yet. Add them in AAQZ.';
    } catch (error) {
      if (!this.valid(generation)) return;
      if (error === 'unauthenticated') { void this.loadAccounts(true); return; }
      content.textContent = message(error);
      content.append(button('Retry workspace', () => { void this.loadWorkspace(content); }));
    }
  }

  private nodes(parent: HTMLElement, nodes: Node[], parentId: number | null, visited: Set<number>, generation: number) {
    for (const node of nodes.filter(item => item.parent_id === parentId && !visited.has(item.id))) {
      visited.add(node.id);
      if (node.type === 'folder') {
        const folder = document.createElement('details'); const title = document.createElement('summary'); title.textContent = node.name;
        folder.append(title); this.nodes(folder, nodes, node.id, visited, generation); parent.append(folder);
      } else if (node.type === 'note') {
        const detail = document.createElement('details'); const title = document.createElement('summary'); title.textContent = `Note · ${node.name}`;
        const body = document.createElement('div'); body.className = 'note-body'; detail.append(title, body);
        let loaded = false; let loading = false;
        detail.addEventListener('toggle', async () => {
          if (!detail.open || loaded || loading) return;
          loading = true; body.textContent = 'Loading note…';
          try {
            const note = await invoke<{ name: string; content: string }>('note', { id: node.id });
            if (this.valid(generation)) { body.textContent = note.content; loaded = true; }
          } catch (error) {
            if (this.valid(generation)) {
              if (error === 'unauthenticated') { void this.loadAccounts(true); return; }
              body.textContent = 'Unable to load note. Close and reopen it to retry.';
            }
          } finally { loading = false; }
        }); parent.append(detail);
      } else if (node.type === 'shortcut' && node.url) {
        const link = button(`↗ ${node.name}`, async () => {
          try { await invoke('open_shortcut', { url: node.url }); }
          catch { if (this.valid(generation)) this.status('Cannot open this shortcut. Only http:// and https:// links are supported.'); }
        }); link.className = 'shortcut'; link.title = node.url; parent.append(link);
      }
    }
  }
}
