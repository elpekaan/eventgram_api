<?php

declare(strict_types=1);

namespace App\Modules\Transfer\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ticket_id' => ['required', 'integer', 'exists:tickets,id'],
            'buyer_email' => ['required', 'email', 'max:255', 'exists:users,email'],
            'asking_price' => ['required', 'numeric', 'min:0', 'max:999999'],
        ];
    }

    public function messages(): array
    {
        return [
            'ticket_id.required' => 'Bilet seçimi zorunludur.',
            'ticket_id.exists' => 'Seçilen bilet bulunamadı.',
            'buyer_email.required' => 'Alıcı e-posta adresi zorunludur.',
            'buyer_email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'buyer_email.exists' => 'Bu e-posta adresine kayıtlı kullanıcı bulunamadı.',
            'asking_price.required' => 'Fiyat alanı zorunludur.',
            'asking_price.min' => 'Fiyat 0 veya daha büyük olmalıdır.',
            'asking_price.max' => 'Fiyat çok yüksek.',
        ];
    }
}
