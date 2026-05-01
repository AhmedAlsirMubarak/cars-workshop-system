<x-layouts.guest title="Sign In">
<div class="min-h-screen flex flex-col lg:flex-row">

    {{-- Branding panel (desktop) --}}
    <div class="hidden lg:flex lg:w-1/2 xl:w-3/5 relative flex-col justify-between p-12 overflow-hidden">
    
        <div class="">
            <img src=" {{ asset('images/ts-w-logo.png') }}" class="h-20 max-w-full" alt="Tsunami Garage">
        </div>
        <div class="relative z-10">
            <h1 class="text-5xl xl:text-6xl font-black text-white leading-tight mb-6">
                Run your<br><span class="text-[#FEE103]">workshop</span><br>smarter.
            </h1>
            <p class="text-gray-400 text-lg max-w-sm leading-relaxed">
                Full management for job orders, inventory, customers, invoicing and your team — all in one place.
            </p>
            <div class="flex flex-wrap gap-2 mt-8">
                @foreach(['Job Orders','Inventory','Invoices','Appointments','Customers','Payroll'] as $f)
                <span class="text-xs bg-white/5 border border-white/10 text-gray-300 px-3 py-1.5 rounded-full">{{ $f }}</span>
                @endforeach
            </div>
        </div>
        <div class="relative z-10 flex gap-8">
            <div><p class="text-2xl font-bold text-white">8</p><p class="text-xs text-gray-500 mt-0.5">Modules</p></div>
            <div><p class="text-2xl font-bold text-white">3</p><p class="text-xs text-gray-500 mt-0.5">Role levels</p></div>
            <div><p class="text-2xl font-bold text-white">EN/AR</p><p class="text-xs text-gray-500 mt-0.5">Bilingual</p></div>
        </div>
    </div>

    {{-- Login form --}}
    <div class="flex-1 flex flex-col justify-center px-6 py-12 sm:px-12 lg:px-16 xl:px-24">
        <div class="flex items-center gap-3 mb-10 lg:hidden">
            <img src=" {{ asset('images/ts-w-logo.png') }}" class="h-20 w-auto" alt="Tsunami Garage">
        </div>

        <div class="w-full max-w-sm mx-auto lg:mx-0">
            <h2 class="text-2xl font-bold text-white mb-1">Welcome back</h2>
            <p class="text-gray-400 text-sm mb-8">Sign in to your workshop dashboard</p>

            @if(session('status'))
            <div class="mb-5 text-sm text-green-400 bg-green-500/10 border border-green-500/20 px-4 py-3 rounded-xl">
                {{ session('status') }}
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" x-data="{ show: false, loading: false }" @submit="loading=true">
                @csrf
                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-2">Email address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                            class="w-full bg-white/5 border @error('email') border-red-500 @else border-white/10 @enderror text-white text-sm rounded-xl px-4 py-3 placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-[#FEE103] focus:border-transparent transition"
                            placeholder="you@workshop.com">
                        @error('email')<p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-medium text-gray-400">Password</label>
                            @if(Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs text-[#FEE103] hover:text-[#FEE103]/80 transition">
                                Forgot password?
                            </a>
                            @endif
                        </div>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password"
                                class="w-full bg-white/5 border @error('password') border-red-500 @else border-white/10 @enderror text-white text-sm rounded-xl px-4 py-3 pr-11 placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-[#FEE103] focus:border-transparent transition"
                                placeholder="••••••••">
                            <button type="button" @click="show=!show" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition p-1">
                                <svg x-show="!show" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="show" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                        @error('password')<p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-white/20 bg-white/5 text-[#FEE103] focus:ring-[#FEE103]">
                        <span class="text-sm text-gray-400 select-none">Remember me for 30 days</span>
                    </label>

                    <button type="submit" :disabled="loading"
                        class="w-full bg-[#FEE103] hover:bg-[#FEE103]/80 disabled:opacity-60 text-black font-semibold text-sm py-3 rounded-xl transition-all shadow-lg shadow-yellow-500/20 flex items-center justify-center gap-2">
                        <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        <span x-text="loading ? 'Signing in…' : 'Sign in →'"></span>
                    </button>
                </div>
            </form>
            <p class="text-center text-xs text-gray-600 mt-8">Tsunami Garage © {{ date('Y') }}</p>
        </div>
    </div>
</div>
</x-layouts.guest>
