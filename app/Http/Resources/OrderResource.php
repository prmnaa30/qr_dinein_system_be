<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'customer_name' => $this->resource->customer_name,
            'table_number' => $this->whenLoaded('table', function () {
                return $this->resource->table->table_number;
            }),
            'total_price' => (int) $this->resource->total_price,
            'payment_status' => $this->resource->payment_status,
            'status' => $this->resource->status,
            'snap_token' => $this->resource->snap_token,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->resource->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
