<?php

namespace JobMetric\Translation\Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JobMetric\Translation\Tests\Stubs\Factories\PostFactory;
use JobMetric\Translation\HasTranslation;

/**
 * @property int $id
 * @property string $slug
 *
 * @method static create(string[] $array)
 */
class Post extends Model
{
    use HasFactory, HasTranslation;

    public $timestamps = false;

    protected $fillable = [
        'slug'
    ];

    protected $casts = [
        'slug' => 'string'
    ];

    protected static function newFactory(): PostFactory
    {
        return PostFactory::new();
    }
}
