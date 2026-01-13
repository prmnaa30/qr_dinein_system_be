<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Response;

class UpdateTableRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    #[Endpoint('Table Update Request', 'Menangani permintaan pembaruan meja dengan validasi data.')]
    #[BodyParam('table_number', 'string', 'Nomor meja yang unik.', example: 'A1')]
    public function rules(): array
    {
        return [
            'table_number' => ['sometimes', 'string', 'unique:tables,table_number']
        ];
    }
}
