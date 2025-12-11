<?php

declare(strict_types=1);

namespace App\Modules\Ticket\Services;

use App\Contracts\Services\TicketServiceInterface;
use App\Modules\Order\Models\Order;
use App\Modules\Ticket\Events\TicketsGenerated;
use App\Modules\Ticket\Events\QrCodeRegenerated;
use App\Modules\Ticket\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TicketService implements TicketServiceInterface
{
    /**
     * Generate tickets for completed order
     */
    public function generateTickets(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $tickets = [];

            // Generate ticket data
            for ($i = 0; $i < $order->quantity; $i++) {
                $ticketCode = $this->generateUniqueTicketCode();
                
                $tickets[] = [
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                    'event_id' => $order->event_id,
                    'ticket_type_id' => $order->ticket_type_id,
                    'ticket_code' => $ticketCode,
                    'qr_code_url' => $this->generateQrCodeUrl($ticketCode),
                    'is_transferred' => false,
                    'transfer_completed' => false,
                    'status' => 'active',
                    'is_locked' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Bulk insert
            Ticket::insert($tickets);

            // Fire event
            event(new TicketsGenerated($order, $order->quantity));

            // Log
            Log::info('Tickets generated', [
                'order_id' => $order->id,
                'count' => $order->quantity,
            ]);
        });
    }

    /**
     * Regenerate QR code (e.g., after transfer)
     */
    public function regenerateQrCode(Ticket $ticket): Ticket
    {
        return DB::transaction(function () use ($ticket) {
            $oldCode = $ticket->ticket_code;
            $newCode = $this->generateUniqueTicketCode();

            // Update ticket
            $ticket->update([
                'ticket_code' => $newCode,
                'qr_code_url' => $this->generateQrCodeUrl($newCode),
                'qr_regenerated_at' => now(),
            ]);

            // Fire event
            event(new QrCodeRegenerated($ticket, $oldCode));

            // Log
            Log::info('QR code regenerated', [
                'ticket_id' => $ticket->id,
                'old_code' => $oldCode,
                'new_code' => $newCode,
            ]);

            return $ticket->fresh();
        });
    }

    /**
     * Validate ticket for check-in
     */
    public function validateTicket(string $ticketCode, int $eventId): Ticket
    {
        $ticket = Ticket::where('ticket_code', $ticketCode)
            ->with(['event', 'user', 'ticketType'])
            ->first();

        if (!$ticket) {
            throw ValidationException::withMessages([
                'ticket_code' => ['Geçersiz bilet kodu!']
            ]);
        }

        if ($ticket->event_id !== $eventId) {
            throw ValidationException::withMessages([
                'ticket_code' => ['Bu bilet farklı bir etkinlik için!']
            ]);
        }

        if ($ticket->status === 'used') {
            throw ValidationException::withMessages([
                'ticket_code' => [
                    "Bu bilet daha önce kullanılmış! (Giriş: {$ticket->used_at?->format('d.m.Y H:i')})"
                ]
            ]);
        }

        if ($ticket->status !== 'active') {
            throw ValidationException::withMessages([
                'ticket_code' => ['Bu bilet aktif değil! Durum: ' . $ticket->status]
            ]);
        }

        if ($ticket->is_locked) {
            throw ValidationException::withMessages([
                'ticket_code' => ['Bu bilet kilitli! Sebep: ' . ($ticket->locked_reason ?? 'Belirtilmemiş')]
            ]);
        }

        return $ticket;
    }

    /**
     * Get user's tickets
     */
    public function getUserTickets(int $userId): array
    {
        return Ticket::where('user_id', $userId)
            ->with(['event.venue', 'ticketType', 'order'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Generate unique ticket code
     */
    private function generateUniqueTicketCode(): string
    {
        do {
            // 12 character alphanumeric code
            $code = strtoupper(Str::random(12));
        } while (Ticket::where('ticket_code', $code)->exists());

        return $code;
    }

    /**
     * Generate QR code URL
     */
    private function generateQrCodeUrl(string $ticketCode): string
    {
        // TODO: Integrate with QR code generation service
        return config('app.url') . '/api/tickets/qr/' . $ticketCode;
    }
}
