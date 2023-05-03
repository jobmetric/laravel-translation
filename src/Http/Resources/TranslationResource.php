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
 * @property mixed $metaable
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

        try {
            $metadata = $this->whenLoaded('metaable', function () use ($request) {
                return MetadataResource::collection($this->metaable)?->toArray($request);
            });
        } catch(Throwable $throwable) {
            $metadata = [];
        }

        return [
            $this->locale => array_merge([
                'title' => $this->title
            ], ...$metadata)
        ];
    }
}
