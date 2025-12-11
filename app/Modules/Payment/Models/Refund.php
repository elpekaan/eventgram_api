<?php

declare(strict_types=1);

namespace App\Modules\Payment\Models;

use App\Modules\Order\Models\Order;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $order_id
 * @property int $payment_transaction_id
 * @property int $user_id
 * @property float $amount
 * @property float $processing_fee
 * @property string $status
 * @property string $reason
 * @property string|null $description
 * @property string|null $provider_refund_id
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read Order $order
 * @property-read PaymentTransaction $transaction
 * @property-read User $user
 */
class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_transaction_id',
        'user_id',
        'amount',
        'processing_fee',
        'status',
        'reason',
        'description',
        'provider_refund_id',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processing_fee' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
