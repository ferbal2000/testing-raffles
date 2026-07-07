<?php

namespace App\Models;

use App\Enums\RaffleRegistrationStatus;
use App\Exceptions\InvalidRaffleRegistrationTransition;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['raffle_id', 'user_id', 'name', 'email', 'status'])]
class RaffleRegistration extends Model
{
    use HasFactory;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => RaffleRegistrationStatus::class,
        ];
    }

    /**
     * @return Attribute<string|null, string|null>
     */
    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value === null
                ? null
                : Str::lower(trim($value)),
        );
    }

    /**
     * @return Attribute<string, RaffleRegistrationStatus|string>
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            set: fn (RaffleRegistrationStatus|string $value) => $value instanceof RaffleRegistrationStatus
                ? $value->value
                : RaffleRegistrationStatus::from($value)->value,
        );
    }

    public function raffle(): BelongsTo
    {
        return $this->belongsTo(Raffle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function canBeFlagged(): bool
    {
        return $this->status === RaffleRegistrationStatus::Active;
    }

    public function canBeCancelled(): bool
    {
        return $this->status === RaffleRegistrationStatus::Active;
    }

    public function markForReview(): void
    {
        if (! $this->canBeFlagged()) {
            throw InvalidRaffleRegistrationTransition::from(
                $this->status->value,
                RaffleRegistrationStatus::Flagged->value,
            );
        }

        $this->forceFill(['status' => RaffleRegistrationStatus::Flagged]);
    }

    public function cancel(): void
    {
        if (! $this->canBeCancelled()) {
            throw InvalidRaffleRegistrationTransition::from(
                $this->status->value,
                RaffleRegistrationStatus::Cancelled->value,
            );
        }

        $this->forceFill(['status' => RaffleRegistrationStatus::Cancelled]);
    }
}
