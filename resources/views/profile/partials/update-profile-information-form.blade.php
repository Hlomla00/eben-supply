<section>
    <header>
        <h2 class="font-heading font-bold text-sm text-[#A3A380] uppercase tracking-wider">Profile Information</h2>
        <p class="mt-1 text-sm text-stone-400">Update your name, email address and phone number.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <div>
            <label class="form-label" for="name">Full Name</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autocomplete="name"
                   class="form-input @error('name') ring-2 ring-red-300 @enderror">
            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label" for="email">Email Address</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username"
                   class="form-input @error('email') ring-2 ring-red-300 @enderror">
            @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <p class="text-sm mt-2 text-stone-500">
                    Your email address is unverified.
                    <button form="send-verification" class="underline text-[#A3A380] hover:text-[#333333]">Re-send the verification email.</button>
                </p>
                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 font-medium text-sm text-emerald-600">A new verification link has been sent to your email address.</p>
                @endif
            @endif
        </div>

        <div>
            <label class="form-label" for="phone">Phone Number</label>
            <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" autocomplete="tel"
                   class="form-input @error('phone') ring-2 ring-red-300 @enderror" placeholder="082 000 0000">
            @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-primary text-sm px-8 py-3">Save</button>
            @if (session('status') === 'profile-updated')
                <p class="text-sm text-emerald-600 font-medium">Saved.</p>
            @endif
        </div>
    </form>
</section>
