<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Base Translation Language Lines - FA
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during Translation for
    | various messages that we need to display to the user.
    |
    */

    "rule" => [
        "exist" => "فیلد :field از قبل وجود دارد.",
        "default_field" => "نام",
    ],

    "exceptions" => [
        "model_not_use_trait" => "مدل :model از Trait مربوط به JobMetric\Translation\HasTranslation استفاده نمی‌کند!",
        "disallow_field" => "مدل :model اجازه ترجمه فیلد یا فیلدهای :field را ندارد.",
    ],

    "entity_names" => [
        "translation" => "ترجمه",
    ],

    'events' => [
        'translation_stored' => [
            'title' => 'ذخیره ترجمه',
            'description' => 'هنگامی که یک ترجمه ذخیره می‌شود، این رویداد فعال می‌شود.',
        ],

        'translation_forgotten' => [
            'title' => 'فراموشی ترجمه',
            'description' => 'هنگامی که یک ترجمه فراموش می‌شود، این رویداد فعال می‌شود.',
        ],
    ],

];
