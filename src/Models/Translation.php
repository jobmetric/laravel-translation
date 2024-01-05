<?php

namespace JobMetric\Translation\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use JobMetric\Metadata\HasMetadata;
use JobMetric\Metadata\Metadata;
use JobMetric\Metadata\MetadataInterface;

/**
 * @property Model instance
 */
class Translation extends Model implements MetadataInterface
{
    use HasFactory, HasMetadata;

    protected $fillable = [
        'translatable_id',
        'translatable_type',
        'locale',
        'title'
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function (Translation $translation) {
            Metadata::delete($translation);
        });
    }

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
     * allow metadata fields.
     *
     * @return array
     */
    public function allowMetadataFields(): array
    {
        return $this->instance?->allowTranslationFields();
    }
}
