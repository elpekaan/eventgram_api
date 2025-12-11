<?php

declare(strict_types=1);

namespace App\Modules\Event\DTOs;

use App\DTOs\BaseDTO;
use Illuminate\Http\Request;

final readonly class CreateEventDTO extends BaseDTO
{
    public function __construct(
        public int $venueId,
        public int $createdBy,
        public string $name,
        public ?int $categoryId,
        public string $description,
        public string $date,
        public ?string $doorsOpen,
        public ?string $endsAt,
        public string $timezone,
        public ?string $posterUrl,
        public ?string $bannerUrl,
        public ?array $gallery,
        public ?string $salesStart,
        public ?string $salesEnd,
        public int $maxTicketsPerOrder,
        public int $checkInOpensHours,
        public int $lateEntryHours,
        public bool $allowLateEntry,
        public array $ticketTypes,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            venueId: (int) $request->input('venue_id'),
            createdBy: $request->user()->id,
            name: $request->input('name'),
            categoryId: $request->input('category_id'),
            description: $request->input('description'),
            date: $request->input('date'),
            doorsOpen: $request->input('doors_open'),
            endsAt: $request->input('ends_at'),
            timezone: $request->input('timezone', 'Europe/Istanbul'),
            posterUrl: $request->input('poster_url'),
            bannerUrl: $request->input('banner_url'),
            gallery: $request->input('gallery'),
            salesStart: $request->input('sales_start'),
            salesEnd: $request->input('sales_end'),
            maxTicketsPerOrder: (int) $request->input('max_tickets_per_order', 10),
            checkInOpensHours: (int) $request->input('check_in_opens_hours', 2),
            lateEntryHours: (int) $request->input('late_entry_hours', 2),
            allowLateEntry: (bool) $request->input('allow_late_entry', true),
            ticketTypes: $request->input('ticket_types', []),
        );
    }
}
