<x-app-layout>
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

    <div class="py-12">
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
                @endif

                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Saved records') }}</h3>
                        <p class="mt-1 text-sm text-gray-600">{{ __('A compact look at recent information, with removal available for tables that have a primary key.') }}</p>
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
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">{{ __('Remove') }}</th>
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
    </div>
</x-app-layout>
