[Back To Readme.md](https://github.com/jobmetric/laravel-translation/blob/master/README.md)

# TranslationArrayRequest Trait

The `TranslationArrayRequest` trait composes **validation rules** and **human‑friendly attribute labels** for a `translation` payload. If multiple locales are present under `translation`, rules are generated for **each** locale; otherwise, it falls back to `app()->getLocale()`.

> This document focuses only on request-side validation and attribute labels. No database or tests here. All examples include **expected output** blocks you can compare against during development.

---

## What It Does

- Detects locales from the incoming payload:
    - If `translation` is an array: uses its keys as locales (e.g., `en`, `fa`).
    - Otherwise: uses `app()->getLocale()`.
- For every locale:
    - Adds a container rule: `translation.{locale} => array`
    - Enforces a **primary** translated field (default `name`) as:  
      `required|string + TranslationFieldExistRule` (per-locale uniqueness)
    - Adds optional string rules for all other fields returned by `translationAllowFields()` on the target model.
- Builds attribute labels for each `translation.{locale}.{field}` key using:
    - Special labels for meta fields (`meta_title`, `meta_description`, `meta_keywords`).
    - A custom translation scope containing `{field}` (if provided).
    - A sensible fallback: a title‑cased field name.

---

## Quick Start (Typical FormRequest)

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Translation\Http\Requests\TranslationArrayRequest;

class StorePostRequest extends FormRequest
{
    use TranslationArrayRequest;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'slug' => 'required|string',
        ];

        // Build translation rules based on payload and model contract
        $this->renderTranslationFiled(
            $rules,
            $this->all(),               // request data
            \App\Models\Post::class, // model exposing translationAllowFields()
            'title',                    // primary field
            null,                       // object_id (null on create)
            null,                       // parent_id (optional scope)
            []                          // parent_where (optional constraints)
        );

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [
            'slug' => 'Slug',
        ];

        // Produce readable labels. Pass a scope containing "{field}" if you want templated labels.
        $this->renderTranslationAttribute(
            $attributes,
            $this->all(),
            \App\Models\Post::class,
            null // or e.g. 'validation.attributes.translation_field' with "{field}" placeholder
        );

        return $attributes;
    }
}
```

> **Note:** Method names are `renderTranslationFiled` and `renderTranslationAttribute` (keep them exactly as implemented). The target model must implement `translationAllowFields()`.

---

## Example: Resulting `rules()` Output (Multiple Locales)

Assumptions:
- Incoming payload contains:
  ```json
  {
    "slug": "hello-world",
    "translation": {
      "en": { "title": "Hello", "summary": "First post" },
      "fa": { "title": "سلام",   "summary": "اولین پست" }
    }
  }
  ```
- `App\Models\Post::translationAllowFields()` returns: `['title', 'summary', 'meta_title', 'meta_description']`
- Primary field is `title`

**Code (excerpt):**
```php
$rules = ['slug' => 'required|string'];

$this->renderTranslationFiled(
    $rules,
    $payload,
    \App\Models\Post::class,
    'title',
    null,
    null,
    []
);

return $rules;
```

**Expected Output (`print_r($rules)`):**
```
Array
(
    [slug] => required|string
    [translation] => array
    [translation.en] => array
    [translation.en.title] => Array
        (
            [0] => required
            [1] => string
            [2] => JobMetric\Translation\Rules\TranslationFieldExistRule Object (...)
        )

    [translation.en.summary] => string|nullable|sometimes
    [translation.en.meta_title] => string|nullable|sometimes
    [translation.en.meta_description] => string|nullable|sometimes

    [translation.fa] => array
    [translation.fa.title] => Array
        (
            [0] => required
            [1] => string
            [2] => JobMetric\Translation\Rules\TranslationFieldExistRule Object (...)
        )

    [translation.fa.summary] => string|nullable|sometimes
    [translation.fa.meta_title] => string|nullable|sometimes
    [translation.fa.meta_description] => string|nullable|sometimes
)
```

---

## Example: Resulting `rules()` Output (No Locales Provided)

Assumptions:
- Incoming payload does **not** have `translation` key, or it is not an array.
- `app()->getLocale()` returns `en`.
- `translationAllowFields()` returns `['name', 'description']`
- Primary field is `name` (default).

**Expected Output (excerpt):**
```
Array
(
    [translation] => array
    [translation.en] => array
    [translation.en.name] => Array
        (
            [0] => required
            [1] => string
            [2] => JobMetric\Translation\Rules\TranslationFieldExistRule Object (...)
        )

    [translation.en.description] => string|nullable|sometimes
)
```

---

## Example: Resulting `attributes()` Output (Multiple Locales)

Using the first example payload and fields, with **no** `$trans_scope` provided (so it falls back to title‑cased names).

**Code (excerpt):**
```php
$attributes = ['slug' => 'Slug'];

$this->renderTranslationAttribute(
    $attributes,
    $payload,
    \App\Models\Post::class,
    null
);

return $attributes;
```

**Expected Output (`print_r($attributes)`):**
```
Array
(
    [slug] => Slug
    [translation.en.title] => Title
    [translation.en.summary] => Summary
    [translation.en.meta_title] => Meta title
    [translation.en.meta_description] => Meta description
    [translation.fa.title] => Title
    [translation.fa.summary] => Summary
    [translation.fa.meta_title] => Meta title
    [translation.fa.meta_description] => Meta description
)
```

> When `$trans_scope` is provided (e.g., `'validation.attributes.translation_field'` with `{field}` inside), the `{field}` placeholder is replaced per field and the resulting translation string is used.

---

## Primary Field and Uniqueness

- The **primary** field (default `name`, or your override like `title`) is enforced per locale with:  
  `required|string + TranslationFieldExistRule`.
- All other fields from `translationAllowFields()` are attached as:  
  `string|nullable|sometimes`.

You control the list of fields by implementing `translationAllowFields()` on your model.

---

## End-to-End Minimal Flow

1. Implement `translationAllowFields()` on the target model (e.g., return `['title','summary', ...]`).
2. In your `FormRequest::rules()`, call `renderTranslationFiled($rules, $this->all(), Model::class, 'title', ...)`.
3. In `FormRequest::attributes()`, call `renderTranslationAttribute($attributes, $this->all(), Model::class, $scopeOrNull)`.
4. Done — your validator now understands `translation.{locale}.{field}` for each locale present in the payload (or the app locale).

---

## Summary

`TranslationArrayRequest` makes multi-locale translation validation straightforward when your payload looks like:

```json
{
  "translation": {
    "en": { "title": "...", "summary": "..." },
    "fa": { "title": "...", "summary": "..." }
  }
}
```

It automatically:
- Detects locales from the payload (or uses the app locale)
- Enforces a primary translated field with per-locale uniqueness
- Adds optional string rules for other allowed fields
- Generates readable attribute labels for clean error messages

[Next To Request - TranslationTypeObject](https://github.com/jobmetric/laravel-translation/blob/master/dosc/TranslationTypeObjectRequest.md)
