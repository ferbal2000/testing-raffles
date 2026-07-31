<?php

namespace App\Http\Resources\Admin;

use App\Models\Raffle;
use App\Models\RaffleRegistration;
use Illuminate\Pagination\LengthAwarePaginator;

final class RaffleRegistrationSnapshot
{
    public const PER_PAGE = 25;

    /** @return array<string, mixed> */
    public static function make(Raffle $raffle, LengthAwarePaginator $registrations): array
    {
        $currentPage = $registrations->currentPage();
        $lastPage = $registrations->lastPage();
        $pageUrl = fn (int $page): string => route(
            'admin.raffles.registrations.index',
            $page === 1 ? [$raffle] : [$raffle, 'page' => $page],
        );
        $linkPages = collect([1, $lastPage, ...range($currentPage - 2, $currentPage + 2)])
            ->filter(fn (int $page): bool => $page >= 1 && $page <= $lastPage)
            ->unique()
            ->sort()
            ->values();

        return [
            'version' => 1,
            'raffleId' => $raffle->id,
            'rows' => collect($registrations->items())->map(
                fn (RaffleRegistration $registration): array => self::row($raffle, $registration),
            )->values()->all(),
            'counts' => [
                'active' => $raffle->active_registrations_count,
                'flagged' => $raffle->flagged_registrations_count,
                'cancelled' => $raffle->cancelled_registrations_count,
                'total' => $raffle->registrations_count,
            ],
            'pagination' => [
                'current' => $currentPage,
                'last' => $lastPage,
                'perPage' => self::PER_PAGE,
                'total' => $registrations->total(),
                'canonicalUrl' => $pageUrl($currentPage),
                'links' => $linkPages
                    ->map(fn (int $page): array => ['page' => $page, 'url' => $pageUrl($page), 'current' => $page === $currentPage])
                    ->all(),
            ],
            'loginUrl' => route('admin.login'),
            'copy' => [
                'paginationLabel' => trans('admin-raffles.registrations.pagination.label'),
                'page' => trans('admin-raffles.registrations.pagination.page'),
                'navigationError' => trans('admin-raffles.registrations.pagination.navigation_error'),
                'mutationError' => trans('admin-raffles.registrations.pagination.mutation_error'),
                'reconciliationError' => trans('admin-raffles.registrations.pagination.reconciliation_error'),
                'retryLabel' => trans('admin-raffles.registrations.pagination.retry'),
                'sessionExpired' => trans('admin-raffles.registrations.pagination.session_expired'),
                'loginLabel' => trans('admin-raffles.registrations.pagination.login'),
                'summary' => collect(['active', 'flagged', 'cancelled', 'total'])->mapWithKeys(fn (string $key): array => [$key => trans("admin-raffles.registrations.summary.{$key}_label")])->all(),
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function row(Raffle $raffle, RaffleRegistration $registration): array
    {
        $action = fn (string $kind): array => [
            'kind' => $kind,
            'label' => trans("admin-raffles.registrations.actions.{$kind}"),
            'confirm' => trans("admin-raffles.registrations.actions.{$kind}_confirm"),
            'url' => route("admin.raffles.registrations.{$kind}", [$raffle, $registration]),
            'method' => 'POST',
        ];

        return [
            'id' => $registration->id,
            'name' => $registration->name,
            'email' => $registration->email,
            'status' => $registration->status->value,
            'statusLabel' => trans('admin-raffles.registrations.status.'.$registration->status->value),
            'createdAt' => $registration->created_at?->format('Y-m-d H:i'),
            'linkedAccount' => $registration->user_id !== null,
            'linkedAccountLabel' => trans('admin-raffles.registrations.linked_account.'.($registration->user_id === null ? 'no' : 'yes')),
            'actions' => match (true) {
                $registration->canBeRestored() => [$action('restore')],
                $registration->canBeFlagged() => [$action('flag'), $action('cancel')],
                default => [],
            },
        ];
    }
}
