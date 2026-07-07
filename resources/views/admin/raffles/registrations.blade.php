<x-layouts.app>
    <section class="w-full max-w-3xl self-start space-y-6 rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <header class="space-y-2">
                <h1 class="text-3xl font-semibold">{{ __('admin-raffles.registrations.title', ['id' => $raffle->id]) }}</h1>
                <p class="text-slate-600">{{ __('admin-raffles.registrations.description') }}</p>
            </header>

            <a
                href="{{ route('admin.raffles.index') }}"
                class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-3 py-1.5 font-medium text-slate-700 transition hover:bg-slate-100"
            >
                {{ __('admin-raffles.registrations.actions.back_to_index') }}
            </a>
        </div>

        @if (session('admin.raffles.registration_status_flag_success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-900">
                {{ session('admin.raffles.registration_status_flag_success') }}
            </div>
        @endif

        @if (session('admin.raffles.registration_status_cancel_success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-900">
                {{ session('admin.raffles.registration_status_cancel_success') }}
            </div>
        @endif

        @error('registration_status')
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-900">
                {{ $message }}
            </div>
        @enderror

        <section class="rounded-xl border border-slate-200 bg-slate-50 p-4" aria-labelledby="registration-summary-title">
            <h2 id="registration-summary-title" class="text-sm font-medium text-slate-600">{{ __('admin-raffles.registrations.summary_title') }}</h2>
            <dl class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['active', $raffle->active_registrations_count],
                    ['flagged', $raffle->flagged_registrations_count],
                    ['cancelled', $raffle->cancelled_registrations_count],
                    ['total', $raffle->registrations_count],
                ] as [$summaryKey, $summaryCount])
                    <div class="rounded-lg bg-white p-3 ring-1 ring-slate-200">
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __("admin-raffles.registrations.summary.{$summaryKey}_label") }}</dt>
                        <dd class="mt-1 text-lg font-semibold text-slate-950">
                            {{ trans_choice("admin-raffles.registrations.summary.{$summaryKey}_count", $summaryCount, ['count' => $summaryCount]) }}
                        </dd>
                    </div>
                @endforeach
            </dl>
        </section>

        @if ($raffle->registrations->isEmpty())
            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6">
                <p class="text-lg font-medium text-slate-900">{{ __('admin-raffles.registrations.empty.title') }}</p>
                <p class="mt-2 text-sm text-slate-600">{{ __('admin-raffles.registrations.empty.description') }}</p>
            </div>
        @else
            <div class="overflow-hidden rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-medium">{{ __('admin-raffles.registrations.columns.name') }}</th>
                            <th scope="col" class="px-4 py-3 font-medium">{{ __('admin-raffles.registrations.columns.email') }}</th>
                            <th scope="col" class="px-4 py-3 font-medium">{{ __('admin-raffles.registrations.columns.status') }}</th>
                            <th scope="col" class="px-4 py-3 font-medium">{{ __('admin-raffles.registrations.columns.created_at') }}</th>
                            <th scope="col" class="px-4 py-3 font-medium">{{ __('admin-raffles.registrations.columns.linked_account') }}</th>
                            <th scope="col" class="px-4 py-3 font-medium">{{ __('admin-raffles.registrations.columns.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white text-slate-900">
                        @foreach ($raffle->registrations as $registration)
                            @php($linkedAccountLabel = $registration->user_id !== null
                                ? __('admin-raffles.registrations.linked_account.yes')
                                : __('admin-raffles.registrations.linked_account.no'))
                            @php($statusLabel = __('admin-raffles.registrations.status.'.$registration->status->value))

                            <tr>
                                <td class="px-4 py-3">{{ $registration->name }}</td>
                                <td class="px-4 py-3">{{ $registration->email }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700">{{ $statusLabel }}</span>
                                </td>
                                <td class="px-4 py-3">{{ $registration->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-3">{{ $linkedAccountLabel }}</td>
                                <td class="px-4 py-3">
                                    @if ($registration->canBeFlagged() || $registration->canBeCancelled())
                                        <div class="flex flex-wrap gap-2">
                                            @if ($registration->canBeFlagged())
                                                <form method="POST" action="{{ route('admin.raffles.registrations.flag', [$raffle, $registration]) }}">
                                                    @csrf
                                                    <button
                                                        type="submit"
                                                        class="rounded-lg border border-amber-300 px-2 py-1 text-xs font-medium text-amber-800 transition hover:bg-amber-50"
                                                        onclick="return confirm('{{ __('admin-raffles.registrations.actions.flag_confirm') }}')"
                                                    >
                                                        {{ __('admin-raffles.registrations.actions.flag') }}
                                                    </button>
                                                </form>
                                            @endif
                                            @if ($registration->canBeCancelled())
                                                <form method="POST" action="{{ route('admin.raffles.registrations.cancel', [$raffle, $registration]) }}">
                                                    @csrf
                                                    <button
                                                        type="submit"
                                                        class="rounded-lg border border-red-300 px-2 py-1 text-xs font-medium text-red-800 transition hover:bg-red-50"
                                                        onclick="return confirm('{{ __('admin-raffles.registrations.actions.cancel_confirm') }}')"
                                                    >
                                                        {{ __('admin-raffles.registrations.actions.cancel') }}
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-sm text-slate-500">{{ __('admin-raffles.registrations.actions.none_available') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-layouts.app>
