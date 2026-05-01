<x-layouts.guest title="Verify Email">
<div class="min-h-screen flex flex-col justify-center items-center px-6 py-12">
    <div class="w-full max-w-sm text-center">
        <div class="w-16 h-16 bg-orange-500/10 rounded-2xl flex items-center justify-center mx-auto mb-6">
            <svg class="w-8 h-8 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-white mb-2">Verify your email</h2>
        <p class="text-gray-400 text-sm mb-6">
            Thanks for signing up! Please verify your email address by clicking the link we sent you.
        </p>

        @if ($status === 'verification-link-sent')
        <div class="mb-5 text-sm text-green-400 bg-green-500/10 border border-green-500/20 px-4 py-3 rounded-xl">
            A new verification link has been sent.
        </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" class="mb-4">
            @csrf
            <button type="submit"
                class="w-full bg-[#FEE103] hover:bg-[#FEE103]/80 text-black font-semibold text-sm py-3 rounded-xl transition-all">
                Resend verification email
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-gray-500 hover:text-gray-300 transition">
                Log out
            </button>
        </form>
    </div>
</div>
</x-layouts.guest>
