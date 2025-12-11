<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\DTOs;

use App\DTOs\BaseDTO;
use Illuminate\Http\Request;

final readonly class CheckInRequestDTO extends BaseDTO
{
    public function __construct(
        public string $ticketCode,
        public int $staffId,
        public ?float $latitude,
        public ?float $longitude,
        public ?string $deviceId,
        public ?string $deviceInfo,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            ticketCode: $request->input('ticket_code'),
            staffId: $request->user()->id,
            latitude: $request->input('latitude'),
            longitude: $request->input('longitude'),
            deviceId: $request->input('device_id'),
            deviceInfo: $request->userAgent(),
        );
    }
}
