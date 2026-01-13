<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Response;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    #[Endpoint('Product Store Request', 'Menangani permintaan pembuatan produk baru dengan validasi data.')]
    #[BodyParam('category_id', 'integer', 'ID kategori produk.', example: 1)]
    #[BodyParam('name', 'string', 'Nama produk.', example: 'Nasi Goreng Spesial')]
    #[BodyParam('description', 'string', 'Deskripsi produk.', example: 'Nasi goreng dengan campuran telur dan ayam')]
    #[BodyParam('price', 'number', 'Harga produk.', example: 25000)]
    #[BodyParam('image', 'file', 'Gambar produk (opsional).', example: 'nasi_goreng.jpg')]
    #[BodyParam('is_available', 'boolean', 'Status ketersediaan produk.', example: true)]
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:png,jpeg,jpg', 'max:2048'],
            'is_available' => ['boolean'],
        ];
    }
}
