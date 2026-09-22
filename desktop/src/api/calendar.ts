import { invoke } from '@tauri-apps/api/core';

export interface CalendarEvent { id: number | string; title: string; date: string }
export interface CalendarResponse { events: CalendarEvent[] }

export const getCalendar = (month: string) => invoke<CalendarResponse>('calendar', { month });
export const signIn = (email: string, password: string) => invoke<void>('login', { email, password });
