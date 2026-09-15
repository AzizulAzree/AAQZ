export default (config) => ({
    ...config, selectedDate: config.initialDate, view: 'agenda', modal: null, selectedEntry: null, busy: false, error: '', returnFocus: null,
    form: { title: '', entry_date: '', details: '', follow_up_enabled: false, follow_up_days: 3 },
    holidayItems: [], holidayStatus: 'loading', holidayRegion: 'national',
    init() {
        const date = new URLSearchParams(location.search).get('date');
        if (this.days.some(day => day.date === date)) this.selectedDate = date;
        this.$watch('modal', value => { document.body.classList.toggle('cal-modal-open', !!value); });
        try { const region = localStorage.getItem('calendar-holiday-region'); if (region === 'national' || Object.hasOwn(this.holidayStates ?? {}, region)) this.holidayRegion = region; } catch {}
        this.loadHolidays();
    },
    async loadHolidays() {
        if (!this.holidayUrl) return;
        try {
            const response = await fetch(this.holidayUrl, {headers:{Accept:'application/json'}});
            if (!response.ok) throw new Error('Holiday dates unavailable');
            const result = await response.json();
            this.holidayItems = Array.isArray(result.holidays) ? result.holidays : [];
            this.holidayStatus = result.status ?? 'unavailable';
        } catch { this.holidayStatus = 'unavailable'; }
        if (this.upcomingHolidayUrl) {
            try {
                const response = await fetch(this.upcomingHolidayUrl, {headers:{Accept:'application/json'}});
                if (response.ok) { const result = await response.json(); this.holidayItems.push(...(result.holidays ?? [])); }
            } catch {}
        }
    },
    changeHolidayRegion() { try { localStorage.setItem('calendar-holiday-region', this.holidayRegion); } catch {} },
    holidaysFor(date) {
        return this.holidayItems.filter(item => item.date === date && (item.kind === 'observance' || (this.holidayRegion === 'national' ? item.nationwide : item.states.includes(this.holidayRegion))));
    },
    holidayLabel(date) { const items = this.holidaysFor(date); return items.length ? items[0].name + (items.length > 1 ? ` +${items.length - 1}` : '') : ''; },
    get day() { return this.days.find(day => day.date === this.selectedDate) ?? this.days[0]; },
    get week() { const index = this.days.findIndex(day => day.date === this.selectedDate); return this.days.slice(Math.floor(index / 7) * 7, Math.floor(index / 7) * 7 + 7); },
    selectDay(date) {
        this.selectedDate = date;
        const url = new URL(location.href); url.searchParams.set('date', date); history.replaceState(null, '', url);
    },
    changeMonth(month) { if (/^\d{4}-\d{2}$/.test(month)) location.assign(`${this.baseUrl}?month=${month}`); },
    open(mode, entry = null) {
        if (this.busy) return;
        if (!this.modal) this.returnFocus = document.activeElement;
        if (mode !== 'create' && !entry) return;
        if (entry) this.selectedEntry = { ...entry };
        this.error = '';
        if (mode === 'create') this.form = { title: '', entry_date: this.selectedDate, details: '', follow_up_enabled: false, follow_up_days: 3 };
        if (mode === 'edit') this.form = { title: entry.title, entry_date: entry.original_date, details: entry.details ?? '', follow_up_enabled: entry.follow_up_enabled, follow_up_days: entry.follow_up_days ?? 3 };
        this.modal = mode;
        this.$nextTick(() => document.getElementById(mode === 'create' || mode === 'edit' ? 'cal-title-input' : 'cal-modal-cancel')?.focus());
    },
    close() { if (this.busy) return; this.modal = null; this.error = ''; this.$nextTick(() => this.returnFocus?.focus()); },
    get followUpLabel() {
        if (!this.form.entry_date || !this.form.follow_up_enabled) return '';
        const date = new Date(`${this.form.entry_date}T12:00:00`); date.setDate(date.getDate() + Number(this.form.follow_up_days || 0));
        return Number.isNaN(date.valueOf()) ? '' : date.toLocaleDateString('en-GB', {day:'numeric', month:'short', year:'numeric'});
    },
    async submit() {
        if (this.busy) return;
        this.busy = true; this.error = '';
        try {
            const response = await fetch(this.modal === 'create' ? this.storeUrl : this.selectedEntry.manage_url, {
                method: this.modal === 'delete' ? 'DELETE' : this.modal === 'edit' ? 'PATCH' : 'POST',
                headers: { 'Content-Type':'application/json', Accept:'application/json', 'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify(this.modal === 'delete' ? {confirmed:true} : {...this.form, follow_up_days:this.form.follow_up_enabled ? this.form.follow_up_days : null})
            });
            const result = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(result.errors ? Object.values(result.errors).flat().join(' ') : result.message ?? 'Unable to save. Please try again.');
            location.assign(result.redirect);
        } catch (error) { this.error = error.message; this.busy = false; }
    },
    trap(event) {
        if (event.key !== 'Tab') return;
        const fields = [...event.currentTarget.querySelectorAll('button,input,textarea,select,a[href]')].filter(el => el.getClientRects().length && !el.disabled);
        const first = fields[0], last = fields.at(-1);
        if (event.shiftKey && document.activeElement === first) {event.preventDefault(); last?.focus();}
        if (!event.shiftKey && document.activeElement === last) {event.preventDefault(); first?.focus();}
    }
});
