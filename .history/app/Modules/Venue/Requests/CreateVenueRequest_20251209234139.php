<?php

declare(strict_types=1);

namespace App\Modules\Venue\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateVenueRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Sadece giriş yapmış kullanıcılar mekan oluşturabilir.
        // Zaten route'a 'auth:sanctum' middleware ekleyeceğiz ama burası da true olmalı.
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:venues,name'], // Aynı isimde mekan olmasın
            'description' => ['nullable', 'string'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:500'],
            'capacity' => ['required', 'integer', 'min:10'], // En az 10 kişilik mekan
            'phone' => ['nullable', 'string', 'max:20'],
            'website' => ['nullable', 'url', 'max:255'],
        ];
    }
}
