<?php

declare(strict_types=1);

namespace App\Modules\Payment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $transaction_id
 * @property string $provider
 * @property string $event
 * @property string $payload
 * @property string $payload_hash
 * @property string $status
 * @property string|null $error_message
 * @property string|null $source_ip
 * @property \Illuminate\Support\Carbon|null $processed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class WebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'provider',
        'event',
        'payload',
        'payload_hash',
        'status',
        'error_message',
        'source_ip',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'transaction_id');
    }
}
