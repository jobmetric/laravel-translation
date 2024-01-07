<?php

namespace JobMetric\Translation\Http\Resources;

use Throwable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JobMetric\Metadata\Http\Resources\MetadataResource;
use JsonSerializable;

/**
 * @property mixed $locale
 * @property mixed $title
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
    public function toArray($request): array|Arrayable|JsonSerializable
    {
        if(is_null($this->resource)){
            return [];
        }

        return [
            $this->locale => [
                'title' => $this->title
            ]
        ];
    }
}
