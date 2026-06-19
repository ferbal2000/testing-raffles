<x-layouts.app>
    <section class="space-y-6">
        <div class="space-y-4">
        <h1 class="text-3xl font-semibold">{{ __('home.admin.title') }}</h1>
        <p class="text-slate-600">{{ __('home.admin.description') }}</p>

            @auth('admin')
                <p class="text-sm text-slate-500">{{ auth('admin')->user()->email }}</p>
            @endauth
        </div>

        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf

            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 font-medium text-slate-700 transition hover:bg-slate-100"
            >
                {{ __('admin-auth.logout') }}
            </button>
        </form>
    </section>
</x-layouts.app>
