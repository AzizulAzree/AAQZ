<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Private Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-2">
                    <p>{{ __('You are signed in to the private area of this app.') }}</p>
                    <p class="text-sm text-gray-600">{{ __('Public self-registration is disabled. Create or manage your single local account with Artisan when needed.') }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
