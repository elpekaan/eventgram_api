<?php

declare(strict_types=1);

namespace App\Modules\Transfer\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Auth middleware zaten kontrol edecek
    }

    public function rules(): array
    {
        return [
            'ticket_id' => ['required', 'integer', 'exists:tickets,id'],
            'buyer_email' => ['required', 'email', 'exists:users,email'], // Alıcı sistemde kayıtlı olmalı
            'asking_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
