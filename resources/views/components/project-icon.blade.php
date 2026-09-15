@props(['name', 'class' => ''])
<svg {{ $attributes->merge(['class' => 'pr-icon '.$class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($name)
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
