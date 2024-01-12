<?php

namespace JobMetric\Translation\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * @property mixed translations
 */
class TranslationCollectionResource extends JsonResource
{
    private ?string $locale = null;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray(Request $request): array|Arrayable|JsonSerializable
    {
        $query = $this->translations;

        if ($this->locale) {
            return $query->where('locale', $this->locale)->pluck('value', 'key')->all();
        }

        return $query->groupBy('locale')->map(function ($translations) {
            return $translations->pluck('value', 'key');
        });
    }

    /**
     * add locale
     *
     * @param string $locale
     * @return $this
     */
    public function withLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }
}
