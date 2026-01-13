<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'product_name' => $this->whenLoaded('product', function () {
                return $this->resource->product->name;
            }),
            'quantity' => $this->resource->quantity,
            'price' => $this->resource->price,
            'notes' => $this->resource->notes,
            'created_at' => $this->resource->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
