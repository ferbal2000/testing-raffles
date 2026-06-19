<x-layouts.app>
    <section class="w-full max-w-md space-y-6 rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
        <header class="space-y-2">
            <h1 class="text-3xl font-semibold">{{ __('home.admin.title') }}</h1>
            <p class="text-slate-600">{{ __('admin-auth.login_description') }}</p>
        </header>

        <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-4">
            @csrf

            <div class="space-y-2">
                <label for="email" class="text-sm font-medium text-slate-700">{{ __('admin-auth.email') }}</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-950 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"
                >
                @error('email')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <label for="password" class="text-sm font-medium text-slate-700">{{ __('admin-auth.password') }}</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-950 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"
                >
            </div>

            <button
                type="submit"
                class="inline-flex w-full items-center justify-center rounded-lg bg-slate-950 px-4 py-2 font-medium text-white transition hover:bg-slate-800"
            >
                {{ __('admin-auth.submit') }}
            </button>
        </form>
    </section>
</x-layouts.app>
