<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ticket_code' => ['required', 'string', 'min:12', 'max:12'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'device_id' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'ticket_code.required' => 'Bilet kodu gereklidir.',
            'ticket_code.min' => 'Geçersiz bilet kodu formatı.',
            'ticket_code.max' => 'Geçersiz bilet kodu formatı.',
            'latitude.between' => 'Geçersiz konum bilgisi.',
            'longitude.between' => 'Geçersiz konum bilgisi.',
        ];
    }
}
