import DOMPurify from 'dompurify';

export function mountStickyNote(root: HTMLElement, content: string | null) {
  root.innerHTML = '<section class="my-notes" aria-label="My notes"><h2>My notes</h2><div class="sticky-note-paper"><p class="sticky-note-kicker">Sticky note</p><div class="sticky-note-content" tabindex="0" role="region" aria-label="Sticky note content"></div></div></section>';
  const body = root.querySelector<HTMLElement>('.sticky-note-content')!;
  const text = content ?? '';
  // Match Laravel's handling of older plain-text notes and newer editor HTML.
  if (!/<[a-z][\s\S]*>/i.test(text)) {
    body.textContent = text.trim() ? text : 'Your sticky note is empty. Add a note in AAQZ.';
    return;
  }
  const fragment = DOMPurify.sanitize(text, {
    ALLOWED_TAGS: ['p', 'div', 'br', 'b', 'strong', 'i', 'em', 's', 'strike', 'del', 'u', 'ul', 'ol', 'li', 'span', 'font'],
    ALLOWED_ATTR: ['style'],
    ALLOW_DATA_ATTR: false,
    ALLOW_ARIA_ATTR: false,
    RETURN_DOM_FRAGMENT: true,
  });
  // Web editor spans may carry formatting as CSS. Convert only text styles to
  // fixed local classes, removing the original CSS before attaching the fragment.
  for (const element of fragment.querySelectorAll<HTMLElement>('[style]')) {
    const weight = element.style.fontWeight;
    const italic = element.style.fontStyle;
    const decoration = `${element.style.textDecoration} ${element.style.textDecorationLine}`;
    element.removeAttribute('style');
    if (/^(bold|bolder|[6-9]00)$/.test(weight)) element.classList.add('note-format-bold');
    if (/^(italic|oblique)/.test(italic)) element.classList.add('note-format-italic');
    if (/\bline-through\b/.test(decoration)) element.classList.add('note-format-strike');
    if (/\bunderline\b/.test(decoration)) element.classList.add('note-format-underline');
  }
  if (!fragment.textContent?.trim()) body.textContent = 'Your sticky note is empty. Add a note in AAQZ.';
  else body.replaceChildren(fragment);
}
