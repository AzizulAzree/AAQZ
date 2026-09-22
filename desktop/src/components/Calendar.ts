import { getCalendar, signIn, type CalendarEvent } from '../api/calendar';

const dateKey = (date: Date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;

export class Calendar {
  private month = new Date(new Date().getFullYear(), new Date().getMonth(), 1);
  private selected = dateKey(new Date());
  private events: CalendarEvent[] = [];
  private request = 0;
  private active = false;

  constructor(private root: HTMLElement) {}

  open() { this.active = true; void this.load(); }
  close() { this.active = false; this.request++; this.events = []; this.root.replaceChildren(); }

  private async load() {
    const request = ++this.request;
    this.events = [];
    this.render('Loading…');
    try {
      const result = await getCalendar(dateKey(this.month).slice(0, 7));
      if (!this.active || request !== this.request) return;
      this.events = result.events;
      this.render();
    } catch (error) {
      if (!this.active || request !== this.request) return;
      if (error === 'unauthenticated') this.renderLogin();
      else this.render('Unable to connect to server');
    }
  }

  private renderLogin() {
    this.root.innerHTML = `<form class="login"><h2>Sign in to your calendar</h2>
      <label>Email<input name="email" type="email" autocomplete="username" required></label>
      <label>Password<input name="password" type="password" autocomplete="current-password" required></label>
      <p class="status" role="status">Use your existing AAQZ account.</p>
      <button type="submit">Sign in</button></form>`;
    const form = this.root.querySelector('form')!;
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      const data = new FormData(form);
      const button = form.querySelector('button')!;
      const status = form.querySelector('.status')!;
      button.disabled = true;
      status.textContent = 'Signing in…';
      try {
        const signingIn = signIn(String(data.get('email')), String(data.get('password')));
        (form.elements.namedItem('password') as HTMLInputElement).value = '';
        await signingIn;
        if (this.active) await this.load();
      } catch (error) {
        status.textContent = error === 'invalid_credentials' ? 'Sign-in failed. Check your details or try again later.' : 'Unable to connect to server';
        button.disabled = false;
      }
    });
  }

  private render(message = '') {
    this.root.innerHTML = `<div class="month-heading"><button class="previous" aria-label="Previous month">‹</button><h2></h2><button class="next" aria-label="Next month">›</button></div>
      <div class="weekdays" aria-hidden="true"><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span><span>Su</span></div>
      <div class="days" role="group" aria-label="Calendar dates"></div><p class="status" role="status"></p><section class="events"><h3></h3><ul></ul></section>`;
    this.root.querySelector('h2')!.textContent = this.month.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
    this.root.querySelector('.status')!.textContent = message;
    for (const [selector, offset] of [['.previous', -1], ['.next', 1]] as const) {
      this.root.querySelector(selector)!.addEventListener('click', () => {
        this.month = new Date(this.month.getFullYear(), this.month.getMonth() + offset, 1);
        this.selected = dateKey(this.month);
        void this.load();
      });
    }
    const days = this.root.querySelector('.days')!;
    const offset = (this.month.getDay() + 6) % 7;
    const count = new Date(this.month.getFullYear(), this.month.getMonth() + 1, 0).getDate();
    for (let index = 0; index < offset; index++) days.append(document.createElement('span'));
    for (let day = 1; day <= count; day++) {
      const date = new Date(this.month.getFullYear(), this.month.getMonth(), day);
      const key = dateKey(date);
      const hasEvents = this.events.some(event => event.date === key);
      const button = document.createElement('button');
      button.textContent = String(day);
      button.classList.toggle('today', key === dateKey(new Date()));
      button.classList.toggle('selected', key === this.selected);
      button.classList.toggle('has-events', hasEvents);
      button.setAttribute('aria-label', `${date.toLocaleDateString(undefined, { dateStyle: 'full' })}${hasEvents ? ', has events' : ''}`);
      button.setAttribute('aria-pressed', String(key === this.selected));
      if (key === dateKey(new Date())) button.setAttribute('aria-current', 'date');
      button.addEventListener('click', () => { this.selected = key; this.render(message); });
      days.append(button);
    }
    this.root.querySelector('h3')!.textContent = `Events · ${this.selected}`;
    const list = this.root.querySelector('ul')!;
    const selectedEvents = this.events.filter(event => event.date === this.selected);
    for (const event of selectedEvents) {
      const item = document.createElement('li');
      item.textContent = event.title;
      list.append(item);
    }
    if (!message && selectedEvents.length === 0) {
      const item = document.createElement('li');
      item.className = 'empty'; item.textContent = 'No events for this date'; list.append(item);
    }
  }
}
