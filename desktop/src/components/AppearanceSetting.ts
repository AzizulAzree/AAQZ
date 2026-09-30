type Appearance = 'system' | 'light' | 'dark';
const key = 'aaqz-widget-appearance';
export function mountAppearance(root: HTMLElement) {
  const media = matchMedia('(prefers-color-scheme: dark)');
  let choice: Appearance = 'system';
  try {
    const saved = localStorage.getItem(key);
    if (saved === 'light' || saved === 'dark') choice = saved;
  } catch { /* Appearance still works when storage is unavailable. */ }
  root.innerHTML = `<fieldset class="appearance-setting"><legend>Appearance</legend><div class="appearance-options">${(['system', 'light', 'dark'] as const).map(value => `<label><input type="radio" name="appearance" value="${value}"><span>${value[0].toUpperCase() + value.slice(1)}</span></label>`).join('')}</div></fieldset>`;
  const apply = () => {
    document.documentElement.dataset.theme = choice === 'system' ? (media.matches ? 'dark' : 'light') : choice;
    root.querySelectorAll<HTMLInputElement>('input').forEach(input => { input.checked = input.value === choice; });
  };
  root.addEventListener('change', event => {
    if (!(event.target instanceof HTMLInputElement)) return;
    choice = event.target.value as Appearance;
    try { localStorage.setItem(key, choice); } catch { /* Keep the choice for this session. */ }
    apply();
  });
  media.addEventListener('change', apply);
  apply();
}
