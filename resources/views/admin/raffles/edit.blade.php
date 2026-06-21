<x-layouts.app>
    <section class="w-full max-w-2xl self-start space-y-6 rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
        <header class="space-y-2">
            <h1 class="text-3xl font-semibold">{{ __('admin-raffles.edit.title') }}</h1>
            <p class="text-slate-600">{{ __('admin-raffles.edit.description') }}</p>
        </header>

        <form method="POST" action="{{ route('admin.raffles.update', $raffle) }}" class="space-y-6">
            @csrf
            @method('PATCH')

            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-2">
                    <label for="starts_at" class="text-sm font-medium text-slate-700">{{ __('admin-raffles.edit.fields.starts_at.label') }}</label>
                    <input
                        id="starts_at"
                        name="starts_at"
                        type="datetime-local"
                        value="{{ old('starts_at', $raffle->starts_at?->format('Y-m-d\TH:i')) }}"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-950 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"
                    >
                    <p class="text-sm text-slate-500">{{ __('admin-raffles.edit.fields.starts_at.help') }}</p>
                    @error('starts_at')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="ends_at" class="text-sm font-medium text-slate-700">{{ __('admin-raffles.edit.fields.ends_at.label') }}</label>
                    <input
                        id="ends_at"
                        name="ends_at"
                        type="datetime-local"
                        value="{{ old('ends_at', $raffle->ends_at?->format('Y-m-d\TH:i')) }}"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-950 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"
                    >
                    <p class="text-sm text-slate-500">{{ __('admin-raffles.edit.fields.ends_at.help') }}</p>
                    @error('ends_at')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                <a
                    href="{{ route('admin.raffles.index') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 font-medium text-slate-700 transition hover:bg-slate-100"
                >
                    {{ __('admin-raffles.edit.actions.cancel') }}
                </a>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-slate-950 px-4 py-2 font-medium text-white transition hover:bg-slate-800"
                >
                    {{ __('admin-raffles.edit.actions.submit') }}
                </button>
            </div>
        </form>
    </section>
</x-layouts.app>
