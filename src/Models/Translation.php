<?php

namespace JobMetric\Translation\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Translation
 *
 * Represents a localized translation entry associated with any Eloquent model
 * via a polymorphic relation. Each row stores a translated value for a specific
 * model attribute (the "field") and locale, optionally versioned.
 *
 * @package JobMetric\Translation
 *
 * @property int $id The primary identifier of the translation row.
 * @property int|null $translatable_id The ID of the related model instance.
 * @property string|null $translatable_type The class name (morph class) of the related model.
 * @property string $locale The locale code (e.g., 'en', 'fa').
 * @property string $field The model attribute key being translated (e.g., 'title').
 * @property string|null $value The translated content (maybe null).
 * @property int $version The translation version sequence (>= 1).
 * @property Carbon|null $deleted_at Soft delete timestamp if the row is deleted.
 * @property Carbon|null $created_at Row creation timestamp.
 * @property Carbon|null $updated_at Row last update timestamp.
 *
 * @property-read Model|MorphTo $translatable The related Eloquent model (polymorphic).
 *
 * @method static Builder|Translation locale(?string $locale = null) Scope by locale.
 * @method static Builder|Translation field(string $field) Scope by field name.
 * @method static Builder|Translation version(int $version) Scope by version number.
 *
 * @method static Builder|Translation whereTranslatableType(string $translatable_type)
 * @method static Builder|Translation whereTranslatableId(int $translatable_id)
 * @method static Builder|Translation whereLocale(string $locale)
 * @method static Builder|Translation whereField(string $field)
 * @method static Builder|Translation whereValue(?string $value)
 * @method static Builder|Translation whereVersion(int $version)
 * @method static Builder|Translation whereDeletedAt(?Carbon $deleted_at)
 * @method static Builder|Translation whereCreatedAt(?Carbon $created_at)
 * @method static Builder|Translation whereUpdatedAt(?Carbon $updated_at)
 */
class Translation extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Mass-assignable attributes that define a translation payload.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'translatable_id',
        'translatable_type',
        'locale',
        'field',
        'value',
        'version',
    ];

    /**
     * Attribute type casting for consistency and query safety.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'translatable_id'   => 'integer',
        'translatable_type' => 'string',
        'locale'            => 'string',
        'field'             => 'string',
        'value'             => 'string',
        'version'           => 'integer',
        'deleted_at'        => 'datetime',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    /**
     * Resolve the table name from configuration to keep package portable.
     *
     * @return string
     */
    public function getTable(): string
    {
        return config('translation.tables.translation', parent::getTable());
    }

    /**
     * Polymorphic relationship to the parent translatable model.
     *
     * @return MorphTo
     */
    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope query by locale. Defaults to the application's current locale when omitted.
     *
     * @param Builder $query
     * @param string|null $locale
     *
     * @return Builder
     */
    public function scopeLocale(Builder $query, ?string $locale = null): Builder
    {
        $locale = $locale ?? app()->getLocale();

        return $query->where('locale', $locale);
    }

    /**
     * Scope query by translated field name (e.g., 'title', 'body').
     *
     * @param Builder $query
     * @param string $field
     *
     * @return Builder
     */
    public function scopeField(Builder $query, string $field): Builder
    {
        return $query->where('field', $field);
    }

    /**
     * Scope query by version number.
     *
     * @param Builder $query
     * @param int $version
     *
     * @return Builder
     */
    public function scopeVersion(Builder $query, int $version): Builder
    {
        return $query->where('version', $version);
    }
}
