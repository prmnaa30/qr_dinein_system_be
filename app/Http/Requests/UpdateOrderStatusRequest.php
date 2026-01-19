<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Response;

class UpdateOrderStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    #[Endpoint('Update Order Status Request', 'Menangani permintaan pembaruan status pesanan dengan validasi data.')]
    #[BodyParam('status', 'string', 'Status pesanan baru. Harus salah satu dari: pending, preparing, ready, completed, cancelled.', example: 'completed')]
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:pending,preparing,ready,completed,cancelled']
        ];
    }
}
