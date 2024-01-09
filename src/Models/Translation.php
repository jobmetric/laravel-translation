<?php

namespace JobMetric\Translation\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property Model instance
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

    protected static function boot(): void
    {
        parent::boot();
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
}
