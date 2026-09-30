import { invoke } from '@tauri-apps/api/core';

interface WorkspaceNode { id: number; parent_id: number | null; type: 'folder' | 'note' | 'shortcut'; name: string; url: string | null }
interface Workspace { id: number; name: string; nodes: WorkspaceNode[] }
export interface WorkspaceData { workspaces: Workspace[]; sticky_note: string | null }

const icons = {
  folder: '<path d="M3 7a2 2 0 0 1 2-2h5l2 3h7a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/>',
  shortcut: '<path d="m10 13 4-4M8 16l-1 1a4 4 0 0 1-6-6l4-4a4 4 0 0 1 6 0M16 8l1-1a4 4 0 0 1 6 6l-4 4a4 4 0 0 1-6 0"/>',
  note: '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9Z"/><path d="M14 3v6h6M8 13h8M8 17h6"/>',
};

function action(label: string, run: () => void) {
  const button = document.createElement('button'); button.type = 'button'; button.textContent = label;
  button.addEventListener('click', run); return button;
}

export class WorkspaceBrowser {
  private view = 0;
  private notes = new Map<number, string>();
  constructor(private root: HTMLElement, private data: WorkspaceData, private valid: () => boolean, private expired: () => void) {
    this.home();
  }

  private reset(title: string, focus = true) {
    this.view++;
    this.root.replaceChildren();
    this.root.classList.add('workspace-browser');
    const heading = document.createElement('h2'); heading.textContent = title; heading.tabIndex = -1;
    this.root.append(heading);
    if (focus) heading.focus({ preventScroll: true });
    if (this.root.parentElement) this.root.parentElement.scrollTop = 0;
    return heading;
  }

  private home(focus = false) {
    this.reset('Workspace', focus);
    for (const workspace of this.data.workspaces) {
      const section = document.createElement('section');
      const title = document.createElement('h3'); title.textContent = workspace.name; section.append(title);
      this.items(section, workspace, []); this.root.append(section);
    }
    if (!this.data.workspaces.length) this.notice('No workspace items yet. Add them in AAQZ.');
  }

  private grid(parent: HTMLElement) {
    const grid = document.createElement('div'); grid.className = 'workspace-grid'; parent.append(grid); return grid;
  }

  private tile(name: string, type: WorkspaceNode['type'], run: () => void, detail?: string) {
    const tile = document.createElement('button'); tile.type = 'button'; tile.className = `workspace-tile ${type}`;
    tile.title = name;
    tile.setAttribute('aria-label', `${type === 'folder' ? 'Open folder' : type === 'note' ? 'Read note' : 'Open link'} ${name}`);
    const icon = document.createElement('span'); icon.className = 'workspace-icon'; icon.setAttribute('aria-hidden', 'true');
    icon.innerHTML = `<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">${icons[type]}</svg>`;
    const label = document.createElement('span'); label.className = 'workspace-name'; label.textContent = name;
    const kind = document.createElement('span'); kind.className = 'workspace-kind'; kind.setAttribute('aria-hidden', 'true');
    kind.textContent = detail ?? (type === 'shortcut' ? 'Link ↗' : 'Note');
    tile.append(icon, label, kind); tile.addEventListener('click', run); return tile;
  }

  private items(parent: HTMLElement, workspace: Workspace, path: WorkspaceNode[]) {
    const parentId = path.at(-1)?.id ?? null;
    const nodes = workspace.nodes.filter(node => node.parent_id === parentId && !path.some(ancestor => ancestor.id === node.id));
    nodes.sort((a, b) => Number(b.type === 'folder') - Number(a.type === 'folder') || a.name.localeCompare(b.name));
    const grid = this.grid(parent);
    for (const node of nodes) {
      if (node.type === 'shortcut' && !node.url) continue;
      const tile = this.tile(node.name, node.type, () => {
        if (node.type === 'folder') this.folder(workspace, [...path, node]);
        else if (node.type === 'note') void this.note(workspace, path, node);
        else void this.link(node.url!);
      }, node.type === 'folder' ? `${workspace.nodes.filter(child => child.parent_id === node.id).length} items` : undefined);
      if (node.type === 'shortcut') tile.title = `${node.name}\n${node.url}`;
      grid.append(tile);
    }
    if (!grid.childElementCount) {
      const empty = document.createElement('p'); empty.className = 'workspace-empty';
      empty.textContent = path.length ? 'This folder is empty.' : 'No items in this workspace yet.'; parent.append(empty);
    }
  }

  private breadcrumbs(workspace: Workspace, path: WorkspaceNode[]) {
    const nav = document.createElement('nav'); nav.className = 'workspace-breadcrumbs'; nav.setAttribute('aria-label', 'Folder location');
    const append = (crumb: HTMLButtonElement) => {
      if (nav.childElementCount) {
        const separator = document.createElement('span'); separator.textContent = '/'; separator.setAttribute('aria-hidden', 'true'); nav.append(separator);
      }
      nav.append(crumb);
    };
    append(action('All workspaces', () => this.home(true))); append(action(workspace.name, () => this.folder(workspace, [])));
    path.forEach((node, index) => {
      const crumb = action(node.name, () => this.folder(workspace, path.slice(0, index + 1)));
      if (index === path.length - 1) crumb.setAttribute('aria-current', 'page');
      append(crumb);
    });
    this.root.prepend(nav);
  }

  private folder(workspace: Workspace, path: WorkspaceNode[]) {
    this.reset(path.at(-1)?.name ?? workspace.name);
    this.breadcrumbs(workspace, path);
    this.items(this.root, workspace, path);
  }

  private reader(name: string, text: string, back: () => void) {
    this.reset(name);
    const backButton = action('← Back', back); backButton.className = 'workspace-back';
    this.root.prepend(backButton);
    const body = document.createElement('div'); body.className = 'note-body'; body.textContent = text || 'This note is empty.';
    this.root.append(body); return body;
  }

  private async note(workspace: Workspace, path: WorkspaceNode[], node: WorkspaceNode) {
    const back = () => path.length ? this.folder(workspace, path) : this.home(true);
    const cached = this.notes.get(node.id);
    const body = this.reader(node.name, cached ?? 'Loading note…', back);
    if (cached !== undefined) return;
    const view = this.view;
    try {
      const note = await invoke<{ content: string }>('note', { id: node.id });
      if (!this.valid() || view !== this.view) return;
      this.notes.set(node.id, note.content); body.textContent = note.content || 'This note is empty.';
    } catch (error) {
      if (!this.valid() || view !== this.view) return;
      if (error === 'unauthenticated') { this.expired(); return; }
      body.textContent = 'Unable to load note.';
      body.append(action('Retry note', () => { void this.note(workspace, path, node); }));
    }
  }

  private async link(url: string) {
    const view = this.view;
    try { await invoke('open_shortcut', { url }); }
    catch {
      if (this.valid() && view === this.view) this.notice('Cannot open this shortcut. Only http:// and https:// links are supported.');
    }
  }

  private notice(text: string) {
    let status = this.root.querySelector<HTMLElement>('.workspace-status');
    if (!status) { status = document.createElement('p'); status.className = 'workspace-status status'; status.setAttribute('role', 'status'); this.root.append(status); }
    status.textContent = text;
  }
}
