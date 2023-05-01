<?php

namespace JobMetric\Translation\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use JobMetric\Metadata\Traits\HasMetadata;

class Translation extends Model
{
    use HasFactory, HasMetadata;

    protected $fillable = [
        'translatable_id',
        'translatable_type',
        'locale',
        'title'
    ];

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
