<?php

namespace App\Models;

use App\Enums\RaffleStatus;
use App\Exceptions\InvalidRaffleTransition;
use Database\Factories\RaffleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LogicException;

#[Fillable(['status', 'starts_at', 'ends_at'])]
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
        ];
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

        if ($this->status !== RaffleStatus::Draft) {
            throw InvalidRaffleTransition::from($this->status->value, RaffleStatus::Published->value);
        }

        $this->forceFill(['status' => RaffleStatus::Published])->save();
    }

    public function close(): void
    {
        $this->ensureIsPersisted();

        if ($this->status !== RaffleStatus::Published) {
            throw InvalidRaffleTransition::from($this->status->value, RaffleStatus::Closed->value);
        }

        $this->forceFill(['status' => RaffleStatus::Closed])->save();
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
