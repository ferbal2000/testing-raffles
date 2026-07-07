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

        <section class="rounded-xl border border-slate-200 bg-slate-50 p-4" aria-labelledby="registration-summary-title">
            <h2 id="registration-summary-title" class="text-sm font-medium text-slate-600">{{ __('admin-raffles.registrations.summary_title') }}</h2>
            <p class="mt-1 text-lg font-semibold text-slate-950">
                {{ trans_choice('admin-raffles.registrations.summary_count', $raffle->registrations_count, ['count' => $raffle->registrations_count]) }}
            </p>
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
                            <th scope="col" class="px-4 py-3 font-medium">{{ __('admin-raffles.registrations.columns.created_at') }}</th>
                            <th scope="col" class="px-4 py-3 font-medium">{{ __('admin-raffles.registrations.columns.linked_account') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white text-slate-900">
                        @foreach ($raffle->registrations as $registration)
                            @php($linkedAccountLabel = $registration->user_id !== null
                                ? __('admin-raffles.registrations.linked_account.yes')
                                : __('admin-raffles.registrations.linked_account.no'))

                            <tr>
                                <td class="px-4 py-3">{{ $registration->name }}</td>
                                <td class="px-4 py-3">{{ $registration->email }}</td>
                                <td class="px-4 py-3">{{ $registration->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-3">{{ $linkedAccountLabel }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-layouts.app>
