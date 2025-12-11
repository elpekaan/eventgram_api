<?php

declare(strict_types=1);

namespace App\Modules\Order\Mail;

use App\Modules\Order\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketPurchasedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Siparişiniz Onaylandı - Biletleriniz Hazır! 🎫',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-purchased', // View dosyası
        );
    }
}
