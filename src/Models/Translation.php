<?php

namespace JobMetric\Translation\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * JobMetric\Category\Models\Category
 *
 * @property int $translatable_id
 * @property string $translatable_type
 * @property string $locale
 * @property string $key
 * @property string $value
 *
 * @method static find(int $int)
 */
class Translation extends Model
{
    use HasFactory;

    protected $fillable = [
        'translatable_id',
        'translatable_type',
        'locale',
        'key',
        'value'
    ];

    public function getTable()
    {
        return config('translation.tables.translation', parent::getTable());
    }

    /**
     * translatable relationship
     *
     * @return MorphTo
     */
    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope locale.
     *
     * @param Builder $query
     * @param string|null $locale
     *
     * @return Builder
     */
    public function scopeLocale(Builder $query, string $locale = null): Builder
    {
        if(is_null($locale)) {
            $locale = app()->getLocale();
        }

        return $query->where('locale', $locale);
    }

    /**
     * Scope key.
     *
     * @param Builder $query
     * @param string $key
     *
     * @return Builder
     */
    public function scopeKey(Builder $query, string $key): Builder
    {
        return $query->where('key', $key);
    }
}
