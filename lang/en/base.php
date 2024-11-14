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
    ],

    "components" => [
        "translation_card" => [
            "title" => "General Information",
            "multi_title" => "General Information in :language Language",
            "tabs" => [
                "basic_info" => "Basic Information",
                "seo" => "SEO Settings",
            ],
            "fields" => [
                "name" => [
                    "label" => "Name",
                    "info" => "Name of the item",
                    "placeholder" => "Enter Item Name",
                ],
                "meta_title" => [
                    "label" => "Meta Title",
                    "info" => "Set a meta title tag. It is recommended to be simple and precise keywords.",
                    "placeholder" => "Enter Meta Title",
                ],
                "meta_description" => [
                    "label" => "Meta Description",
                    "info" => "Set a meta description tag to increase SEO rank.",
                    "placeholder" => "Enter Meta Description",
                ],
                "meta_keywords" => [
                    "label" => "Meta Keywords",
                    "info" => "Set a list of keywords that this item is related to. Separate keywords by adding <code>,</code> between each keyword.",
                    "placeholder" => "Enter Meta Keywords",
                ],
            ],
        ],
    ],

    "modals" => [
        "translation_list" => [
            "title" => "Edit Translation in {language} Language",
        ],
    ],

];
