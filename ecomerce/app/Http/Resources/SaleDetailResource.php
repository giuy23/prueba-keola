<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sub_total' => $this->sub_total,
            'name' => $this->product->name,
            'price' => $this->product->price,
            'quantity' => $this->quantity,
            'quantity_max' => $this->product->quantity + $this->quantity,
            'image' => $this->product->image->url ?? $this->product->image,
        ];
    }
}
