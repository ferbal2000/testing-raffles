<x-layouts.app>
    <section class="w-full self-start space-y-6 rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
        <header class="space-y-2">
            <h1 class="text-3xl font-semibold">{{ __('admin-raffles.index.title') }}</h1>
            <p class="text-slate-600">{{ __('admin-raffles.index.description') }}</p>
        </header>

        <div class="flex items-center justify-between gap-4">
            <a
                href="{{ route('admin.raffles.create') }}"
                class="inline-flex items-center justify-center rounded-lg bg-slate-950 px-4 py-2 font-medium text-white transition hover:bg-slate-800"
            >
                {{ __('admin-raffles.index.actions.create') }}
            </a>
        </div>

        @if (session('admin.raffles.create_success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('admin.raffles.create_success') }}
            </div>
        @endif

        @if (session('admin.raffles.update_success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('admin.raffles.update_success') }}
            </div>
        @endif

        @if (session('admin.raffles.publish_success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('admin.raffles.publish_success') }}
            </div>
        @endif

        @if (session('admin.raffles.participation_open_success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('admin.raffles.participation_open_success') }}
            </div>
        @endif

        @if (session('admin.raffles.participation_close_success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('admin.raffles.participation_close_success') }}
            </div>
        @endif

        @if ($errors->has('participation'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                {{ $errors->first('participation') }}
            </div>
        @endif

        @if ($errors->has('publish'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                {{ $errors->first('publish') }}
            </div>
        @endif

        @if ($raffles->isEmpty())
            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6">
                <p class="text-lg font-medium text-slate-900">{{ __('admin-raffles.index.empty.title') }}</p>
                <p class="mt-2 text-sm text-slate-600">{{ __('admin-raffles.index.empty.description') }}</p>
            </div>
        @else
            <div class="-mx-2 overflow-x-auto sm:mx-0">
                <table class="min-w-[40rem] divide-y divide-slate-200 text-left text-sm text-slate-700 sm:min-w-full">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-medium">{{ __('admin-raffles.index.columns.id') }}</th>
                            <th scope="col" class="px-4 py-3 font-medium">{{ __('admin-raffles.index.columns.status') }}</th>
                            <th scope="col" class="px-4 py-3 font-medium">{{ __('admin-raffles.index.columns.starts_at') }}</th>
                            <th scope="col" class="px-4 py-3 font-medium">{{ __('admin-raffles.index.columns.ends_at') }}</th>
                            <th scope="col" class="px-4 py-3 font-medium">{{ __('admin-raffles.index.columns.created_at') }}</th>
                            <th scope="col" class="px-4 py-3 font-medium">{{ __('admin-raffles.index.columns.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($raffles as $raffle)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 font-medium text-slate-950">{{ $raffle->id }}</td>
                                <td class="whitespace-nowrap px-4 py-3">{{ $raffle->status->value }}</td>
                                <td class="whitespace-nowrap px-4 py-3">{{ $raffle->starts_at?->format('Y-m-d H:i') ?? __('admin-raffles.index.placeholder') }}</td>
                                <td class="whitespace-nowrap px-4 py-3">{{ $raffle->ends_at?->format('Y-m-d H:i') ?? __('admin-raffles.index.placeholder') }}</td>
                                <td class="whitespace-nowrap px-4 py-3">{{ $raffle->created_at?->format('Y-m-d H:i') ?? __('admin-raffles.index.placeholder') }}</td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a
                                            href="{{ route('admin.raffles.edit', $raffle) }}"
                                            class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-3 py-1.5 font-medium text-slate-700 transition hover:bg-slate-100"
                                        >
                                            {{ __('admin-raffles.index.actions.edit') }}
                                        </a>

                                        <a
                                            href="{{ route('admin.raffles.registrations.index', $raffle) }}"
                                            class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-3 py-1.5 font-medium text-slate-700 transition hover:bg-slate-100"
                                        >
                                            {{ __('admin-raffles.index.actions.registrations') }}
                                        </a>

                                        <span class="text-xs text-slate-500">
                                            {{ trans_choice('admin-raffles.index.registration_count', $raffle->registrations_count, ['count' => $raffle->registrations_count]) }}
                                        </span>

                                        @if ($raffle->canPublish())
                                            <form method="POST" action="{{ route('admin.raffles.publish', $raffle) }}" onsubmit="return confirm(@js(__('admin-raffles.index.actions.publish_confirm')))">
                                                @csrf

                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center justify-center rounded-lg border border-sky-300 px-3 py-1.5 font-medium text-sky-700 transition hover:bg-sky-50"
                                                >
                                                    {{ __('admin-raffles.index.actions.publish') }}
                                                </button>
                                            </form>
                                        @endif

                                        @if ($raffle->canOpenParticipation())
                                            <form method="POST" action="{{ route('admin.raffles.participation.open', $raffle) }}">
                                                @csrf

                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center justify-center rounded-lg border border-emerald-300 px-3 py-1.5 font-medium text-emerald-700 transition hover:bg-emerald-50"
                                                >
                                                    {{ __('admin-raffles.index.actions.open_participation') }}
                                                </button>
                                            </form>
                                        @endif

                                        @if ($raffle->canCloseParticipation())
                                            <form method="POST" action="{{ route('admin.raffles.participation.close', $raffle) }}">
                                                @csrf

                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center justify-center rounded-lg border border-amber-300 px-3 py-1.5 font-medium text-amber-700 transition hover:bg-amber-50"
                                                >
                                                    {{ __('admin-raffles.index.actions.close_participation') }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-layouts.app>
