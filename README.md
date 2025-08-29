[contributors-shield]: https://img.shields.io/github/contributors/jobmetric/laravel-translation.svg?style=for-the-badge
[contributors-url]: https://github.com/jobmetric/laravel-translation/graphs/contributors
[forks-shield]: https://img.shields.io/github/forks/jobmetric/laravel-translation.svg?style=for-the-badge&label=Fork
[forks-url]: https://github.com/jobmetric/laravel-translation/network/members
[stars-shield]: https://img.shields.io/github/stars/jobmetric/laravel-translation.svg?style=for-the-badge
[stars-url]: https://github.com/jobmetric/laravel-translation/stargazers
[license-shield]: https://img.shields.io/github/license/jobmetric/laravel-translation.svg?style=for-the-badge
[license-url]: https://github.com/jobmetric/laravel-translation/blob/master/LICENCE.md
[linkedin-shield]: https://img.shields.io/badge/-LinkedIn-blue.svg?style=for-the-badge&logo=linkedin&colorB=555
[linkedin-url]: https://linkedin.com/in/majidmohammadian

[![Contributors][contributors-shield]][contributors-url]
[![Forks][forks-shield]][forks-url]
[![Stargazers][stars-shield]][stars-url]
[![MIT License][license-shield]][license-url]
[![LinkedIn][linkedin-shield]][linkedin-url]

# Translation for Laravel

Laravel-Translation is a powerful package that simplifies the management of multilingual content within Laravel applications. It offers dynamic translation storage, retrieval, and updates, seamlessly integrating with your models through the HasTranslation trait. The core features include:

> **Package:** `jobmetric/laravel-translation`  
> **PHP:** 8.1+ (8.2+ recommended) · **Laravel:** 9/10/11  
> **Provider:** `JobMetric\Translation\TranslationServiceProvider`

## Highlights

🔧 **Custom Validation:** Ensures translation data integrity with flexible rules and error handling.

🌟 **Model Integration:** Supports multi-language attributes with versioning, soft deletes, and event-driven updates.

🗃️ **API Resources:** Provides structured serialization for translation data, facilitating API responses.

⚙️ **Extensible Architecture:** Includes custom exceptions, event handling, and dynamic translation management for scalable localization.

🔍 **Query Support:** Enables scope queries and relationship management for efficient multilingual data handling.

🗣️ **Language Files:** Centralized language files for consistent, maintainable multilingual user communication.

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

## Quickstart

```php
use Illuminate\Database\Eloquent\Model;
use JobMetric\Translation\HasTranslation;

class Post extends Model
{
    use HasTranslation;

    /**
     * @var array<int, string>
     */
    protected array $translatables = ['title', 'summary', 'body'];
    
    /**
     * @var bool
     */
    protected bool $translationVersioning = true;
}

// Create & attach translations
$post = Post::create(['status' => 'published']);

$post->translate('en', ['title' => 'Hello', 'body' => 'Welcome']);
$post->translate('fa', ['title' => 'سلام', 'body' => 'خوش آمدید']);

OR

$post = Post::create([
    'status' => 'published'
    'translations' => [
        'en' => ['title' => 'Hello', 'body' => 'Welcome'],
        'fa' => ['title' => 'سلام', 'body' => 'خوش آمدید'],
    ],
]);
// Read
$title = $post->getTranslation('title', 'fa');   // 'سلام'
```

👉 Dive deeper in **/docs**:

- [Main Trait - HasTranslation](https://github.com/jobmetric/laravel-translation/blob/master/dosc/HasTranslation.md)
- [Rule TranslationFieldExist](https://github.com/jobmetric/laravel-translation/blob/master/dosc/TranslationFieldExistRule.md)
- [Events](https://github.com/jobmetric/laravel-translation/blob/master/dosc/Events.md)
- [Resource - TranslationCollection](https://github.com/jobmetric/laravel-translation/blob/master/dosc/TranslationCollectionResource.md)
- [Resource - Translation](https://github.com/jobmetric/laravel-translation/blob/master/dosc/TranslationResource.md)
- [Request - MultiTranslationArray](https://github.com/jobmetric/laravel-translation/blob/master/dosc/MultiTranslationArrayRequest.md)
- [Request - MultiTranslationTypeObject](https://github.com/jobmetric/laravel-translation/blob/master/dosc/MultiTranslationTypeObjectRequest.md)
- [Request - TranslationArray](https://github.com/jobmetric/laravel-translation/blob/master/dosc/TranslationArrayRequest.md)
- [Request - TranslationTypeObject](https://github.com/jobmetric/laravel-translation/blob/master/dosc/TranslationTypeObjectRequest.md)


## Contributing

Thank you for considering contributing to the Laravel Translation! The contribution guide can be found in the [CONTRIBUTING.md](https://github.com/jobmetric/laravel-translation/blob/master/CONTRIBUTING.md).

## License

The MIT License (MIT). Please see [License File](https://github.com/jobmetric/laravel-translation/blob/master/LICENCE.md) for more information.
