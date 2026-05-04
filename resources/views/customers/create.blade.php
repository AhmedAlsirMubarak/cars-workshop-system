<x-layouts.app title="{{ __('New Customer') }}">

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('customers.index') }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h2 class="text-lg font-semibold text-gray-900">{{ __('New Customer') }}</h2>
    </div>

    <div class="max-w-2xl">
        <div class="card p-6">
            <form method="POST" action="{{ route('customers.store') }}">
                @csrf

                @if($errors->any())
                <div class="mb-5 rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-700 space-y-1">
                    @foreach($errors->all() as $err)
                    <p>{{ $err }}</p>
                    @endforeach
                </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Full Name') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="input w-full @error('name') border-red-400 @enderror"
                            placeholder="{{ __('e.g. Ahmed Al-Busaidi') }}">
                        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Phone') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone') }}" required
                            class="input w-full @error('phone') border-red-400 @enderror"
                            placeholder="+968 9X XXX XXX">
                        @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Alternate Phone') }}</label>
                        <input type="text" name="phone_alt" value="{{ old('phone_alt') }}"
                            class="input w-full"
                            placeholder="+968 9X XXX XXX">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email') }}</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="input w-full @error('email') border-red-400 @enderror"
                            placeholder="customer@email.com">
                        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('City') }}</label>
                        <input type="text" name="city" value="{{ old('city') }}"
                            class="input w-full" placeholder="{{ __('e.g. Muscat') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Address') }}</label>
                        <input type="text" name="address" value="{{ old('address') }}"
                            class="input w-full" placeholder="{{ __('Street / Building') }}">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Notes') }}</label>
                        <textarea name="notes" rows="3" class="input w-full"
                            placeholder="{{ __('Any additional notes…') }}">{{ old('notes') }}</textarea>
                    </div>

                </div>

                <div class="mt-6 flex items-center gap-3">
                    <button type="submit" class="btn-primary">{{ __('Save Customer') }}</button>
                    <a href="{{ route('customers.index') }}" class="btn-secondary">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>

</x-layouts.app>
