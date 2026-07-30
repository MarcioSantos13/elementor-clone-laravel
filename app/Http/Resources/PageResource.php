<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'status' => $this->status,
            'settings' => $this->settings,
            'meta_data' => $this->meta_data,
            'template' => $this->template,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        if ($this->relationLoaded('elements') && $this->elements->isNotEmpty()) {
            $data['elements'] = ElementResource::collection($this->elements);
        }

        return $data;
    }
}
