<?php

declare(strict_types=1);

namespace App\Modules\Event\Requests;

use App\Modules\Event\Enums\EventCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Middleware kontrol edecek
    }

    public function rules(): array
    {
        return [
            'venue_id' => ['required', 'integer', 'exists:venues,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'start_time' => ['required', 'date', 'after:now'],
            'end_time' => ['nullable', 'date', 'after:start_time'],
            'category' => ['required', Rule::enum(EventCategory::class)],

            // Nested Array Validation
            'tickets' => ['required', 'array', 'min:1'], // En az 1 bilet tipi olmalı
            'tickets.*.name' => ['required', 'string', 'max:50'],
            'tickets.*.price' => ['required', 'numeric', 'min:0'],
            'tickets.*.capacity' => ['required', 'integer', 'min:1'],
        ];
    }
}
