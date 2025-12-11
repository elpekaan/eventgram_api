<?php

declare(strict_types=1);

namespace App\Modules\Event\Services;

use App\Contracts\Services\EventServiceInterface;
use App\Modules\Event\DTOs\CreateEventDTO;
use App\Modules\Event\DTOs\UpdateEventDTO;
use App\Modules\Event\Events\EventCreated;
use App\Modules\Event\Events\EventPublished;
use App\Modules\Event\Events\EventCancelled;
use App\Modules\Event\Models\Event;
use App\Modules\Venue\Models\Venue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EventService implements EventServiceInterface
{
    /**
     * Create new event with ticket types
     */
    public function create(CreateEventDTO $dto): Event
    {
        return DB::transaction(function () use ($dto) {
            // 1. Verify venue exists and is verified
            $venue = Venue::findOrFail($dto->venueId);
            
            if ($venue->status !== 'verified') {
                throw ValidationException::withMessages([
                    'venue_id' => ['Mekan henüz onaylanmamış!']
                ]);
            }

            // 2. Calculate total capacity
            $totalCapacity = collect($dto->ticketTypes)->sum('quantity');

            // 3. Create event
            $event = Event::create([
                'venue_id' => $dto->venueId,
                'created_by' => $dto->createdBy,
                'name' => $dto->name,
                'slug' => $this->generateUniqueSlug($dto->name),
                'category_id' => $dto->categoryId,
                'description' => $dto->description,
                'date' => $dto->date,
                'doors_open' => $dto->doorsOpen,
                'ends_at' => $dto->endsAt,
                'timezone' => $dto->timezone,
                'poster_url' => $dto->posterUrl,
                'banner_url' => $dto->bannerUrl,
                'gallery' => $dto->gallery,
                'sales_start' => $dto->salesStart,
                'sales_end' => $dto->salesEnd,
                'max_tickets_per_order' => $dto->maxTicketsPerOrder,
                'check_in_opens_hours' => $dto->checkInOpensHours,
                'late_entry_hours' => $dto->lateEntryHours,
                'allow_late_entry' => $dto->allowLateEntry,
                'check_in_status' => 'closed',
                'status' => 'draft',
                'total_capacity' => $totalCapacity,
                'tickets_sold' => 0,
                'checked_in_count' => 0,
                'views_count' => 0,
                'likes_count' => 0,
                'shares_count' => 0,
            ]);

            // 4. Create ticket types
            foreach ($dto->ticketTypes as $ticketTypeData) {
                $event->ticketTypes()->create([
                    'name' => $ticketTypeData['name'],
                    'description' => $ticketTypeData['description'] ?? null,
                    'price' => $ticketTypeData['price'],
                    'service_fee' => $ticketTypeData['service_fee'] ?? 0,
                    'quantity' => $ticketTypeData['quantity'],
                    'sold' => 0,
                    'reserved' => 0,
                    'sales_start' => $ticketTypeData['sales_start'] ?? null,
                    'sales_end' => $ticketTypeData['sales_end'] ?? null,
                    'min_per_order' => $ticketTypeData['min_per_order'] ?? 1,
                    'max_per_order' => $ticketTypeData['max_per_order'] ?? null,
                    'is_visible' => $ticketTypeData['is_visible'] ?? true,
                    'sort_order' => $ticketTypeData['sort_order'] ?? 0,
                ]);
            }

            // 5. Increment venue's total_events
            $venue->increment('total_events');

            // 6. Fire event
            event(new EventCreated($event));

            // 7. Log
            Log::info('Event created', [
                'event_id' => $event->id,
                'venue_id' => $venue->id,
                'created_by' => $dto->createdBy,
                'ticket_types_count' => count($dto->ticketTypes),
            ]);

            return $event->load('ticketTypes', 'venue');
        });
    }

    /**
     * Update event (TODO: implement)
     */
    public function update(int $eventId, UpdateEventDTO $dto): Event
    {
        // TODO: Implement update logic
        throw new \Exception('Not implemented yet');
    }

    /**
     * Publish event
     */
    public function publish(int $eventId): Event
    {
        return DB::transaction(function () use ($eventId) {
            $event = Event::findOrFail($eventId);

            // Validate can be published
            if ($event->status !== 'draft') {
                throw ValidationException::withMessages([
                    'event' => ['Sadece taslak etkinlikler yayınlanabilir!']
                ]);
            }

            if ($event->ticketTypes()->count() === 0) {
                throw ValidationException::withMessages([
                    'event' => ['En az 1 bilet tipi eklemelisiniz!']
                ]);
            }

            // Publish
            $event->update([
                'status' => 'published',
                'published_at' => now(),
            ]);

            // Fire event
            event(new EventPublished($event));

            // Log
            Log::info('Event published', [
                'event_id' => $event->id,
                'venue_id' => $event->venue_id,
            ]);

            return $event;
        });
    }

    /**
     * Cancel event
     */
    public function cancel(int $eventId, string $reason): Event
    {
        return DB::transaction(function () use ($eventId, $reason) {
            $event = Event::findOrFail($eventId);

            $event->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            // Fire event (will trigger refund process)
            event(new EventCancelled($event, $reason));

            // Log
            Log::info('Event cancelled', [
                'event_id' => $event->id,
                'reason' => $reason,
            ]);

            return $event;
        });
    }

    /**
     * Delete event (soft delete)
     */
    public function delete(int $eventId): void
    {
        $event = Event::findOrFail($eventId);
        
        if ($event->tickets_sold > 0) {
            throw ValidationException::withMessages([
                'event' => ['Satılmış biletleri olan etkinlik silinemez!']
            ]);
        }

        $event->delete();

        Log::info('Event deleted', [
            'event_id' => $eventId,
        ]);
    }

    /**
     * Get upcoming events for venue
     */
    public function getUpcoming(int $venueId): array
    {
        return Event::where('venue_id', $venueId)
            ->upcoming()
            ->with('ticketTypes')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    /**
     * Generate unique slug
     */
    private function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $count = 1;

        while (Event::where('slug', $slug)->exists()) {
            $slug = Str::slug($name) . '-' . $count;
            $count++;
        }

        return $slug;
    }
}
