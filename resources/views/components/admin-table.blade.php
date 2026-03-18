@props([
    'headers' => [],
    'rows' => [],
    'actions' => null,
    'empty' => 'No data available',
    'sortable' => false,
    'sortColumn' => null,
    'sortDirection' => 'asc',
])

<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                @foreach ($headers as $header)
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        @if ($sortable && isset($header['key']))
                            <button class="flex items-center space-x-1 hover:text-gray-700"
                                @click="sort('{{ $header['key'] }}')">
                                <span>{{ $header['label'] }}</span>
                                @if ($sortColumn === $header['key'])
                                    <i class="fa-solid"
                                        :class="$sortDirection === 'asc' ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                @endif
                            </button>
                        @else
                            {{ $header['label'] }}
                        @endif
                    </th>
                @endforeach
                @if ($actions)
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                @endif
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @if (count($rows) > 0)
                @foreach ($rows as $row)
                    <tr class="hover:bg-gray-50">
                        @foreach ($headers as $header)
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if (isset($header['slot']))
                                    {!! $header['slot']($row) !!}
                                @elseif(isset($header['key']))
                                    {{ $row->{$header['key']} ?? '' }}
                                @else
                                    {{ $row->{$header} ?? '' }}
                                @endif
                            </td>
                        @endforeach
                        @if ($actions)
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                {!! $actions($row) !!}
                            </td>
                        @endif
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="{{ count($headers) + ($actions ? 1 : 0) }}" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <i class="fa-solid fa-inbox text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500">{{ $empty }}</p>
                        </div>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
