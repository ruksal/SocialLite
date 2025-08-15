<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" wire:submit="login" class="flex flex-col gap-6">
        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Email address')"
            type="email"
            required
            autofocus
            autocomplete="email"
            placeholder="email@example.com"
        />

        <!-- Password -->
        <div class="relative">
            <flux:input
                wire:model="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('Password')"
                viewable
            />

            @if (Route::has('password.request'))
                <flux:link class="absolute end-0 top-0 text-sm" :href="route('password.request')" wire:navigate>
                    {{ __('Forgot your password?') }}
                </flux:link>
            @endif
        </div>

        <!-- Remember Me -->
        <flux:checkbox wire:model="remember" :label="__('Remember me')" />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full">{{ __('Log in') }}</flux:button>
        </div>
    </form>

     <!-- Divider -->
    <div class="flex items-center my-4">
        <hr class="flex-grow border-gray-300 dark:border-gray-600">
        <span class="px-2 text-gray-500 dark:text-gray-400">or</span>
        <hr class="flex-grow border-gray-300 dark:border-gray-600">
    </div>

    <!-- Login with Google -->
    <a href="{{ route('google-auth') }}"
       class="flex items-center justify-center gap-2 w-full px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
        <img src="https://www.svgrepo.com/show/355037/google.svg" alt="Google" class="w-5 h-5">
        {{ __('Log in with Google') }}
    </a>

    @if (Route::has('register'))
        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Don\'t have an account?') }}</span>
            <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
        </div>
    @endif
</div>
