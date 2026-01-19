<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Response;

class ExportSalesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    #[Endpoint('Permintaan Ekspor Laporan Penjualan', 'Menangani permintaan ekspor laporan penjualan dengan validasi rentang tanggal.')]
    #[BodyParam('start_date', 'string', 'Tanggal awal periode laporan (format: YYYY-MM-DD).', example: '2023-01-01')]
    #[BodyParam('end_date', 'string', 'Tanggal akhir periode laporan (format: YYYY-MM-DD).', example: '2023-01-31')]
    #[BodyParam('export', 'string', 'Format ekspor (opsional).', example: 'csv')]
    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'export' => ['sometimes', 'string']
        ];
    }
}
