<div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h3 class="text-lg font-medium text-gray-900">{{ __('Tables') }}</h3>
            <p class="mt-1 text-sm text-gray-600">
                {{ __('A read-only look at how your saved information is organized.') }}
            </p>
        </div>
        <a
            href="{{ route('database.index') }}"
            class="inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            title="{{ __('Open the full read-only data browser.') }}"
        >
            {{ __('Open Browser') }}
        </a>
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-gray-600">
        <span><span class="font-medium text-gray-900">{{ __('Connection type') }}:</span> {{ $databaseOverview['driver'] }}</span>
        <span><span class="font-medium text-gray-900">{{ __('Storage') }}:</span> {{ $databaseOverview['database'] }}</span>
        <span><span class="font-medium text-gray-900">{{ __('Tables') }}:</span> {{ count($databaseOverview['tables']) }}</span>
    </div>

    <div class="mt-6 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">{{ __('Table') }}</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">{{ __('Records') }}</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">{{ __('Notes') }}</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">{{ __('Open') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($databaseOverview['tables'] as $table)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $table['name'] }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $table['row_count'] !== null ? number_format((int) $table['row_count']) : __('Unknown') }}
                        </td>
                        <td class="px-4 py-3 text-gray-500">
                            @php
                                $metadata = collect($table['metadata'])
                                    ->filter(fn ($value) => $value !== null && $value !== '')
                                    ->map(fn ($value, $key) => Str::headline((string) $key).': '.$value)
                                    ->implode(' | ');
                            @endphp
                            {{ $metadata !== '' ? $metadata : '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('database.show', ['table' => $table['name']]) }}" class="text-sm font-medium text-gray-900 underline-offset-2 hover:underline">
                                {{ __('View') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">{{ __('No tables found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
