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
    ],

    "components" => [
        "translation_card" => [
            "title" => "اطلاعات عمومی",
            "multi_title" => "اطلاعات عمومی در زبان :language",
            'tabs' => [
                "basic_info" => "اطلاعات پایه",
                "seo" => "تنظیمات سئو",
            ],
            "fields" => [
                "name" => [
                    "label" => "نام",
                    "info" => "نام آیتم",
                    "placeholder" => "نام آیتم را وارد کنید.",
                ],
                "meta_title" => [
                    "label" => "عنوان متا",
                    "info" => "یک عنوان متا تگ تنظیم کنید. توصیه می شود کلمات کلیدی ساده و دقیق باشند.",
                    "placeholder" => "عنوان متا تگ را وارد کنید.",
                ],
                "meta_description" => [
                    "label" => "توضیحات متا",
                    "info" => "برای افزایش رتبه سئو، یک توضیحات متا تگ تنظیم کنید.",
                    "placeholder" => "توضیحات متا تگ را وارد کنید.",
                ],
                "meta_keywords" => [
                    "label" => "کلمات کلیدی متا",
                    "info" => "فهرستی از کلمات کلیدی که این آیتم مربوط به آن است تنظیم کنید. کلمات کلیدی را با اضافه کردن <code>,</code> بین هر کلمه کلیدی جدا کنید.",
                    "placeholder" => "کلمات کلیدی متا تگ را وارد کنید.",
                ],
            ],
        ],
    ],

    "modals" => [
        "translation_list" => [
            "title" => "ویرایش ترجمه در زبان: {language}",
        ],
    ],

];
