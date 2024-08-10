<?php

namespace JobMetric\Translation\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * @property mixed translatable_type
 * @property mixed translatable_id
 * @property mixed locale
 * @property mixed key
 * @property mixed value
 */
class TranslationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray(Request $request): array|Arrayable|JsonSerializable
    {
        if (is_null($this->resource)) {
            return [];
        }

        return [
            'translatable_type' => $this->translatable_type,
            'translatable_id' => $this->translatable_id,
            'locale' => $this->locale,
            'key' => $this->key,
            'value' => $this->value
        ];
    }
}
