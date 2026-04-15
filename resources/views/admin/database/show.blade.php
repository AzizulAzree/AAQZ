<x-app-layout>
    @php
        $editableColumns = collect($table['columns'])
            ->reject(fn (array $column) => $column['name'] === $table['primary_key'])
            ->values()
            ->all();
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Data Browser') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">{{ $table['name'] }}</p>
            </div>
            <a
                href="{{ route('database.index') }}"
                class="inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
                {{ __('Back to tables') }}
            </a>
        </div>
    </x-slot>

    <div
        x-data="{
            editModalOpen: false,
            editRecordKey: '',
            editFields: {},
            editNulls: {},
            editableColumns: @js($editableColumns),
            openEditModal(recordKey, values) {
                this.editRecordKey = recordKey;
                this.editFields = {};
                this.editNulls = {};

                this.editableColumns.forEach((column) => {
                    const raw = Object.prototype.hasOwnProperty.call(values, column.name) ? values[column.name] : '';
                    this.editFields[column.name] = raw ?? '';
                    this.editNulls[column.name] = raw === null;
                });

                this.editModalOpen = true;
            },
        }"
        x-init="
            @if ($errors->any() && old('_method') === 'PUT')
                editRecordKey = @js(old('record_key', ''));
                editFields = @js(old('values', []));
                editNulls = {};
                (@js(old('null_columns', []))).forEach((column) => editNulls[column] = true);
                editModalOpen = true;
            @endif
        "
        class="py-12"
    >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-gray-600">
                    <span><span class="font-medium text-gray-900">{{ __('Connection type') }}:</span> {{ $table['driver'] }}</span>
                    <span><span class="font-medium text-gray-900">{{ __('Storage') }}:</span> {{ $table['database'] }}</span>
                    <span><span class="font-medium text-gray-900">{{ __('Records') }}:</span> {{ number_format((int) $table['row_count']) }}</span>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Field details') }}</h3>
                        <p class="mt-1 text-sm text-gray-600">{{ __('Names, types, defaults, and key hints where available.') }}</p>
                    </div>
                </div>

                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">{{ __('Column') }}</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">{{ __('Type') }}</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">{{ __('Nullable') }}</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">{{ __('Default') }}</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">{{ __('Key') }}</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">{{ __('Extra') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($table['columns'] as $column)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $column['name'] }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $column['type'] }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $column['nullable'] ? __('Yes') : __('No') }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $column['default'] ?? 'NULL' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $column['key'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $column['extra'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                @if (session('status') === 'record-deleted')
                    <p class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {{ __('Record removed successfully.') }}
                    </p>
                @elseif (session('status') === 'record-updated')
                    <p class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {{ __('Record updated successfully.') }}
                    </p>
                @endif

                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Saved records') }}</h3>
                        <p class="mt-1 text-sm text-gray-600">{{ __('A compact look at recent information, with quick edit and removal available for tables that have a primary key.') }}</p>
                    </div>
                    <div class="text-sm text-gray-500">
                        {{ __('Page :page of :pages', ['page' => $table['preview']->currentPage(), 'pages' => max(1, $table['preview']->lastPage())]) }}
                    </div>
                </div>

                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                @foreach (array_keys($table['preview']->items()[0]['values'] ?? []) as $column)
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">{{ $column }}</th>
                                @endforeach
                                @if ($table['primary_key'] !== null)
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">{{ __('Actions') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($table['preview'] as $row)
                                <tr>
                                    @foreach ($row['values'] as $value)
                                        <td class="max-w-xs px-4 py-3 text-gray-600">{{ $value }}</td>
                                    @endforeach
                                    @if ($table['primary_key'] !== null)
                                        <td class="px-4 py-3">
                                            @if ($row['delete_key'] !== null)
                                                <div class="flex flex-wrap items-center gap-2">
                                                    @if (count($editableColumns) > 0)
                                                        <button
                                                            type="button"
                                                            class="rounded-md border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                                                            x-on:click="openEditModal({{ \Illuminate\Support\Js::from($row['delete_key']) }}, {{ \Illuminate\Support\Js::from($row['raw_values']) }})"
                                                        >
                                                            {{ __('Edit') }}
                                                        </button>
                                                    @endif

                                                    <form method="POST" action="{{ route('database.destroy', ['table' => $table['name']]) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="record_key" value="{{ $row['delete_key'] }}">
                                                        <input type="hidden" name="page" value="{{ $table['preview']->currentPage() }}">
                                                        <button
                                                            type="submit"
                                                            class="rounded-md border border-red-200 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50"
                                                            onclick="return confirm('{{ __('Remove this record? This action cannot be undone.') }}')"
                                                        >
                                                            {{ __('Remove') }}
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="text-xs text-gray-400">{{ __('Unavailable') }}</span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count(array_keys($table['preview']->items()[0]['values'] ?? [])) + ($table['primary_key'] !== null ? 1 : 0) }}" class="px-4 py-6 text-center text-gray-500">{{ __('No rows to preview.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($table['preview']->hasPages())
                    <div class="mt-4">
                        {{ $table['preview']->links() }}
                    </div>
                @endif
            </div>
        </div>

        <template x-if="editModalOpen">
            <div class="fixed inset-0 z-50 flex items-center justify-center px-4">
                <div class="absolute inset-0 bg-slate-900/40" x-on:click="editModalOpen = false"></div>
                <div class="relative z-10 w-full max-w-3xl rounded-2xl bg-white p-6 shadow-2xl">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm text-slate-500">{{ __('Edit record') }}</p>
                            <h3 class="mt-1 text-lg font-semibold text-slate-900">{{ $table['name'] }}</h3>
                            @if ($table['primary_key'] !== null)
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $table['primary_key'] }}:
                                    <span class="font-medium text-slate-700" x-text="editRecordKey"></span>
                                </p>
                            @endif
                        </div>
                        <button type="button" class="rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50" x-on:click="editModalOpen = false">
                            {{ __('Close') }}
                        </button>
                    </div>

                    <form method="POST" action="{{ route('database.update', ['table' => $table['name']]) }}" class="mt-6 space-y-5">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="record_key" x-model="editRecordKey">
                        <input type="hidden" name="page" value="{{ $table['preview']->currentPage() }}">

                        <div class="grid gap-4 md:grid-cols-2">
                            @foreach ($editableColumns as $column)
                                <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <label class="text-sm font-medium text-slate-900">{{ $column['name'] }}</label>
                                            <p class="mt-1 text-xs text-slate-500">{{ $column['type'] }}</p>
                                        </div>
                                        @if ($column['nullable'])
                                            <label class="inline-flex items-center gap-2 text-xs text-slate-500">
                                                <input type="checkbox" x-model="editNulls['{{ $column['name'] }}']" class="rounded border-slate-300 text-slate-900 shadow-sm focus:ring-slate-400">
                                                <span>{{ __('Set null') }}</span>
                                            </label>
                                            <input type="hidden" name="null_columns[]" value="{{ $column['name'] }}" x-bind:disabled="!editNulls['{{ $column['name'] }}']">
                                        @endif
                                    </div>

                                    <div class="mt-3">
                                        @if (str_contains(strtolower($column['type']), 'text') || str_contains(strtolower($column['type']), 'json'))
                                            <textarea
                                                name="values[{{ $column['name'] }}]"
                                                rows="4"
                                                class="block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400"
                                                x-model="editFields['{{ $column['name'] }}']"
                                                x-bind:disabled="editNulls['{{ $column['name'] }}']"
                                            ></textarea>
                                        @else
                                            <input
                                                type="text"
                                                name="values[{{ $column['name'] }}]"
                                                class="block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400"
                                                x-model="editFields['{{ $column['name'] }}']"
                                                x-bind:disabled="editNulls['{{ $column['name'] }}']"
                                            >
                                        @endif
                                    </div>

                                    @error('values.'.$column['name'])
                                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endforeach
                        </div>

                        <div class="flex items-center gap-3">
                            <x-primary-button>{{ __('Save changes') }}</x-primary-button>
                            <p class="text-xs text-slate-500">{{ __('Changes update the selected row immediately.') }}</p>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>
</x-app-layout>
