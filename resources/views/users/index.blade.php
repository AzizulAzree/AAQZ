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

    <div
        x-data="{ addUserModalOpen: false }"
        x-init="
            @if ($errors->has('name') || $errors->has('email') || $errors->has('password'))
                addUserModalOpen = true;
            @endif
        "
        class="py-12"
    >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Users') }}</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('People who can sign in to this site.') }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="text-sm text-gray-500">
                            {{ trans_choice('{1} :count user|[2,*] :count users', $users->count(), ['count' => $users->count()]) }}
                        </div>
                        <button
                            type="button"
                            class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700"
                            x-on:click="addUserModalOpen = true"
                        >
                            {{ __('Add User') }}
                        </button>
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
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">{{ __('Color') }}</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">{{ __('Created') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($users as $managedUser)
                                <tr>
                                    <td class="px-4 py-3 text-gray-900">{{ $managedUser->name }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $managedUser->email }}</td>
                                    <td class="px-4 py-3 text-gray-600">
                                        <div class="flex items-center gap-2">
                                            <span class="h-3 w-3 rounded-full border border-gray-200" style="background-color: {{ $managedUser->ownerColor() }}"></span>
                                            <span>{{ $managedUser->ownerColor() }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500">{{ optional($managedUser->created_at)->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                                        {{ __('No users found yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @include('admin.database.partials.overview', ['databaseOverview' => $databaseOverview])
        </div>

        <template x-if="addUserModalOpen">
            <div class="fixed inset-0 z-50 flex items-center justify-center px-4">
                <div class="absolute inset-0 bg-slate-900/40" x-on:click="addUserModalOpen = false"></div>
                <div class="relative z-10 w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm text-slate-500">{{ __('New user') }}</p>
                            <h3 class="mt-1 text-lg font-semibold text-slate-900">{{ __('Add User') }}</h3>
                            <p class="mt-1 text-sm text-slate-600">
                                {{ __('Create another login for someone who needs access.') }}
                            </p>
                        </div>
                        <button type="button" class="rounded-full border border-slate-200 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50" x-on:click="addUserModalOpen = false">
                            {{ __('Close') }}
                        </button>
                    </div>

                    <form method="POST" action="{{ route('users.store') }}" class="mt-6 space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="modal-user-name" :value="__('Name')" />
                            <x-text-input id="modal-user-name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus autocomplete="name" />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <x-input-label for="modal-user-email" :value="__('Email')" />
                            <x-text-input id="modal-user-email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required autocomplete="username" />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>

                        <div>
                            <x-input-label for="modal-user-password" :value="__('Password')" />
                            <x-text-input id="modal-user-password" name="password" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                            <x-input-error class="mt-2" :messages="$errors->get('password')" />
                        </div>

                        <div>
                            <x-input-label for="modal-user-password-confirmation" :value="__('Confirm Password')" />
                            <x-text-input id="modal-user-password-confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Create User') }}</x-primary-button>
                            <p class="text-xs text-slate-500">{{ __('The new account will be able to sign in immediately after creation.') }}</p>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>
</x-app-layout>
