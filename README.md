# Translation for laravel

This is a package for translating the contents of different Laravel projects.

## Install via composer

Run the following command to pull in the latest version:
```bash
composer require jobmetric/laravel-translation
```

## Documentation

Undergoing continuous enhancements, this package evolves each day, integrating an array of diverse features. It stands as an indispensable asset for enthusiasts of Laravel, offering a seamless way to harmonize their projects with translation database models.

In this package, you can employ it seamlessly with any model requiring database translation.

Now, let's delve into the core functionality.

>#### Before doing anything, you must migrate after installing the package by composer.

```bash
php artisan migrate
```

Meet the `HasTranslation` class, meticulously designed for integration into your model. This class automates essential tasks, ensuring a streamlined process for:

In the first step, you need to connect this class to your main model.

```php
use JobMetric\Translation\HasTranslation;

class Post extends Model
{
    use HasTranslation;
}
```

When you add this class, you will have to implement `TranslationContract` to your model.

```php
use JobMetric\Translation\Contracts\TranslationContract;

class Post extends Model implements TranslationContract
{
    use HasTranslation;
}
```

Now you have to use the translationAllowFields function and you have to add it to your model.

```php
use JobMetric\Translation\Contracts\TranslationContract;

class Post extends Model implements TranslationContract
{
    use HasTranslation;

    public function translationAllowFields(): array
    {
        return [
            'title',
            'body',
        ];
    }
}
```

> This function is for you to declare what translation fields you need for this model, and you should return them here as an `array`.

## How is it used?

Now, you can use the `HasTranslation` class to translate your model. The following example demonstrates how to create a new post with translations:

```php

$post = Post::create([
    'status' => 'published',
]);

$post->translate('en', [
    'title' => 'Post title',
    'body' => 'Post body',
]);

$post->translate('de', [
    'title' => 'Post Titel',
    'body' => 'Post Inhalt',
]);

$post->translate('fr', [
    'title' => 'Titre de la publication',
    'body' => 'Corps de poste',
]);

$post->translate('fa', [
    'title' => 'عنوان پست',
    'body' => 'متن پست',
]);
```

> You could also do this inside a `foreach`, it was more for show off.

You can also use the `translate` method to update the translations:

```php

$post->translate('de', [
    'title' => 'Post Titel',
    'body' => 'Post Inhalt',
]);
```

### Now we go to the functions that we have added to our model.

### `translation`

translation has one relationship

### `translations`

translation has many relationships

### `translationTo`

scope locale for select translation relationship

```php
Post::translationTo('en')->get();

// or

Post::where('status', 'published')->translationTo('en')->get();
```

### `translationsTo`

scope locale for select translations relationship

```php
Post::translationsTo('en')->get();

// or

Post::where('status', 'published')->translationsTo('en')->get();
```

### `translate`

store translates for the model

```php
$post->translate('en', [
    'title' => 'Post title',
    'body' => 'Post body',
]);
```

### `withTranslation`

load translation after model loaded

```php
$post = Post::query()->where('id', 1)->first()->withTranslation('en', 'title');
```

### `withTranslations`

load translations after model loaded

```php
$post = Post::query()->where('id', 1)->first()->withTranslations();
```

### `hasTranslationField`

check model has translation field

```php
$post->hasTranslationField('title');
```

### `getTranslation`

get translation for the model

```php
$post->getTranslation('title');
```

### `getTranslations`

get translations for the model

```php
$post->getTranslations();
```

### `forgetTranslation`

forget translation for the model

```php
$post->forgetTranslation('title', 'en');
```

### `forgetTranslations`

forget translations for the model

```php
$post->forgetTranslations('en');
```

## Events

This package contains several events for which you can write a listener as follows

| Event                    | Description                                            |
|--------------------------|--------------------------------------------------------|
| `TranslationStoredEvent` | This event is called after storing the translation.    |
| `TranslationForgetEvent` | This event is called after forgetting the translation. |

## Contributing

Thank you for considering contributing to the Laravel Translation! The contribution guide can be found in the [CONTRIBUTING.md](https://github.com/jobmetric/laravel-translation/blob/master/CONTRIBUTING.md).

## License

The MIT License (MIT). Please see [License File](https://github.com/jobmetric/laravel-translation/blob/master/LICENCE.md) for more information.
