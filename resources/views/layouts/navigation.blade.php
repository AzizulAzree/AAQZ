@php
    $navigationItems = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'match' => 'dashboard*', 'icon' => 'grid'],
        ['label' => 'BPP', 'route' => 'bpp.index', 'match' => 'bpp.*', 'icon' => 'document'],
        ['label' => 'Workspaces', 'route' => 'project.index', 'match' => 'project.*', 'icon' => 'folder'],
    ];
    if (Auth::user()->canAccessFinancePage()) {
        $navigationItems[] = ['label' => 'Budget', 'route' => 'finance.index', 'match' => 'finance.*', 'icon' => 'wallet'];
    }
    $navigationItems[] = ['label' => 'Forms', 'route' => 'forms.index', 'match' => 'forms.*', 'icon' => 'form'];
@endphp
<header class="app-navigation" x-data="{ accountOpen: false }" @keydown.escape.window="if (accountOpen) { accountOpen = false; $refs.accountTrigger.focus() }">
    <div class="app-navigation-inner">
        <a class="app-brand" href="{{ route('dashboard') }}" aria-label="{{ __('Dashboard home') }}"><x-application-logo class="app-brand-logo" /></a>
        <nav class="app-primary-links" aria-label="{{ __('Main navigation') }}">
            @foreach ($navigationItems as $item)
                <a class="app-navigation-link" href="{{ route($item['route']) }}" @if(request()->routeIs($item['match'])) aria-current="page" @endif>
                    <x-project-icon :name="$item['icon']" /><span>{{ __($item['label']) }}</span>
                </a>
            @endforeach
        </nav>
        <div class="app-navigation-meta">
            <time class="app-navigation-date" datetime="{{ now()->toDateString() }}"><span>{{ now()->isoFormat('dddd') }}</span><strong>{{ now()->isoFormat('D MMM YYYY') }}</strong></time>
            <div class="app-account" @click.outside="accountOpen = false">
                <button type="button" class="app-account-trigger" x-ref="accountTrigger" @click="accountOpen = ! accountOpen" :aria-expanded="accountOpen.toString()" aria-controls="app-account-menu" aria-label="{{ __('Account menu') }}">
                    <span class="app-account-avatar" style="--owner-color: {{ Auth::user()->ownerColor() }}" aria-hidden="true">{{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}</span>
                    <span class="app-account-name">{{ Auth::user()->name }}</span>
                    <svg class="app-account-chevron" :class="{ 'is-open': accountOpen }" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m6 8 4 4 4-4" /></svg>
                </button>
                <div id="app-account-menu" class="app-account-menu" x-show="accountOpen" x-cloak>
                    <p class="app-account-heading">{{ Auth::user()->name }}</p>
                    <a href="{{ route('profile.edit') }}" @if(request()->routeIs('profile.*')) aria-current="page" @endif><x-project-icon name="person" />{{ __('My account') }}</a>
                    <a href="{{ route('portfolio.show') }}" @if(request()->routeIs('portfolio.*')) aria-current="page" @endif><x-project-icon name="portfolio" />{{ __('Portfolio') }}</a>
                    @if (Auth::user()->isAdmin())
                        <a href="{{ route('users.index') }}" @if(request()->routeIs('users.*')) aria-current="page" @endif><x-project-icon name="users" />{{ __('Manage users') }}</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit"><x-project-icon name="logout" />{{ __('Sign out') }}</button></form>
                </div>
            </div>
        </div>
    </div>
</header>
