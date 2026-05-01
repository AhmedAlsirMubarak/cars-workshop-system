<x-layouts.app title="{{ $customer->name }}">

    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('customers.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h2 class="text-lg font-semibold text-gray-900">{{ $customer->name }}</h2>
                <p class="text-sm text-gray-400 mt-0.5">Customer since {{ $customer->created_at->format('d M Y') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('vehicles.create', ['customer' => $customer->id]) }}" class="btn-secondary btn-sm">+ Add Vehicle</a>
            <a href="{{ route('customers.edit', $customer) }}" class="btn-primary btn-sm">Edit</a>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-6">

        {{-- Left: info + stats --}}
        <div class="space-y-4">

            {{-- Profile card --}}
            <div class="card p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900 text-sm">Customer Info</h3>
                    @include('components.status-badge', ['status' => $customer->status])
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex gap-2">
                        <span class="text-gray-400 w-20 shrink-0">Phone</span>
                        <span class="font-medium text-gray-900">{{ $customer->phone }}</span>
                    </div>
                    @if($customer->phone_alt)
                    <div class="flex gap-2">
                        <span class="text-gray-400 w-20 shrink-0">Alt Phone</span>
                        <span class="text-gray-700">{{ $customer->phone_alt }}</span>
                    </div>
                    @endif
                    @if($customer->email)
                    <div class="flex gap-2">
                        <span class="text-gray-400 w-20 shrink-0">Email</span>
                        <span class="text-gray-700 truncate">{{ $customer->email }}</span>
                    </div>
                    @endif
                    @if($customer->city)
                    <div class="flex gap-2">
                        <span class="text-gray-400 w-20 shrink-0">City</span>
                        <span class="text-gray-700">{{ $customer->city }}</span>
                    </div>
                    @endif
                    @if($customer->address)
                    <div class="flex gap-2">
                        <span class="text-gray-400 w-20 shrink-0">Address</span>
                        <span class="text-gray-700">{{ $customer->address }}</span>
                    </div>
                    @endif
                </div>
                @if($customer->notes)
                <div class="border-t border-gray-50 pt-3">
                    <p class="text-xs text-gray-400 mb-1">Notes</p>
                    <p class="text-sm text-gray-700">{{ $customer->notes }}</p>
                </div>
                @endif
            </div>

            {{-- Revenue card --}}
            <div class="card p-5">
                <p class="text-xs text-gray-400 font-medium mb-1">Total Revenue</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($total_revenue, 3) }} <span class="text-sm font-normal text-gray-500">OMR</span></p>
                <div class="mt-3 flex gap-4 text-sm">
                    <div>
                        <p class="text-gray-400 text-xs">Vehicles</p>
                        <p class="font-semibold text-gray-900">{{ $customer->vehicles->count() }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Job Orders</p>
                        <p class="font-semibold text-gray-900">{{ $customer->jobOrders->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: vehicles + job orders --}}
        <div class="xl:col-span-2 space-y-4 sm:space-y-6">

            {{-- Vehicles --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-gray-900">Vehicles</h3>
                    <a href="{{ route('vehicles.create', ['customer' => $customer->id]) }}" class="btn-primary btn-sm">+ Add</a>
                </div>
                @if($customer->vehicles->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-gray-400">No vehicles registered yet.</p>
                @else
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Plate #</th><th>Make / Model</th><th>Year</th><th>Color</th><th>Mileage</th><th></th></tr></thead>
                        <tbody>
                            @foreach($customer->vehicles as $v)
                            <tr>
                                <td class="font-mono font-semibold text-orange-500">{{ $v->plate_number }}</td>
                                <td class="font-medium text-gray-900">{{ $v->make }} {{ $v->model }}</td>
                                <td class="text-gray-500">{{ $v->year }}</td>
                                <td class="text-gray-500">{{ $v->color ?? '—' }}</td>
                                <td class="text-gray-500">{{ $v->mileage ? number_format($v->mileage) . ' km' : '—' }}</td>
                                <td><a href="{{ route('vehicles.show', $v) }}" class="text-xs text-orange-500 hover:underline">View →</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            {{-- Job Orders --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-gray-900">Job Orders</h3>
                    <a href="{{ route('jobs.create') }}" class="btn-primary btn-sm">+ New Job</a>
                </div>
                @if($customer->jobOrders->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-gray-400">No job orders yet.</p>
                @else
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Job #</th><th>Vehicle</th><th>Status</th><th>Technician</th><th class="text-end">Total</th><th></th></tr></thead>
                        <tbody>
                            @foreach($customer->jobOrders as $j)
                            <tr>
                                <td class="font-mono text-xs font-semibold text-orange-500">{{ $j->job_number }}</td>
                                <td class="text-gray-500 text-xs">{{ $j->vehicle?->make }} {{ $j->vehicle?->model }}<br><span class="font-mono">{{ $j->vehicle?->plate_number }}</span></td>
                                <td>@include('components.status-badge', ['status' => $j->status])</td>
                                <td class="text-gray-500 text-xs">{{ $j->staff?->user?->name ?? '—' }}</td>
                                <td class="text-end font-semibold text-gray-900 text-xs">{{ number_format($j->total, 3) }} OMR</td>
                                <td><a href="{{ route('jobs.show', $j) }}" class="text-xs text-orange-500 hover:underline">View →</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

        </div>
    </div>

</x-layouts.app>
