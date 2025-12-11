<?php

declare(strict_types=1);

namespace App\Modules\Order\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_id' => ['required', 'integer', 'exists:events,id'],
            'ticket_type_id' => ['required', 'integer', 'exists:event_ticket_types,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:50'],
            'coupon_code' => ['nullable', 'string', 'alpha_num', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'event_id.required' => 'Etkinlik seçimi zorunludur.',
            'event_id.exists' => 'Seçilen etkinlik bulunamadı.',
            'ticket_type_id.required' => 'Bilet tipi seçimi zorunludur.',
            'ticket_type_id.exists' => 'Seçilen bilet tipi bulunamadı.',
            'quantity.required' => 'Bilet adedi zorunludur.',
            'quantity.min' => 'En az 1 bilet alınmalıdır.',
            'quantity.max' => 'Maksimum 50 bilet alınabilir.',
            'coupon_code.alpha_num' => 'Kupon kodu sadece harf ve rakam içerebilir.',
        ];
    }
}
