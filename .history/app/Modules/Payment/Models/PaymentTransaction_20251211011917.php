<?php

declare(strict_types=1);

namespace App\Modules\Payment\Models;

use App\Modules\Order\Models\Order;
use App\Modules\Transfer\Models\TicketTransfer;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int|null $order_id
 * @property int|null $transfer_id
 * @property int $user_id
 * @property string $provider
 * @property string|null $transaction_id
 * @property string $idempotency_key
 * @property float $amount
 * @property string $currency
 * @property string $status
 * @property string|null $payment_status
 * @property int $fraud_status
 * @property string|null $error_code
 * @property string|null $error_message
 * @property string|null $raw_response
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $failed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'transfer_id',
        'user_id',
        'provider',
        'transaction_id',
        'idempotency_key',
        'amount',
        'currency',
        'status',
        'payment_status',
        'fraud_status',
        'error_code',
        'error_message',
        'error_group',
        'raw_response',
        'completed_at',
        'failed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
            'fraud_status' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(TicketTransfer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function webhookLogs(): HasMany
    {
        return $this->hasMany(WebhookLog::class, 'transaction_id');
    }
}
