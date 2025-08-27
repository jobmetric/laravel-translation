<?php

namespace JobMetric\Translation\Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use JobMetric\Translation\HasTranslation;

/**
 * Class RestrictedPost
 *
 * Host model with a strict whitelist to test disallowed field handling.
 * Allowed fields: ['title'] only. No versioning.
 *
 * @property int $id
 * @property string|null $slug
 */
class RestrictedPost extends Model
{
    use HasTranslation, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'posts';

    /**
     * @var array<int, string>
     */
    protected array $translatables = ['title'];

    /**
     * @var bool
     */
    protected bool $translationVersioning = false;

    /**
     * @var array<int, string>
     */
    protected $fillable = ['slug', 'translation'];
}
