import DOMPurify from 'dompurify';
import { invoke } from '@tauri-apps/api/core';

const formats = [
  ['bold', 'Bold', 'B'], ['italic', 'Italic', 'I'], ['strikeThrough', 'Strikethrough', 'S'],
  ['insertUnorderedList', 'Bullet list', '•'], ['insertOrderedList', 'Numbered list', '1.'], ['removeFormat', 'Clear style', 'Tx'],
] as const;
function clean(html: string): string {
  const fragment = DOMPurify.sanitize(html, {
    ALLOWED_TAGS: ['p', 'div', 'br', 'b', 'strong', 'i', 'em', 's', 'strike', 'del', 'u', 'ul', 'ol', 'li', 'span', 'font'],
    ALLOWED_ATTR: ['style'], ALLOW_DATA_ATTR: false, ALLOW_ARIA_ATTR: false, RETURN_DOM_FRAGMENT: true,
  });
  // Semantic HTML works in both editors, including styles produced by WebView.
  for (const element of fragment.querySelectorAll<HTMLElement>('[style]')) {
    const style = element.style;
    const tags = [
      /^(bold|bolder|[6-9]00)$/.test(style.fontWeight) ? 'b' : '',
      /^(italic|oblique)/.test(style.fontStyle) ? 'i' : '',
      /\bline-through\b/.test(`${style.textDecoration} ${style.textDecorationLine}`) ? 'strike' : '',
      /\bunderline\b/.test(`${style.textDecoration} ${style.textDecorationLine}`) ? 'u' : '',
    ].filter(Boolean);
    element.removeAttribute('style');
    for (const tag of tags) {
      const wrapper = document.createElement(tag);
      wrapper.append(...Array.from(element.childNodes)); element.append(wrapper);
    }
  }
  const holder = document.createElement('div'); holder.append(fragment);
  return holder.textContent?.trim() ? holder.innerHTML : '';
}
function initial(content: string | null): string {
  const text = content ?? '';
  if (/<[a-z][\s\S]*>/i.test(text)) return clean(text);
  const holder = document.createElement('div'); holder.textContent = text;
  return holder.innerHTML;
}
class Draft {
  html: string;
  saved: string;
  saving = false;
  notice = 'Autosave on';
  listeners = new Set<() => void>();
  constructor(private accountId: number, html: string) { this.html = this.saved = html; }
  get dirty() { return this.html !== this.saved; }
  notify() { this.listeners.forEach(listener => listener()); }
  async save() {
    if (this.saving || !this.dirty) return;
    const snapshot = this.html;
    if (Array.from(snapshot).length > 5000) { this.notice = 'Note too long (5,000 characters including formatting).'; this.notify(); return; }
    this.saving = true; this.notice = 'Saving…'; this.notify();
    let success = false;
    try {
      await invoke('save_sticky_note', { accountId: this.accountId, content: snapshot });
      this.saved = snapshot; success = true; this.notice = this.dirty ? 'Unsaved changes' : 'Saved';
    } catch (error) {
      this.notice = error === 'unauthenticated' ? 'Sign in again to save. Draft kept for this session.'
        : error === 'note_too_long' ? 'Note too long (5,000 characters including formatting).'
        : 'Could not save. Retry; draft kept for this session.';
    } finally {
      this.saving = false; this.notify();
      // Serialize writes so an older response cannot overwrite newer typing.
      if (success && this.dirty) void this.save();
    }
  }
}
// Failed/in-flight drafts stay in memory, scoped to their account; never on disk.
const drafts = new Map<number, Draft>();
export function mountStickyNote(root: HTMLElement, content: string | null, accountId: number): () => void {
  let draft = drafts.get(accountId);
  if (!draft || (!draft.dirty && !draft.saving)) { draft = new Draft(accountId, initial(content)); drafts.set(accountId, draft); }
  const current = draft;
  root.innerHTML = `<section class="my-notes" aria-label="My notes"><h2>My notes</h2><div class="sticky-note-paper"><p class="sticky-note-kicker">Sticky note</p><div class="sticky-note-toolbar" role="group" aria-label="Text formatting">${formats.map(([command, label, icon]) => `<button type="button" data-format="${command}" aria-label="${label}" title="${label}"${command === 'removeFormat' ? '' : ' aria-pressed="false"'}>${icon}</button>`).join('')}</div><div class="sticky-note-content" contenteditable="true" role="textbox" aria-multiline="true" aria-label="Sticky note content" data-placeholder="Write a note…" spellcheck="true"></div><div class="sticky-note-save"><span role="status" aria-live="polite"></span><button type="button">Save now</button></div></div></section>`;
  const body = root.querySelector<HTMLElement>('.sticky-note-content')!;
  const status = root.querySelector<HTMLElement>('.sticky-note-save span')!;
  const save = root.querySelector<HTMLButtonElement>('.sticky-note-save button')!;
  const buttons = root.querySelectorAll<HTMLButtonElement>('[data-format]');
  body.innerHTML = current.html;
  let timer: ReturnType<typeof setTimeout> | undefined;
  let range: Range | undefined;
  let composing = false;
  const update = () => { status.textContent = current.notice; save.disabled = !current.dirty || current.saving; };
  current.listeners.add(update); update();
  const remember = () => {
    const selection = window.getSelection();
    if (selection?.rangeCount && body.contains(selection.getRangeAt(0).commonAncestorContainer)) {
      range = selection.getRangeAt(0).cloneRange();
      buttons.forEach(button => { if (button.dataset.format !== 'removeFormat') button.setAttribute('aria-pressed', String(document.queryCommandState(button.dataset.format!))); });
    }
  };
  const queue = () => {
    current.html = clean(body.innerHTML);
    current.notice = current.dirty ? 'Unsaved changes' : 'Saved'; update();
    clearTimeout(timer);
    if (!composing && current.dirty) timer = setTimeout(() => { void current.save(); }, 450);
  };
  body.addEventListener('input', queue);
  body.addEventListener('compositionstart', () => { composing = true; clearTimeout(timer); });
  body.addEventListener('compositionend', () => { composing = false; queue(); });
  body.addEventListener('keyup', remember); body.addEventListener('mouseup', remember);
  body.addEventListener('paste', event => { event.preventDefault(); document.execCommand('insertText', false, event.clipboardData?.getData('text/plain') ?? ''); queue(); });
  body.addEventListener('drop', event => { event.preventDefault(); });
  buttons.forEach(button => {
    button.addEventListener('mousedown', event => { event.preventDefault(); remember(); });
    button.addEventListener('click', () => {
      body.focus();
      const selection = window.getSelection();
      if (range && body.contains(range.commonAncestorContainer)) { selection?.removeAllRanges(); selection?.addRange(range); }
      document.execCommand(button.dataset.format!, false); remember(); queue();
    });
  });
  save.addEventListener('click', () => { clearTimeout(timer); queue(); clearTimeout(timer); void current.save(); });
  return () => { clearTimeout(timer); current.listeners.delete(update); current.html = clean(body.innerHTML); void current.save(); };
}
