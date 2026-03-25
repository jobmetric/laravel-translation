<?php

namespace JobMetric\Translation\Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Model;
use JobMetric\Translation\HasTranslation;

class RequestablePost extends Model
{
    use HasTranslation;

    protected $table = 'posts';

    protected $fillable = [
        'slug',
        'translation',
    ];

    /**
     * Compatibility method used by TranslationArrayRequest.
     *
     * @return array<int, string>
     */
    public function translationAllowFields(): array
    {
        return [
            'name',
            'summary',
        ];
    }
}
