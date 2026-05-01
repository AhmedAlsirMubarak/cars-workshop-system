<x-layouts.guest title="Reset Password">
<div class="min-h-screen flex flex-col justify-center items-center px-6 py-12">
    <div class="w-full max-w-sm">
        <h2 class="text-2xl font-bold text-white mb-1">Reset password</h2>
        <p class="text-gray-400 text-sm mb-8">Enter your new password below.</p>

        <form method="POST" action="{{ route('password.store') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="space-y-5">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-2">Email address</label>
                    <input type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email"
                        class="w-full bg-white/5 border @error('email') border-red-500 @else border-white/10 @enderror text-white text-sm rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#FEE103] transition">
                    @error('email')<p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-2">New password</label>
                    <input type="password" name="password" required autocomplete="new-password"
                        class="w-full bg-white/5 border @error('password') border-red-500 @else border-white/10 @enderror text-white text-sm rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#FEE103] transition">
                    @error('password')<p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-2">Confirm new password</label>
                    <input type="password" name="password_confirmation" required autocomplete="new-password"
                        class="w-full bg-white/5 border border-white/10 text-white text-sm rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#FEE103] transition">
                </div>

                <button type="submit"
                    class="w-full bg-[#FEE103] hover:bg-[#FEE103]/80 text-black font-semibold text-sm py-3 rounded-xl transition-all">
                    Reset password
                </button>
            </div>
        </form>
    </div>
</div>
</x-layouts.guest>
