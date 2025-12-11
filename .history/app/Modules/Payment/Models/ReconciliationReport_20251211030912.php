<?php

declare(strict_types=1);

namespace App\Modules\Payment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReconciliationReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'platform_count',
        'platform_total',
        'provider_count',
        'provider_total',
        'status',
        'difference',
        'discrepancies',
    ];

    protected $casts = [
        'date' => 'date',
        'platform_total' => 'decimal:2',
        'provider_total' => 'decimal:2',
        'difference' => 'decimal:2',
        'discrepancies' => 'array',
    ];
}
