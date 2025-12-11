<?php

declare(strict_types=1);

namespace App\Modules\Payment\Models;

use App\Modules\Order\Models\Order;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chargeback extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_transaction_id',
        'order_id',
        'user_id',
        'amount',
        'reason_code',
        'reason_description',
        'status',
        'evidence_snapshot',
        'dispute_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'evidence_snapshot' => 'array',
        'dispute_date' => 'datetime',
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
