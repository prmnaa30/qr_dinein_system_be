<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Response;

class StoreTableRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    #[Endpoint('Table Store Request', 'Menangani permintaan pembuatan meja baru dengan validasi data.')]
    #[BodyParam('table_number', 'string', 'Nomor meja yang unik.', example: 'A1')]
    public function rules(): array
    {
        return [
            'table_number' => ['required', 'string', 'unique:tables,table_number']
        ];
    }
}
