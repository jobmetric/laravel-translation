<?php

namespace JobMetric\Translation\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use HasFactory;

    protected $fillable = [
        'translatable_id',
        'translatable_type',
        'locale',
        'title',
        'data'
    ];

    protected $casts = [
        'data' => 'array'
    ];
}
