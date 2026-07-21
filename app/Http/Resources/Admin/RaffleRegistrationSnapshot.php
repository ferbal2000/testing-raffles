<?php

namespace App\Http\Resources\Admin;

use App\Models\Raffle;
use App\Models\RaffleRegistration;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

final class RaffleRegistrationSnapshot extends JsonResource
{
    public function toArray(Request $request): array
    {
        $raffle = $this->resource['raffle'];
        $paginator = $this->resource['registrations'];

        return [
            'raffle' => ['id' => $raffle->getKey()],
            'rows' => $paginator->getCollection()->map(
                fn (RaffleRegistration $registration) => $this->row($raffle, $registration, $paginator->currentPage()),
            )->values()->all(),
            'counts' => [
                'active' => $raffle->active_registrations_count, 'flagged' => $raffle->flagged_registrations_count,
                'cancelled' => $raffle->cancelled_registrations_count, 'total' => $raffle->registrations_count,
            ],
            'pagination' => [
                'current_page' => $paginator->currentPage(), 'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(), 'from' => $paginator->firstItem(), 'to' => $paginator->lastItem(),
                'canonical_url' => $this->pageUrl($raffle, $paginator->currentPage()),
                'links' => collect(range(1, $paginator->lastPage()))->map(fn (int $page) => [
                    'page' => $page, 'url' => $this->pageUrl($raffle, $page), 'current' => $page === $paginator->currentPage(),
                ])->all(),
            ],
            'copy' => [
                'busy' => __('admin-raffles.registrations.interaction.busy'), 'login_url' => route('admin.login'),
                'unavailable' => __('admin-raffles.registrations.interaction.unavailable'),
            ],
        ];
    }

    private function row(Raffle $raffle, RaffleRegistration $registration, int $page): array
    {
        return [
            'id' => $registration->getKey(), 'name' => $registration->name, 'email' => $registration->email,
            'status' => ['value' => $registration->status->value, 'label' => __('admin-raffles.registrations.status.'.$registration->status->value)],
            'created_at' => $registration->created_at?->format('Y-m-d H:i'),
            'linked_account' => [
                'value' => $registration->user_id !== null,
                'label' => __('admin-raffles.registrations.linked_account.'.($registration->user_id ? 'yes' : 'no')),
            ],
            'actions' => $this->actions($raffle, $registration, $page),
        ];
    }

    private function actions(Raffle $raffle, RaffleRegistration $registration, int $page): array
    {
        $actions = [];
        $available = ['flag' => $registration->canBeFlagged(), 'cancel' => $registration->canBeCancelled(), 'restore' => $registration->canBeRestored()];

        foreach (array_keys(array_filter($available)) as $kind) {
            $actions[] = [
                'kind' => $kind, 'label' => __("admin-raffles.registrations.actions.{$kind}"), 'confirm' => __("admin-raffles.registrations.actions.{$kind}_confirm"),
                'url' => route("admin.raffles.registrations.{$kind}", ['raffle' => $raffle, 'registration' => $registration, 'page' => $page]),
            ];
        }

        return $actions;
    }

    private function pageUrl(Raffle $raffle, int $page): string
    {
        return route('admin.raffles.registrations.index', compact('raffle', 'page'));
    }
}
