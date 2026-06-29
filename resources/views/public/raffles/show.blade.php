<x-layouts.app>
    <section class="space-y-6">
        <div class="space-y-2">
            <p class="text-sm font-medium uppercase tracking-wide text-slate-500">{{ __('public-raffles.title') }}</p>
            <h1 class="text-3xl font-semibold text-slate-950">{{ $statusMessage }}</h1>
        </div>

        <dl class="grid gap-4 text-sm text-slate-700 sm:grid-cols-2">
            <div class="space-y-1 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <dt class="font-medium text-slate-500">{{ __('public-raffles.status_label') }}</dt>
                <dd class="text-base text-slate-950">{{ $statusMessage }}</dd>
            </div>

            <div class="space-y-1 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <dt class="font-medium text-slate-500">{{ __('public-raffles.availability_label') }}</dt>
                <dd class="text-base text-slate-950">{{ $availabilityMessage }}</dd>
            </div>

            <div class="space-y-1 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <dt class="font-medium text-slate-500">{{ __('public-raffles.starts_at_label') }}</dt>
                <dd class="text-base text-slate-950">{{ $raffle->starts_at?->format('d/m/Y H:i') ?? __('public-raffles.empty_date') }}</dd>
            </div>

            <div class="space-y-1 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <dt class="font-medium text-slate-500">{{ __('public-raffles.ends_at_label') }}</dt>
                <dd class="text-base text-slate-950">{{ $raffle->ends_at?->format('d/m/Y H:i') ?? __('public-raffles.empty_date') }}</dd>
            </div>
        </dl>
    </section>
</x-layouts.app>
