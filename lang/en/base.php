<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Base Translation Language Lines - EN
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during Translation for
    | various messages that we need to display to the user.
    |
    */

    "rule" => [
        "exist" => "The :field field already exists.",
        "default_field" => "Name",
    ],

    "exceptions" => [
        "model_not_use_trait" => "Model :model not use JobMetric\Translation\HasTranslation Trait!",
        "disallow_field" => "The model :model does not allow the field or fields :field to be translated.",
    ],

    "entity_names" => [
        "translation" => "Translation",
    ],

    'events' => [
        'translation_stored' => [
            'title' => 'Translation Stored',
            'description' => 'This event is triggered when a translation is stored.',
        ],

        'translation_forgotten' => [
            'title' => 'Translation Forgotten',
            'description' => 'This event is triggered when a translation is forgotten.',
        ],
    ],

];
