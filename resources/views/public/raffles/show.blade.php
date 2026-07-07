<x-layouts.app>
    <section class="space-y-6">
        <div class="space-y-2">
            <p class="text-sm font-medium uppercase tracking-wide text-slate-500">{{ __('public-raffles.title') }}</p>
            <h1 class="text-3xl font-semibold text-slate-950">{{ $statusMessage }}</h1>
        </div>

        @if (session('public.raffles.participation_success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                {{ __('public-raffles.participation.success') }}
            </div>
        @endif

        @if (session('public.raffles.participation_duplicate'))
            <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800" role="status">
                {{ __('public-raffles.participation.duplicate') }}
            </div>
        @endif

        @if (session('public.raffles.participation_unavailable'))
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800" role="status">
                {{ __('public-raffles.participation.unavailable') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                {{ __('public-raffles.participation.errors') }}
            </div>
        @endif

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

        @if ($raffle->canAcceptParticipants())
            <section class="space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="space-y-1">
                    <h2 class="text-xl font-semibold text-slate-950">{{ __('public-raffles.participation.title') }}</h2>
                    <p class="text-sm text-slate-600">{{ __('public-raffles.participation.description') }}</p>
                    <p class="text-sm font-medium text-slate-700">
                        {{ trans_choice('public-raffles.participation.registration_count', $raffle->registrations_count, ['count' => $raffle->registrations_count]) }}
                    </p>
                </div>

                <form method="POST" action="{{ route('public.raffles.participation.store', $raffle) }}" class="grid gap-4 sm:grid-cols-2">
                    @csrf

                    <div class="space-y-2">
                        <label for="name" class="text-sm font-medium text-slate-700">{{ __('public-raffles.participation.name_label') }}</label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-slate-950 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"
                            autocomplete="name"
                        >

                        @error('name')
                            <p class="text-sm text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="email" class="text-sm font-medium text-slate-700">{{ __('public-raffles.participation.email_label') }}</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-slate-950 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"
                            autocomplete="email"
                        >

                        @error('email')
                            <p class="text-sm text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-lg bg-slate-950 px-4 py-2 font-medium text-white transition hover:bg-slate-800"
                        >
                            {{ __('public-raffles.participation.submit') }}
                        </button>
                    </div>
                </form>
            </section>
        @else
            <section class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-sm text-slate-600">
                <p class="font-medium text-slate-950">{{ __('public-raffles.participation.closed') }}</p>
            </section>
        @endif
    </section>
</x-layouts.app>
