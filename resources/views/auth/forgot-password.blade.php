<x-layouts.guest title="Forgot Password">
<div class="min-h-screen flex flex-col justify-center items-center px-6 py-12">
    <div class="w-full max-w-sm">
        <h2 class="text-2xl font-bold text-white mb-1">Forgot password</h2>
        <p class="text-gray-400 text-sm mb-8">Enter your email and we'll send a reset link.</p>

        @if ($status)
        <div class="mb-5 text-sm text-green-400 bg-green-500/10 border border-green-500/20 px-4 py-3 rounded-xl">
            {{ $status }}
        </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="space-y-5">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-2">Email address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                        class="w-full bg-white/5 border @error('email') border-red-500 @else border-white/10 @enderror text-white text-sm rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 transition">
                    @error('email')<p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                <button type="submit"
                    class="w-full bg-[#FEE103] hover:bg-[#FEE103]/80 text-black font-semibold text-sm py-3 rounded-xl transition-all">
                    Send reset link
                </button>
            </div>
        </form>

        <p class="text-center text-sm text-gray-400 mt-6">
            <a href="{{ route('login') }}" class="text-[#FEE103] hover:text-[#FEE103]/80">Back to sign in</a>
        </p>
    </div>
</div>
</x-layouts.guest>
