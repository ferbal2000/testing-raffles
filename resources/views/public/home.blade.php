<x-layouts.app>
    <section class="space-y-6">
        <div class="space-y-2">
            <p class="text-sm font-medium uppercase tracking-wide text-slate-500">{{ __('home.public.catalog_label') }}</p>
            <h1 class="text-3xl font-semibold text-slate-950">{{ __('home.public.title') }}</h1>
            <p class="text-slate-600">{{ __('home.public.description') }}</p>
            <p class="text-sm text-slate-500">{{ __('home.public.ordering_note') }}</p>
        </div>

        @forelse ($raffles as $raffle)
            <article class="space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="space-y-1">
                    <h2 class="text-xl font-semibold text-slate-950">{{ __('home.public.raffle_label') }} #{{ $raffle->id }}</h2>
                    <p class="text-sm text-slate-500">{{ $raffle->status_message }}</p>
                </div>

                <dl class="grid gap-4 text-sm text-slate-700 sm:grid-cols-2">
                    <div class="space-y-1">
                        <dt class="font-medium text-slate-500">{{ __('public-raffles.status_label') }}</dt>
                        <dd class="text-base text-slate-950">{{ $raffle->status_message }}</dd>
                    </div>

                    <div class="space-y-1">
                        <dt class="font-medium text-slate-500">{{ __('public-raffles.availability_label') }}</dt>
                        <dd class="text-base text-slate-950">{{ $raffle->availability_message }}</dd>
                    </div>
                </dl>

                <a class="inline-flex items-center font-medium text-slate-950 underline underline-offset-4" href="{{ route('public.raffles.show', $raffle, false) }}">
                    {{ __('home.public.detail_link') }}
                </a>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-sm text-slate-600">
                <p class="font-medium text-slate-950">{{ __('home.public.empty_title') }}</p>
                <p class="mt-2">{{ __('home.public.empty_description') }}</p>
            </div>
        @endforelse
    </section>
</x-layouts.app>
