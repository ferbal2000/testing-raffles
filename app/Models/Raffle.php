<?php

namespace App\Models;

use App\Enums\RaffleStatus;
use App\Exceptions\InvalidRaffleTransition;
use Carbon\CarbonImmutable;
use Database\Factories\RaffleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

#[Fillable([
    'status',
    'starts_at',
    'ends_at',
    'participation_opened_at',
    'participation_closed_at',
    'participation_closed_reason',
    'participation_closed_by_admin_id',
])]
class Raffle extends Model
{
    /** @use HasFactory<RaffleFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => RaffleStatus::class,
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
            'participation_opened_at' => 'immutable_datetime',
            'participation_closed_at' => 'immutable_datetime',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'participation_closed_by_admin_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(RaffleRegistration::class);
    }

    protected static function booted(): void
    {
        static::creating(function (self $raffle): void {
            $raffle->status = RaffleStatus::Draft;
        });
    }

    public function publish(): void
    {
        $this->ensureIsPersisted();

        if (! $this->canPublish()) {
            throw InvalidRaffleTransition::from($this->status->value, RaffleStatus::Published->value);
        }

        $this->forceFill(['status' => RaffleStatus::Published])->save();
    }

    public function canPublish(): bool
    {
        return $this->status === RaffleStatus::Draft;
    }

    public function close(): void
    {
        $this->ensureIsPersisted();

        if ($this->status !== RaffleStatus::Published) {
            throw InvalidRaffleTransition::from($this->status->value, RaffleStatus::Closed->value);
        }

        $this->forceFill(['status' => RaffleStatus::Closed])->save();
    }

    public function canAcceptParticipants(): bool
    {
        return $this->status === RaffleStatus::Published
            && $this->participation_opened_at !== null
            && $this->participation_closed_at === null;
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('status', RaffleStatus::Published);
    }

    public function canOpenParticipation(): bool
    {
        return $this->status === RaffleStatus::Published
            && $this->participation_opened_at === null
            && $this->participation_closed_at === null;
    }

    public function canCloseParticipation(): bool
    {
        return $this->status === RaffleStatus::Published
            && $this->participation_opened_at !== null
            && $this->participation_closed_at === null;
    }

    public function openParticipation(CarbonImmutable $openedAt): void
    {
        $this->ensureIsPersisted();

        if (! $this->canOpenParticipation()) {
            throw InvalidRaffleTransition::from($this->status->value, 'participation_open');
        }

        $this->forceFill([
            'participation_opened_at' => $openedAt,
        ])->save();
    }

    public function closeParticipation(CarbonImmutable $closedAt, string $reason = 'admin_closed', ?Admin $admin = null): void
    {
        $this->ensureIsPersisted();

        if (! $this->canCloseParticipation()) {
            throw InvalidRaffleTransition::from($this->status->value, 'participation_close');
        }

        $this->forceFill([
            'participation_closed_at' => $closedAt,
            'participation_closed_reason' => $reason,
            'participation_closed_by_admin_id' => $admin?->getKey(),
        ])->save();
    }

    /**
     * @return Attribute<string, RaffleStatus|string>
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            set: fn (RaffleStatus|string $value) => $value instanceof RaffleStatus
                ? $value->value
                : RaffleStatus::from($value)->value,
        );
    }

    private function ensureIsPersisted(): void
    {
        if (! $this->exists) {
            throw new LogicException('Cannot transition an unsaved raffle.');
        }
    }
}
