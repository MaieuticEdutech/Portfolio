<div class="w-full max-w-sm">
    <div class="mb-8 text-center">
        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-brand-mint">{{ config('app.name') }}</p>
        <h1 class="mt-3 text-2xl font-bold text-white">Sign in to the studio</h1>
        <p class="mt-2 text-sm text-white/40">Manage clients, logos and films.</p>
    </div>

    <form wire:submit="login" class="space-y-4 rounded-2xl border border-white/10 bg-ink-800 p-6">
        <div>
            <label for="email" class="mb-1.5 block text-sm font-medium text-white/70">Email</label>
            <input
                wire:model="email"
                id="email"
                type="email"
                required
                autofocus
                autocomplete="username"
                class="w-full rounded-lg border border-white/10 bg-ink-700 px-3 py-2 text-sm text-white placeholder-white/25 transition focus:border-brand-mint/60 focus:outline-none"
            >
            @error('email')
                <p class="mt-1.5 text-sm text-brand-coral">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="mb-1.5 block text-sm font-medium text-white/70">Password</label>
            <input
                wire:model="password"
                id="password"
                type="password"
                required
                autocomplete="current-password"
                class="w-full rounded-lg border border-white/10 bg-ink-700 px-3 py-2 text-sm text-white placeholder-white/25 transition focus:border-brand-mint/60 focus:outline-none"
            >
            @error('password')
                <p class="mt-1.5 text-sm text-brand-coral">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-white/50">
            <input wire:model="remember" type="checkbox" class="rounded border-white/20 bg-ink-700 text-brand-teal focus:ring-brand-mint/40">
            Keep me signed in
        </label>

        <button
            type="submit"
            class="w-full rounded-lg bg-brand-teal py-2.5 text-sm font-semibold text-white transition hover:bg-brand-mint hover:text-ink-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-mint disabled:opacity-60"
        >
            <span wire:loading.remove wire:target="login">Sign in</span>
            <span wire:loading wire:target="login">Signing in&hellip;</span>
        </button>
    </form>

    <p class="mt-6 text-center text-xs text-white/25">
        Accounts are created by an administrator. There is no public sign-up.
    </p>
</div>
