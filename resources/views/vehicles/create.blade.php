<x-layouts.app title="Add Vehicle">

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('customers.show', $customer) }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h2 class="text-lg font-semibold text-gray-900">Add Vehicle — {{ $customer->name }}</h2>
    </div>

    <div class="max-w-2xl">
        <div class="card p-6">
            <form method="POST" action="{{ route('vehicles.store') }}">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">

                @if($errors->any())
                <div class="mb-5 rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-700 space-y-1">
                    @foreach($errors->all() as $err)
                    <p>{{ $err }}</p>
                    @endforeach
                </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Make <span class="text-red-500">*</span></label>
                        <input type="text" name="make" value="{{ old('make') }}" required
                            class="input w-full @error('make') border-red-400 @enderror"
                            placeholder="e.g. Toyota">
                        @error('make')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Model <span class="text-red-500">*</span></label>
                        <input type="text" name="model" value="{{ old('model') }}" required
                            class="input w-full @error('model') border-red-400 @enderror"
                            placeholder="e.g. Camry">
                        @error('model')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Year <span class="text-red-500">*</span></label>
                        <input type="number" name="year" value="{{ old('year') }}" required
                            min="1900" max="{{ date('Y') + 1 }}"
                            class="input w-full @error('year') border-red-400 @enderror"
                            placeholder="{{ date('Y') }}">
                        @error('year')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                        <input type="text" name="color" value="{{ old('color') }}"
                            class="input w-full" placeholder="e.g. White">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Plate Number <span class="text-red-500">*</span></label>
                        <input type="text" name="plate_number" value="{{ old('plate_number') }}" required
                            class="input w-full @error('plate_number') border-red-400 @enderror"
                            placeholder="e.g. AB 1234">
                        @error('plate_number')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">VIN</label>
                        <input type="text" name="vin" value="{{ old('vin') }}"
                            maxlength="17"
                            class="input w-full font-mono @error('vin') border-red-400 @enderror"
                            placeholder="17-character VIN">
                        @error('vin')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Engine Type</label>
                        <input type="text" name="engine_type" value="{{ old('engine_type') }}"
                            class="input w-full" placeholder="e.g. 2.5L 4-cylinder">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Current Mileage (km)</label>
                        <input type="number" name="mileage" value="{{ old('mileage') }}" min="0"
                            class="input w-full" placeholder="e.g. 45000">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" rows="3" class="input w-full"
                            placeholder="Any notes about the vehicle…">{{ old('notes') }}</textarea>
                    </div>

                </div>

                <div class="mt-6 flex items-center gap-3">
                    <button type="submit" class="btn-primary">Save Vehicle</button>
                    <a href="{{ route('customers.show', $customer) }}" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</x-layouts.app>
