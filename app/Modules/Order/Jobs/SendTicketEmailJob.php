<?php

declare(strict_types=1);

namespace App\Modules\Order\Jobs;

use App\Modules\Order\Mail\TicketPurchasedMail;
use App\Modules\Order\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTicketEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Order $order
    ) {}

    public function handle(): void
    {
        // Kullanıcının emailine gönder
        Mail::to($this->order->user->email)
            ->send(new TicketPurchasedMail($this->order));
    }
}
