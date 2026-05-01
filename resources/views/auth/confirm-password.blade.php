<x-layouts.guest title="Confirm Password">
<div class="min-h-screen flex flex-col justify-center items-center px-6 py-12">
    <div class="w-full max-w-sm">
        <h2 class="text-2xl font-bold text-white mb-1">Confirm password</h2>
        <p class="text-gray-400 text-sm mb-8">
            This is a secure area. Please confirm your password before continuing.
        </p>

        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf
            <div class="space-y-5">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-2">Password</label>
                    <input type="password" name="password" required autocomplete="current-password"
                        class="w-full bg-white/5 border @error('password') border-red-500 @else border-white/10 @enderror text-white text-sm rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 transition">
                    @error('password')<p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                <button type="submit"
                    class="w-full bg-[#FEE103] hover:bg-[#FEE103]/80 text-black font-semibold text-sm py-3 rounded-xl transition-all">
                    Confirm
                </button>
            </div>
        </form>
    </div>
</div>
</x-layouts.guest>
