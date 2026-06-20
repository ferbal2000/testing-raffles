<x-layouts.app>
    <section class="w-full self-start space-y-6 rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
        <header class="space-y-2">
            <h1 class="text-3xl font-semibold">{{ __('admin-raffles.index.title') }}</h1>
            <p class="text-slate-600">{{ __('admin-raffles.index.description') }}</p>
        </header>

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
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-layouts.app>
