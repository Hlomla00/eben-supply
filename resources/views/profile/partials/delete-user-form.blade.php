<section class="space-y-6">
    <header>
        <h2 class="font-heading font-bold text-sm text-[#A3A380] uppercase tracking-wider">Delete Account</h2>
        <p class="mt-1 text-sm text-stone-400">
            Once your account is deleted, your profile and saved cart are permanently removed. Past orders are kept for our records.
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Delete Account</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="font-heading font-bold text-lg text-[#333333]">Are you sure you want to delete your account?</h2>

            <p class="mt-1 text-sm text-stone-500">Enter your password to confirm you want to permanently delete your account.</p>

            <div class="mt-6">
                <label for="password" class="sr-only">Password</label>
                <input id="password" name="password" type="password" placeholder="Password"
                       class="form-input sm:w-3/4 @if($errors->userDeletion->has('password')) ring-2 ring-red-300 @endif">
                @foreach($errors->userDeletion->get('password') as $message)<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@endforeach
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">Cancel</x-secondary-button>
                <x-danger-button>Delete Account</x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
