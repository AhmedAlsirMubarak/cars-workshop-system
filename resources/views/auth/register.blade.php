<x-layouts.guest title="Register">
<div class="min-h-screen flex flex-col justify-center items-center px-6 py-12">
    <div class="w-full max-w-sm">
        <h2 class="text-2xl font-bold text-white mb-1">Create account</h2>
        <p class="text-gray-400 text-sm mb-8">Join Workshop Pro</p>

        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="space-y-5">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-2">Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required autocomplete="name"
                        class="w-full bg-white/5 border @error('name') border-red-500 @else border-white/10 @enderror text-white text-sm rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 transition">
                    @error('name')<p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-2">Email address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                        class="w-full bg-white/5 border @error('email') border-red-500 @else border-white/10 @enderror text-white text-sm rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 transition">
                    @error('email')<p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-2">Password</label>
                    <input type="password" name="password" required autocomplete="new-password"
                        class="w-full bg-white/5 border @error('password') border-red-500 @else border-white/10 @enderror text-white text-sm rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 transition">
                    @error('password')<p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-2">Confirm password</label>
                    <input type="password" name="password_confirmation" required autocomplete="new-password"
                        class="w-full bg-white/5 border border-white/10 text-white text-sm rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 transition">
                </div>

                <button type="submit"
                    class="w-full bg-[#FEE103] hover:bg-[#FEE103]/80 text-black font-semibold text-sm py-3 rounded-xl transition-all">
                    Create account
                </button>
            </div>
        </form>

        <p class="text-center text-sm text-gray-400 mt-6">
            Already have an account?
            <a href="{{ route('login') }}" class="text-[#FEE103] hover:text-[#FEE103]/80">Sign in</a>
        </p>
    </div>
</div>
</x-layouts.guest>
