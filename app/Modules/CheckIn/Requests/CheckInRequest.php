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
            'code' => ['required', 'string', 'size:12'], // Bilet kodlarımız 12 karakterli (TIC-...)
        ];
    }
}
