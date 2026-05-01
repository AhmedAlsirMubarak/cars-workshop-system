<x-layouts.app title="{{ $vehicle->plate_number }}">

    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('vehicles.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h2 class="text-lg font-semibold text-gray-900 font-mono">{{ $vehicle->plate_number }}</h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ $vehicle->make }} {{ $vehicle->model }} · {{ $vehicle->year }}</p>
            </div>
        </div>
        <a href="{{ route('jobs.create', ['vehicle_id' => $vehicle->id]) }}" class="btn-primary btn-sm">+ New Job Order</a>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-6">

        {{-- Left: vehicle info + edit form --}}
        <div class="space-y-4">

            {{-- Vehicle Info --}}
            <div class="card p-5 space-y-3">
                <h3 class="font-semibold text-gray-900 text-sm">Vehicle Details</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex gap-2">
                        <span class="text-gray-400 w-24 shrink-0">Make</span>
                        <span class="font-medium text-gray-900">{{ $vehicle->make }}</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="text-gray-400 w-24 shrink-0">Model</span>
                        <span class="font-medium text-gray-900">{{ $vehicle->model }}</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="text-gray-400 w-24 shrink-0">Year</span>
                        <span class="text-gray-700">{{ $vehicle->year }}</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="text-gray-400 w-24 shrink-0">Color</span>
                        <span class="text-gray-700">{{ $vehicle->color ?? '—' }}</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="text-gray-400 w-24 shrink-0">Engine</span>
                        <span class="text-gray-700">{{ $vehicle->engine_type ?? '—' }}</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="text-gray-400 w-24 shrink-0">VIN</span>
                        <span class="text-gray-700 font-mono text-xs">{{ $vehicle->vin ?? '—' }}</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="text-gray-400 w-24 shrink-0">Mileage</span>
                        <span class="text-gray-700">{{ $vehicle->mileage ? number_format($vehicle->mileage) . ' km' : '—' }}</span>
                    </div>
                </div>
                @if($vehicle->notes)
                <div class="border-t border-gray-50 pt-3">
                    <p class="text-xs text-gray-400 mb-1">Notes</p>
                    <p class="text-sm text-gray-700">{{ $vehicle->notes }}</p>
                </div>
                @endif

                {{-- Owner --}}
                @if($vehicle->customer)
                <div class="border-t border-gray-50 pt-3">
                    <p class="text-xs text-gray-400 mb-1">Owner</p>
                    <a href="{{ route('customers.show', $vehicle->customer) }}"
                        class="font-medium text-orange-500 hover:underline text-sm">
                        {{ $vehicle->customer->name }}
                    </a>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $vehicle->customer->phone }}</p>
                </div>
                @endif
            </div>

            {{-- Edit Form --}}
            <div class="card p-5" x-data="{ editOpen: false }">
                <button @click="editOpen = !editOpen"
                    class="flex items-center justify-between w-full text-sm font-semibold text-gray-900">
                    <span>Edit Vehicle</span>
                    <svg :class="editOpen ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="editOpen" x-cloak x-transition class="mt-4">
                    <form method="POST" action="{{ route('vehicles.update', $vehicle) }}" class="space-y-3">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Make <span class="text-red-500">*</span></label>
                                <input type="text" name="make" value="{{ old('make', $vehicle->make) }}" required class="input w-full text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Model <span class="text-red-500">*</span></label>
                                <input type="text" name="model" value="{{ old('model', $vehicle->model) }}" required class="input w-full text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Year <span class="text-red-500">*</span></label>
                                <input type="number" name="year" value="{{ old('year', $vehicle->year) }}" required
                                    min="1900" max="{{ date('Y') + 1 }}" class="input w-full text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Color</label>
                                <input type="text" name="color" value="{{ old('color', $vehicle->color) }}" class="input w-full text-sm">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Plate # <span class="text-red-500">*</span></label>
                                <input type="text" name="plate_number" value="{{ old('plate_number', $vehicle->plate_number) }}" required class="input w-full text-sm">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">VIN</label>
                                <input type="text" name="vin" value="{{ old('vin', $vehicle->vin) }}" maxlength="17" class="input w-full text-sm font-mono">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Mileage (km)</label>
                                <input type="number" name="mileage" value="{{ old('mileage', $vehicle->mileage) }}" min="0" class="input w-full text-sm">
                            </div>
                        </div>

                        <button type="submit" class="btn-primary w-full">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Right: jobs + appointments --}}
        <div class="xl:col-span-2 space-y-4 sm:space-y-6">

            {{-- Job Orders --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-gray-900">Job Orders</h3>
                    <a href="{{ route('jobs.create', ['vehicle_id' => $vehicle->id]) }}" class="btn-primary btn-sm">+ New</a>
                </div>
                @if($vehicle->jobOrders->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-gray-400">No job orders for this vehicle.</p>
                @else
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Job #</th><th>Status</th><th>Technician</th><th>Date</th><th class="text-end">Total</th></tr></thead>
                        <tbody>
                            @foreach($vehicle->jobOrders as $j)
                            <tr class="cursor-pointer" onclick="location='{{ route('jobs.show', $j) }}'">
                                <td class="font-mono text-xs font-semibold text-orange-500">{{ $j->job_number }}</td>
                                <td>@include('components.status-badge', ['status' => $j->status])</td>
                                <td class="text-gray-500 text-sm">{{ $j->staff?->user?->name ?? '—' }}</td>
                                <td class="text-gray-500 text-xs">{{ $j->created_at->format('d M Y') }}</td>
                                <td class="text-end font-semibold text-gray-900 text-xs">{{ number_format($j->total, 3) }} OMR</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            {{-- Appointments --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-gray-900">Appointments</h3>
                    <a href="{{ route('appointments.index') }}" class="text-xs text-orange-500 hover:underline">View all</a>
                </div>
                @if($vehicle->appointments->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-gray-400">No appointments for this vehicle.</p>
                @else
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Date / Time</th><th>Type</th><th>Technician</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($vehicle->appointments as $apt)
                            <tr>
                                <td>
                                    <p class="text-sm text-gray-900">{{ $apt->scheduled_at->format('d M Y') }}</p>
                                    <p class="text-xs text-orange-500 font-semibold">{{ $apt->scheduled_at->format('H:i') }}</p>
                                </td>
                                <td class="capitalize text-gray-600 text-sm">{{ $apt->type ?? '—' }}</td>
                                <td class="text-gray-500 text-sm">{{ $apt->staff?->user?->name ?? '—' }}</td>
                                <td>@include('components.status-badge', ['status' => $apt->status])</td>
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
