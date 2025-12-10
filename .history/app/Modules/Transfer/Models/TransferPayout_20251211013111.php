<?php

declare(strict_types=1);

namespace App\Modules\Transfer\Models;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferPayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_id',
        'seller_id',
        'amount',
        'status',
        'iban'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(TicketTransfer::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
