<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
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
            'name' => $this->resource->name,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'price' => (int) $this->resource->price,
            'description' => $this->resource->description,
            'image' => $this->resource->image ? Storage::url($this->resource->image) : null,
            'is_available' => (bool) $this->resource->is_available,
            'created_at' => $this->resource->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->resource->created_at->format('Y-m-d H:i:s')
        ];
    }
}
