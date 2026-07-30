<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ElementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->uuid,
            'type' => $this->type,
            'name' => $this->name,
            'order' => $this->order,
            'column_size' => $this->column_size,
            'parent_id' => $this->parent_id,
            'settings' => $this->settings,
            'content' => $this->content,
            'styles' => $this->styles,
            'responsive_settings' => $this->responsive_settings,
            'animation' => $this->animation,
            'effects' => $this->effects,
            'css_classes' => $this->css_classes,
            'css_id' => $this->css_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        if ($this->relationLoaded('children') && $this->children->isNotEmpty()) {
            $data['children'] = ElementResource::collection($this->children);
        }

        return $data;
    }
}
