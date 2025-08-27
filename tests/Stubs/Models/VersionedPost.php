<?php

namespace JobMetric\Translation\Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use JobMetric\Translation\HasTranslation;

/**
 * Class VersionedPost
 *
 * Host model enabling versioning to test history behavior.
 * Allowed fields: ['title', 'summary'].
 *
 * @property int $id
 * @property string|null $slug
 */
class VersionedPost extends Model
{
    use HasTranslation, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'posts';

    /**
     * @var array<int, string>
     */
    protected array $translatables = ['title', 'summary'];

    /**
     * @var bool
     */
    protected bool $translationVersioning = true;

    /**
     * @var array<int, string>
     */
    protected $fillable = ['slug', 'translation'];
}
