<x-layouts.app title="Profile">

<div class="max-w-2xl space-y-6">

    {{-- Profile information --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-base font-semibold text-gray-900 mb-4">Profile Information</h2>

        @if ($mustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
        <div class="mb-4 text-sm text-amber-700 bg-amber-50 border border-amber-200 px-4 py-3 rounded-xl">
            Your email address is unverified.
            <form method="POST" action="{{ route('verification.send') }}" class="inline">
                @csrf
                <button type="submit" class="underline hover:no-underline">Resend verification email.</button>
            </form>
            @if ($status === 'verification-link-sent')
            <span class="block text-green-600 mt-1">A new verification link has been sent.</span>
            @endif
        </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Name</label>
                <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required
                    class="w-full border @error('name') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 transition">
                @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Email</label>
                <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required
                    class="w-full border @error('email') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 transition">
                @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-3 pt-1">
                <button type="submit" class="btn-primary">Save changes</button>
                @if (session('status') === 'profile-updated')
                <span class="text-sm text-green-600">Saved.</span>
                @endif
            </div>
        </form>
    </div>

    {{-- Update password --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-base font-semibold text-gray-900 mb-4">Update Password</h2>
        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Current password</label>
                <input type="password" name="current_password" autocomplete="current-password"
                    class="w-full border @error('current_password', 'updatePassword') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 transition">
                @error('current_password', 'updatePassword')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">New password</label>
                <input type="password" name="password" autocomplete="new-password"
                    class="w-full border @error('password', 'updatePassword') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 transition">
                @error('password', 'updatePassword')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Confirm password</label>
                <input type="password" name="password_confirmation" autocomplete="new-password"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 transition">
            </div>

            <button type="submit" class="btn-primary">Update password</button>
        </form>
    </div>

    {{-- Delete account --}}
    <div class="bg-white rounded-2xl shadow-sm border border-red-100 p-6" x-data="modal()">
        <h2 class="text-base font-semibold text-gray-900 mb-1">Delete Account</h2>
        <p class="text-sm text-gray-500 mb-4">Permanently delete your account and all associated data.</p>
        <button @click="show()" class="btn-danger">Delete account</button>

        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6" @click.stop>
                <h3 class="font-semibold text-gray-900 mb-2">Are you sure?</h3>
                <p class="text-sm text-gray-500 mb-5">This cannot be undone. Please enter your password to confirm.</p>
                <form method="POST" action="{{ route('profile.destroy') }}" class="space-y-4">
                    @csrf
                    @method('DELETE')
                    <div>
                        <input type="password" name="password" placeholder="Password" required autocomplete="current-password"
                            class="w-full border @error('password', 'userDeletion') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 transition">
                        @error('password', 'userDeletion')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="btn-danger flex-1">Delete account</button>
                        <button type="button" @click="hide()" class="btn-secondary flex-1">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

</x-layouts.app>
