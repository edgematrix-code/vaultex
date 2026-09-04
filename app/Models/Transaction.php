<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $chain
 * @property string $type
 * @property string $status
 * @property float $amount
 * @property float $usd_value
 * @property float $fee_usd
 * @property string|null $tx_hash
 * @property string|null $from_address
 * @property string|null $to_address
 * @property string|null $note
 * @property Carbon|null $confirmed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'user_id',
    'chain',
    'type',
    'status',
    'amount',
    'usd_value',
    'fee_usd',
    'tx_hash',
    'from_address',
    'to_address',
    'note',
    'confirmed_at',
])]
class Transaction extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'usd_value' => 'float',
            'fee_usd' => 'float',
            'confirmed_at' => 'datetime',
        ];
    }

    /**
     * The user that owns this transaction.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
