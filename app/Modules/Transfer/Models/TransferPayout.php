<?php

declare(strict_types=1);

namespace App\Modules\Transfer\Models;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $transfer_id
 * @property int $seller_id
 * @property float $amount
 * @property string|null $iban
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class TransferPayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_id',
        'seller_id',
        'amount',
        'iban',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(TicketTransfer::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
