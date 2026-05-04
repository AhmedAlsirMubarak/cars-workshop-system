<x-layouts.app title="{{ __('Vehicles') }}">

    {{-- Search --}}
    <form method="GET" action="{{ route('vehicles.index') }}" class="flex flex-wrap items-center gap-3 mb-6">
        <div x-data="searchBox()">
            <input type="text" name="search" x-model="query" @input="debounce()"
                placeholder="{{ __('Search plate, make, model or customer…') }}" class="input w-72"
                value="{{ request('search') }}">
        </div>
        <a href="{{ route('vehicles.index') }}" class="text-xs text-gray-400 hover:text-gray-600">{{ __('Clear') }}</a>
    </form>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="hidden sm:block overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Plate #') }}</th>
                        <th>{{ __('Make / Model') }}</th>
                        <th class="text-center">{{ __('Year') }}</th>
                        <th>{{ __('Color') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Mileage') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehicles as $v)
                    <tr class="cursor-pointer" onclick="location='{{ route('vehicles.show', $v) }}'">
                        <td class="font-mono font-semibold text-orange-500">{{ $v->plate_number }}</td>
                        <td>
                            <p class="font-medium text-gray-900">{{ $v->make }} {{ $v->model }}</p>
                            @if($v->vin)
                            <p class="text-xs text-gray-400 font-mono">VIN: {{ $v->vin }}</p>
                            @endif
                        </td>
                        <td class="text-center text-gray-600">{{ $v->year }}</td>
                        <td class="text-gray-500 text-sm">{{ $v->color ?? '—' }}</td>
                        <td>
                            @if($v->customer)
                            <a href="{{ route('customers.show', $v->customer) }}"
                                class="text-sm text-gray-900 hover:text-orange-500 transition" onclick="event.stopPropagation()">
                                {{ $v->customer->name }}
                            </a>
                            @else
                            <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="text-sm text-gray-500">{{ $v->mileage ? number_format($v->mileage) . ' km' : '—' }}</td>
                        <td>
                            <a href="{{ route('vehicles.show', $v) }}" class="text-xs text-orange-500 hover:underline" onclick="event.stopPropagation()">{{ __('View') }} →</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-sm text-gray-400">{{ __('No vehicles found.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile --}}
        <div class="sm:hidden divide-y divide-gray-50">
            @forelse($vehicles as $v)
            <a href="{{ route('vehicles.show', $v) }}" class="flex items-start justify-between gap-3 p-4 hover:bg-gray-50 transition">
                <div>
                    <p class="font-mono font-semibold text-orange-500">{{ $v->plate_number }}</p>
                    <p class="font-medium text-gray-900 mt-0.5">{{ $v->make }} {{ $v->model }} · {{ $v->year }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $v->customer?->name ?? '—' }}</p>
                </div>
                <div class="text-end shrink-0">
                    <p class="text-sm text-gray-500">{{ $v->color ?? '—' }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $v->mileage ? number_format($v->mileage) . ' km' : '' }}</p>
                </div>
            </a>
            @empty
            <p class="py-12 text-center text-sm text-gray-400">{{ __('No vehicles found.') }}</p>
            @endforelse
        </div>
    </div>

    @include('components.pagination', ['paginator' => $vehicles])

</x-layouts.app>
