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
 * @property mixed field
 * @property mixed value
 * @property mixed version
 * @property mixed created_at
 * @property mixed updated_at
 * @property mixed deleted_at
 */
class TranslationResource extends JsonResource
{
    /**
     * Whether to include version in the output.
     *
     * @var bool
     */
    private bool $includeVersion = true;

    /**
     * Whether to include timestamps in the output.
     *
     * @var bool
     */
    private bool $includeTimestamps = false;

    /**
     * Whether to include deleted_at (soft-deletion) in the output.
     *
     * @var bool
     */
    private bool $includeDeletedAt = false;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray(Request $request): array|Arrayable|JsonSerializable
    {
        if ($this->resource === null) {
            return [];
        }

        $data = [
            'translatable_type' => $this->translatable_type,
            'translatable_id' => $this->translatable_id,
            'locale' => $this->locale,
            'field' => $this->field,
            'value' => $this->value,
        ];

        if ($this->includeVersion && isset($this->version)) {
            $data['version'] = (int)$this->version;
        }

        if ($this->includeTimestamps) {
            $data['created_at'] = $this->created_at?->toDateTimeString();
            $data['updated_at'] = $this->updated_at?->toDateTimeString();
        }

        if ($this->includeDeletedAt) {
            $data['deleted_at'] = $this->deleted_at?->toDateTimeString();
        }

        return $data;
    }

    /**
     * Toggle including version in the output.
     *
     * @param bool $with
     *
     * @return $this
     */
    public function withVersion(bool $with = true): self
    {
        $this->includeVersion = $with;

        return $this;
    }

    /**
     * Toggle including timestamps in the output.
     *
     * @param bool $with
     *
     * @return $this
     */
    public function withTimestamps(bool $with = true): self
    {
        $this->includeTimestamps = $with;

        return $this;
    }

    /**
     * Toggle including deleted_at in the output.
     *
     * @param bool $with
     *
     * @return $this
     */
    public function withDeletedAt(bool $with = true): self
    {
        $this->includeDeletedAt = $with;

        return $this;
    }
}
