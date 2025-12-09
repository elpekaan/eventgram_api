<?php

declare(strict_types=1);

namespace App\Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Herkes kayıt olabilir.
    }

    public function rules(): array {
        return [
            'name' => ['required', 'string', 'max:255'], // unique:users
            'email' => ['required', 'string', 'email', 'max:255',
        ]
    }
}
