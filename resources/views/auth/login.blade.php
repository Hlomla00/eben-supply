<x-layouts.app title="Login">
{{-- Matuma Malapile 222904267 | Group KN3 --}}

<div class="min-h-[70vh] flex items-center justify-center px-4 py-16 bg-[#F5F5F5]">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <h1 class="font-heading font-black text-3xl text-[#333333] mb-2">Welcome Back</h1>
            <p class="text-stone-400 text-sm">Sign in to your Eben Supply account</p>
        </div>

        @if (session('status'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm rounded-lg mb-4">{{ session('status') }}</div>
        @endif

        <div class="bg-white rounded-2xl border border-stone-100 shadow-card p-8">
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                           class="form-input @error('email') ring-2 ring-red-300 @enderror"
                           placeholder="you@email.com">
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Password</label>
                    <input type="password" name="password" required autocomplete="current-password"
                           class="form-input @error('password') ring-2 ring-red-300 @enderror"
                           placeholder="••••••••">
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 accent-[#333333] rounded">
                        <span class="text-sm text-stone-500 font-medium">Remember me</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm text-[#A3A380] hover:text-[#333333] transition-colors font-medium">
                            Forgot password?
                        </a>
                    @endif
                </div>

                <button type="submit" class="btn-primary w-full py-3 text-sm">
                    Sign In
                </button>

                <p class="text-center text-sm text-stone-400">
                    Don't have an account?
                    <a href="{{ route('register') }}" class="text-[#333333] hover:text-[#A3A380] transition-colors font-semibold">Register</a>
                </p>
            </form>
        </div>
    </div>
</div>
</x-layouts.app>