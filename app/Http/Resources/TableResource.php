<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'table_number' => $this->resource->table_number,
            'qr_uuid' => $this->resource->qr_uuid,
            'scan_url' => config('app.frontend_url', 'http://localhost:5173') . '/scan?table_uuid=' . $this->resource->qr_uuid,
            'created_at' => $this->resource->created_at->format('Y-m-d H:i:s')
        ];
    }
}
