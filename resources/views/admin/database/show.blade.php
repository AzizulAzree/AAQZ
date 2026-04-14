<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Database Inspector') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">{{ $table['name'] }}</p>
            </div>
            <a
                href="{{ route('database.index') }}"
                class="inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
                {{ __('Back to overview') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-gray-600">
                    <span><span class="font-medium text-gray-900">{{ __('Driver') }}:</span> {{ $table['driver'] }}</span>
                    <span><span class="font-medium text-gray-900">{{ __('Database') }}:</span> {{ $table['database'] }}</span>
                    <span><span class="font-medium text-gray-900">{{ __('Rows') }}:</span> {{ number_format((int) $table['row_count']) }}</span>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Columns') }}</h3>
                        <p class="mt-1 text-sm text-gray-600">{{ __('Structure, nullability, defaults, and key hints where available.') }}</p>
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
                                    <td class="px-4 py-3 text-gray-600">{{ $column['key'] ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $column['extra'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Latest Rows') }}</h3>
                        <p class="mt-1 text-sm text-gray-600">{{ __('A compact, read-only preview of recent rows.') }}</p>
                    </div>
                    <div class="text-sm text-gray-500">
                        {{ __('Page :page of :pages', ['page' => $table['preview']->currentPage(), 'pages' => max(1, $table['preview']->lastPage())]) }}
                    </div>
                </div>

                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                @foreach (array_keys($table['preview']->items()[0] ?? []) as $column)
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">{{ $column }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($table['preview'] as $row)
                                <tr>
                                    @foreach ($row as $value)
                                        <td class="max-w-xs px-4 py-3 text-gray-600">{{ $value }}</td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-4 py-6 text-center text-gray-500">{{ __('No rows to preview.') }}</td>
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
