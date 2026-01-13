<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Response;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    #[Endpoint('Order Store Request', 'Menangani request pembuatan pesanan baru dengan validasi data.')]
    #[BodyParam('table_id', 'integer', 'ID meja untuk pesanan.', example: 1)]
    #[BodyParam('customer_name', 'string', 'Nama pelanggan (opsional).', example: 'Budi Santoso')]
    #[BodyParam('items', 'array', 'Daftar item pesanan.', example: '[{"product_id": 1, "quantity": 2, "notes": "Tanpa cabe"}]')]
    public function rules(): array
    {
        return [
            'table_id' => ['required', 'exists:tables,id'],
            'customer_name' => ['nullable', 'string', 'max:50'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string', 'max:200'],
        ];
    }
}
