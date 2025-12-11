<?php

declare(strict_types=1);

namespace App\Modules\Venue\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateVenueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:500'],
            'capacity' => ['required', 'integer', 'min:10', 'max:1000000'],
            'phone' => ['nullable', 'string', 'regex:/^[0-9\s\-\+\(\)]+$/', 'max:20'],
            'website' => ['nullable', 'url', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Mekan adı zorunludur.',
            'name.min' => 'Mekan adı en az 3 karakter olmalıdır.',
            'name.max' => 'Mekan adı en fazla 255 karakter olabilir.',
            'description.max' => 'Açıklama en fazla 5000 karakter olabilir.',
            'city.required' => 'Şehir seçimi zorunludur.',
            'address.required' => 'Adres bilgisi zorunludur.',
            'capacity.required' => 'Kapasite bilgisi zorunludur.',
            'capacity.min' => 'Minimum kapasite 10 kişi olmalıdır.',
            'capacity.max' => 'Maksimum kapasite 1.000.000 kişi olabilir.',
            'phone.regex' => 'Geçerli bir telefon numarası giriniz.',
            'website.url' => 'Geçerli bir web sitesi adresi giriniz.',
        ];
    }
}
