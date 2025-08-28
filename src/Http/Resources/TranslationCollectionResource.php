<?php

namespace JobMetric\Translation\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection as BaseCollection;
use JsonSerializable;

/**
 * @property Model $resource
 */
class TranslationCollectionResource extends JsonResource
{
    /**
     * Optional locale filter.
     *
     * @var string|null
     */
    private ?string $locale = null;

    /**
     * Optional fields filter.
     *
     * @var array<string>|null
     */
    private ?array $fields = null;

    /**
     * Whether to include historical rows (soft-deleted).
     *
     * @var bool
     */
    private bool $withHistory = false;

    /**
     * Whether to include version/status metadata in output.
     *
     * @var bool
     */
    private bool $includeVersion = false;

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray(Request $request): array|Arrayable|JsonSerializable
    {
        // Load rows: active only by default; with history if requested
        $rows = $this->withHistory
            ? $this->resource->translations()->withTrashed()->get()
            : ($this->resource->relationLoaded('translations')
                ? $this->resource->getRelation('translations')
                : $this->resource->translations()->get());

        if ($this->locale) {
            $rows = $rows->where('locale', $this->locale);
        }

        if ($this->fields && $this->fields !== []) {
            $rows = $rows->whereIn('field', $this->fields);
        }

        // Reduce rows to a single, definitive row per field:
        // prefer the latest "active" (deleted_at = null), otherwise the latest by version.
        $reducePerField = function (BaseCollection $collection): BaseCollection {
            return $collection
                ->groupBy('field')
                ->map(function (BaseCollection $grp) {
                    $active = $grp->where('deleted_at', null);
                    if ($active->isNotEmpty()) {
                        return $active->sortByDesc('version')->first();
                    }
                    return $grp->sortByDesc('version')->first();
                });
        };

        if ($this->locale) {
            $reduced = $reducePerField($rows);

            if ($this->includeVersion) {
                return $reduced
                    ->mapWithKeys(function ($t) {
                        return [
                            $t->field => [
                                'value'      => $t->value,
                                'version'    => (int) $t->version,
                                'is_active'  => $t->deleted_at === null,
                                'deleted_at' => $t->deleted_at?->toDateTimeString(),
                            ],
                        ];
                    })
                    ->all();
            }

            return $reduced->mapWithKeys(fn($t) => [$t->field => $t->value])->all();
        }

        // All locales
        $groupedByLocale = $rows->groupBy('locale');

        if ($this->includeVersion) {
            return $groupedByLocale
                ->map(function (BaseCollection $localeRows) use ($reducePerField) {
                    $reduced = $reducePerField($localeRows);

                    return $reduced->mapWithKeys(function ($t) {
                        return [
                            $t->field => [
                                'value'      => $t->value,
                                'version'    => (int) $t->version,
                                'is_active'  => $t->deleted_at === null,
                                'deleted_at' => $t->deleted_at?->toDateTimeString(),
                            ],
                        ];
                    })->all();
                })
                ->all();
        }

        return $groupedByLocale
            ->map(function (BaseCollection $localeRows) use ($reducePerField) {
                $reduced = $reducePerField($localeRows);

                return $reduced->mapWithKeys(fn($t) => [$t->field => $t->value])->all();
            })
            ->all();
    }

    /**
     * Set locale filtering.
     *
     * @param  string  $locale
     * @return $this
     */
    public function withLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Restrict output to specific fields.
     *
     * @param  array<string>  $fields
     * @return $this
     */
    public function onlyFields(array $fields): self
    {
        $this->fields = array_values(array_unique($fields));

        return $this;
    }

    /**
     * Include historical rows (soft-deleted).
     *
     * @param  bool  $with
     * @return $this
     */
    public function withHistory(bool $with = true): self
    {
        $this->withHistory = $with;

        return $this;
    }

    /**
     * Include version/status metadata per row.
     *
     * @param  bool  $with
     * @return $this
     */
    public function includeVersion(bool $with = true): self
    {
        $this->includeVersion = $with;

        return $this;
    }
}
