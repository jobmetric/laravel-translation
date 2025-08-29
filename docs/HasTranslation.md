[Back To Readme.md](https://github.com/jobmetric/laravel-translation/blob/master/README.md)

# HasTranslation Trait

The `HasTranslation` trait provides per-field translations for your Eloquent models. It supports whitelisting specific fields for translation, optional versioning to keep a history of changes, convenient query scopes, and helper methods for creating, reading, and managing translations.

---

## Setup in a Model

To enable translations for a model, simply add the `HasTranslation` trait.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use JobMetric\Translation\HasTranslation;

class Post extends Model
{
    use SoftDeletes;
    use HasTranslation;

    /**
     * Only these fields can be translated.
     * Use ['*'] to allow all fields.
     */
    protected array $translatables = ['title', 'summary'];

    /**
     * Enable versioning to keep history of translations.
     */
    protected bool $translationVersioning = true;

    protected $fillable = ['slug', 'body', 'translation'];
}
```

---

## Creating with Translations

You can pass translations using the virtual `translation` attribute during `create` or `update`.

```php
$post = Post::create([
    'slug' => 'hello-world',
    'translation' => [
        'en' => [
            'title'   => 'Hello World',
            'summary' => 'My first post',
        ],
        'fa' => [
            'title'   => 'سلام دنیا',
            'summary' => 'اولین پست من',
        ],
    ],
]);

echo $post->getTranslation('title', 'fa');
```

**Output:**
```
سلام دنیا
```

---

## Updating Translations

```php
$post->update([
    'translation' => [
        'en' => [
            'summary' => 'Updated English summary',
        ],
    ],
]);

echo $post->getTranslation('summary', 'en');
```

**Output:**
```
Updated English summary
```

---

## Setting a Single Field

```php
$post->setTranslation('fa', 'title', 'تیتر جدید');

echo $post->getTranslation('title', 'fa');
```

**Output:**
```
تیتر جدید
```

---

## Batch Translations

```php
$post->translateBatch([
    'en' => [
        'title' => 'Batch Title',
        'summary' => 'Batch Summary',
    ],
    'fa' => [
        'title' => 'عنوان گروهی',
        'summary' => 'خلاصه گروهی',
    ],
]);

print_r($post->getTranslations());
```

**Output:**
```
Array
(
    [en] => Array
        (
            [title] => Batch Title
            [summary] => Batch Summary
        )

    [fa] => Array
        (
            [title] => عنوان گروهی
            [summary] => خلاصه گروهی
        )
)
```

---

## Versioning Example

When versioning is enabled (`protected bool $translationVersioning = true`), every update creates a new version and soft-deletes the previous one.

```php
$post->translate('en', ['title' => 'Version 1']);
$post->translate('en', ['title' => 'Version 2']);

echo $post->getTranslation('title', 'en');        // latest active
echo $post->getTranslation('title', 'en', 1);     // exact version 1
echo $post->latestTranslationVersion('title', 'en');
```

**Output:**
```
Version 2
Version 1
2
```

---

## Translation History

```php
$history = $post->getTranslationVersions('title', 'en');

print_r($history);
```

**Output:**
```
Array
(
    [0] => Array
        (
            [version] => 2
            [value] => Version 2
            [deleted_at] => 
        )

    [1] => Array
        (
            [version] => 1
            [value] => Version 1
            [deleted_at] => 2025-08-10 12:34:56
        )
)
```

---

## Forgetting Translations

```php
$post->forgetTranslation('summary', 'en');

var_dump($post->hasTranslationField('summary', 'en'));
```

**Output:**
```
bool(false)
```

---

## Getting All Translations

```php
print_r($post->getTranslations('fa'));
```

**Output:**
```
Array
(
    [title] => عنوان گروهی
    [summary] => خلاصه گروهی
)
```

Or without locale:

```php
print_r($post->getTranslations());
```

**Output:**
```
Array
(
    [en] => Array
        (
            [title] => Batch Title
            [summary] => Batch Summary
        )

    [fa] => Array
        (
            [title] => عنوان گروهی
            [summary] => خلاصه گروهی
        )
)
```

---

## Query Scopes

### whereTranslationEquals

```php
$posts = Post::whereTranslationEquals('title', 'Hello World', 'en')->get();

foreach ($posts as $post) {
    echo $post->getTranslation('title', 'en') . PHP_EOL;
}
```

**Output:**
```
Hello World
```

---

### whereTranslationLike

```php
$posts = Post::whereTranslationLike('summary', 'Batch', 'en')->get();

foreach ($posts as $post) {
    echo $post->getTranslation('summary', 'en') . PHP_EOL;
}
```

**Output:**
```
Batch Summary
```

---

### searchTranslation

```php
$posts = Post::searchTranslation('summary', 'خلاصه', 'fa')->get();

foreach ($posts as $post) {
    echo $post->getTranslation('summary', 'fa') . PHP_EOL;
}
```

**Output:**
```
خلاصه گروهی
```

---

## Managing Translatable Fields

```php
print_r($post->getTranslatableFields());

$post->mergeTranslatables(['seo']);
print_r($post->getTranslatableFields());

$post->removeTranslatableField('summary');
print_r($post->getTranslatableFields());

echo $post->translatablesAllowAll() ? 'All allowed' : 'Restricted';
```

**Output:**
```
Array
(
    [0] => title
    [1] => summary
)

Array
(
    [0] => title
    [1] => summary
    [2] => seo
)

Array
(
    [0] => title
    [1] => seo
)

Restricted
```

---

# Summary

The `HasTranslation` trait makes it easy to manage multilingual content in your models:
- Simple virtual `translation` input for create/update.
- Fine-grained control over which fields are translatable.
- Optional versioning with history tracking.
- Helper methods for reading, updating, and removing translations.
- Query scopes for efficient searching.

This allows you to keep your models clean, your translations structured, and your application fully multilingual.

[Next To Rule TranslationFieldExist](https://github.com/jobmetric/laravel-translation/blob/master/dosc/TranslationFieldExistRule.md)
