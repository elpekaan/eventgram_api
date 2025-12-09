<?php

declare(strict_types=1);

namespace App\Modules\Order\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Auth middleware zaten kontrol edecek
    }

    public function rules(): array
    {
        return [
            'event_id' => ['required', 'integer', 'exists:events,id'],

            // Ticket type id geçerli mi ve bu event'e mi ait?
            // (Advanced validation service içinde de yapılabilir ama burada basic check iyidir)
            'ticket_type_id' => ['required', 'integer', 'exists:event_ticket_types,id'],

            // Karaborsayı engellemek için işlem başına limit
            'quantity' => ['required', 'integer', 'min:1', 'max:5'],
        ];
    }
}
