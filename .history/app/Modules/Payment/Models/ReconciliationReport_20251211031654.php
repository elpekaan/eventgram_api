<?php

declare(strict_types=1);

namespace App\Modules\Payment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon $date
 * @property int $platform_count
 * @property float $platform_total
 * @property int $provider_count
 * @property float $provider_total
 * @property string $status
 * @property float $difference
 * @property array|null $discrepancies
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
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
