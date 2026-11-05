<section>
    <header>
        <h2 class="font-heading font-bold text-sm text-[#A3A380] uppercase tracking-wider">Update Password</h2>
        <p class="mt-1 text-sm text-stone-400">Use a long, random password to keep your account secure.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('put')

        <div>
            <label class="form-label" for="update_password_current_password">Current Password</label>
            <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password"
                   class="form-input @if($errors->updatePassword->has('current_password')) ring-2 ring-red-300 @endif">
            @foreach($errors->updatePassword->get('current_password') as $message)<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@endforeach
        </div>

        <div>
            <label class="form-label" for="update_password_password">New Password</label>
            <input id="update_password_password" name="password" type="password" autocomplete="new-password"
                   class="form-input @if($errors->updatePassword->has('password')) ring-2 ring-red-300 @endif">
            @foreach($errors->updatePassword->get('password') as $message)<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@endforeach
        </div>

        <div>
            <label class="form-label" for="update_password_password_confirmation">Confirm Password</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                   class="form-input @if($errors->updatePassword->has('password_confirmation')) ring-2 ring-red-300 @endif">
            @foreach($errors->updatePassword->get('password_confirmation') as $message)<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@endforeach
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-primary text-sm px-8 py-3">Save</button>
            @if (session('status') === 'password-updated')
                <p class="text-sm text-emerald-600 font-medium">Saved.</p>
            @endif
        </div>
    </form>
</section>
