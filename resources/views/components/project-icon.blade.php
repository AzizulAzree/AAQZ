@props(['name', 'class' => ''])
<svg {{ $attributes->merge(['class' => 'pr-icon '.$class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($name)
@case('document')<path d="M14 3H5v18h14V8zM14 3v5h5M8 12h8M8 16h5"/>@break
@case('wallet')<path d="M20 7V4H4v16h17V7H4M21 11h-6v5h6M17 13.5h.01"/>@break
@case('form')<path d="M4 3h16v18H4zM8 8h1M12 8h4M8 12h1M12 12h4M8 16h1M12 16h4"/>@break
@case('person')<circle cx="12" cy="7" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>@break
@case('portfolio')<path d="M3 7h18v14H3zM8 7V3h8v4M3 12h18M10 12v3h4v-3"/>@break
@case('users')<circle cx="10" cy="7" r="3"/><path d="M3 21v-3a7 7 0 0 1 14 0v3M17 4a3 3 0 0 1 0 6M21 21v-3a7 7 0 0 0-3-5"/>@break
@case('logout')<path d="M9 3H4v18h5M9 12h12M17 8l4 4-4 4"/>@break
@case('edit')<path d="m15 5 4 4M4 20l4-1L20 7a2.8 2.8 0 0 0-4-4L4 15Z"/>@break
@case('trash')<path d="M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7M14 10v7"/>@break
@case('warning')<path d="m12 3 10 18H2Z M12 9v5M12 17h.01"/>@break
@case('folder')<path d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/>@break
@case('grid')<rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/>@break
@case('plus')<path d="M12 5v14M5 12h14"/>@break
@case('search')<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>@break
@case('arrow')<path d="M7 17 17 7M7 7h10v10"/>@break
@case('chevron')<path d="m9 5 7 7-7 7"/>@break
@case('clock')<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>@break
@case('link')<path d="m10 13 4-4M8 16l-1 1a4 4 0 0 1-6-6l4-4a4 4 0 0 1 6 0m2 1 1-1a4 4 0 0 1 6 6l-4 4a4 4 0 0 1-6 0" transform="translate(1 0)"/>@break
@endswitch
</svg>
