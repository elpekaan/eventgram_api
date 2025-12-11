<?php

declare(strict_types=1);

namespace App\Modules\Event\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'venue_id' => ['required', 'integer', 'exists:venues,id'],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'description' => ['required', 'string', 'max:5000'],
            'date' => ['required', 'date', 'after:now'],
            'doors_open' => ['nullable', 'date', 'before:date'],
            'ends_at' => ['nullable', 'date', 'after:date'],
            'timezone' => ['required', 'string', 'timezone'],
            'poster_url' => ['nullable', 'url', 'max:500'],
            'banner_url' => ['nullable', 'url', 'max:500'],
            'gallery' => ['nullable', 'array', 'max:10'],
            'gallery.*' => ['url', 'max:500'],
            'sales_start' => ['nullable', 'date', 'before:date'],
            'sales_end' => ['nullable', 'date', 'before:date', 'after:sales_start'],
            'max_tickets_per_order' => ['required', 'integer', 'min:1', 'max:50'],
            'check_in_opens_hours' => ['required', 'integer', 'min:0', 'max:24'],
            'late_entry_hours' => ['required', 'integer', 'min:0', 'max:12'],
            'allow_late_entry' => ['required', 'boolean'],

            // Ticket types
            'ticket_types' => ['required', 'array', 'min:1', 'max:10'],
            'ticket_types.*.name' => ['required', 'string', 'max:100'],
            'ticket_types.*.description' => ['nullable', 'string', 'max:500'],
            'ticket_types.*.price' => ['required', 'numeric', 'min:0', 'max:999999'],
            'ticket_types.*.service_fee' => ['nullable', 'numeric', 'min:0'],
            'ticket_types.*.quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'ticket_types.*.sales_start' => ['nullable', 'date'],
            'ticket_types.*.sales_end' => ['nullable', 'date', 'after:ticket_types.*.sales_start'],
            'ticket_types.*.min_per_order' => ['nullable', 'integer', 'min:1'],
            'ticket_types.*.max_per_order' => ['nullable', 'integer', 'min:1', 'gte:ticket_types.*.min_per_order'],
            'ticket_types.*.is_visible' => ['nullable', 'boolean'],
            'ticket_types.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'venue_id.required' => 'Mekan seçimi zorunludur.',
            'venue_id.exists' => 'Seçilen mekan bulunamadı.',
            'name.required' => 'Etkinlik adı zorunludur.',
            'name.max' => 'Etkinlik adı en fazla 255 karakter olabilir.',
            'description.required' => 'Açıklama zorunludur.',
            'date.required' => 'Etkinlik tarihi zorunludur.',
            'date.after' => 'Etkinlik tarihi gelecekte olmalıdır.',
            'doors_open.before' => 'Kapı açılış saati, etkinlik başlangıcından önce olmalıdır.',
            'ends_at.after' => 'Etkinlik bitiş saati, başlangıç saatinden sonra olmalıdır.',
            'ticket_types.required' => 'En az 1 bilet tipi eklemelisiniz.',
            'ticket_types.*.name.required' => 'Bilet tipi adı zorunludur.',
            'ticket_types.*.price.required' => 'Bilet fiyatı zorunludur.',
            'ticket_types.*.price.min' => 'Bilet fiyatı 0 veya daha büyük olmalıdır.',
            'ticket_types.*.quantity.required' => 'Bilet adedi zorunludur.',
            'ticket_types.*.quantity.min' => 'En az 1 bilet eklemelisiniz.',
        ];
    }
}
