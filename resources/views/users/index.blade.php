<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Settings') }}
            </h2>

            <a
                href="{{ route('database.index') }}"
                class="inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                title="{{ __('Open a read-only view of your stored data.') }}"
            >
                {{ __('Open Data Browser') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Users') }}</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('People who can sign in to this site.') }}
                        </p>
                    </div>

                    <div class="text-sm text-gray-500">
                        {{ trans_choice('{1} :count user|[2,*] :count users', $users->count(), ['count' => $users->count()]) }}
                    </div>
                </div>

                @if (session('status') === 'user-created')
                    <p class="mt-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {{ __('User created successfully.') }}
                    </p>
                @endif

                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">{{ __('Name') }}</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">{{ __('Email') }}</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">{{ __('Created') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($users as $managedUser)
                                <tr>
                                    <td class="px-4 py-3 text-gray-900">{{ $managedUser->name }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $managedUser->email }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ optional($managedUser->created_at)->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-gray-500">
                                        {{ __('No users found yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-2xl">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Add User') }}</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Create another login for someone who needs access.') }}
                    </p>

                    <form method="POST" action="{{ route('users.store') }}" class="mt-6 space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus autocomplete="name" />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required autocomplete="username" />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>

                        <div>
                            <x-input-label for="password" :value="__('Password')" />
                            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                            <x-input-error class="mt-2" :messages="$errors->get('password')" />
                        </div>

                        <div>
                            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Create User') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            @include('admin.database.partials.overview', ['databaseOverview' => $databaseOverview])
        </div>
    </div>
</x-app-layout>
