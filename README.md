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

# Laravel Translation

**Build Multilingual Applications. Effortlessly.**

Laravel Translation simplifies the management of multilingual content within Laravel applications. Stop managing translations manually and start building truly multilingual applications with ease. It offers dynamic translation storage, retrieval, and updates, seamlessly integrating with your models through the `HasTranslation` trait. This is where powerful multilingual support meets developer-friendly simplicity—giving you complete control over translation management without the complexity.

## Why Laravel Translation?

### Seamless Model Integration

Integrate translations directly with your Eloquent models through a simple trait. No complex setup, no additional services—just add the trait and define which fields should be translatable. Your models become multilingual instantly.

### Flexible Field Control

You have complete control over which fields can be translated. Whitelist specific fields or allow all fields with a simple configuration. This flexibility ensures you only translate what needs to be translated, keeping your database clean and efficient.

### Version History Built-In

Track every change to your translations with optional versioning. See what changed, when it changed, and restore previous versions if needed. Perfect for content management systems where translation history matters.

### Powerful Query Capabilities

Search and filter your models based on translated content. Find products by their translated names, filter posts by translated titles, and build multilingual search functionality with ease.

## What is Translation Management?

Translation management is the process of storing and retrieving content in multiple languages. In a traditional Laravel application, you might store translations in language files or use separate columns for each language. Laravel Translation takes a different approach:

- **Database-Driven**: Translations are stored in the database, making them dynamic and manageable through your application
- **Per-Field Translations**: Each field can have different translations for different locales
- **Version Control**: Track changes to translations over time
- **Query Support**: Search and filter by translated content directly in your queries

Consider a blog post that needs to be available in multiple languages. With Laravel Translation, you can store the title, summary, and body in different locales, retrieve the appropriate translation based on the user's locale, track version history if content changes over time, and search for posts by their translated titles or content. The power of translation management lies not only in storing multiple language versions but also in making them easily accessible, searchable, and manageable throughout your application.

## What Awaits You?

By adopting Laravel Translation, you will:

- **Build truly multilingual applications** - Support as many languages as you need
- **Simplify translation management** - No more complex translation logic in your code
- **Improve content management** - Version history and easy updates
- **Enhance user experience** - Deliver content in users' preferred languages
- **Scale effortlessly** - Handle translations for thousands of models and fields
- **Maintain clean code** - Simple, intuitive API that follows Laravel conventions

## Quick Start

Install Laravel Translation via Composer:

```bash
composer require jobmetric/laravel-translation
```

## Documentation

Ready to transform your Laravel applications? Our comprehensive documentation is your gateway to mastering Laravel Translation:

**[📚 Read Full Documentation →](https://jobmetric.github.io/packages/laravel-translation/)**

The documentation includes:

- **Getting Started** - Quick introduction and installation guide
- **HasTranslation** - Integrate translations into your models
- **Requests** - Complete API reference for TranslationArrayRequest, MultiTranslationArrayRequest, TranslationTypeObjectRequest, and MultiTranslationTypeObjectRequest
- **Resources** - TranslationResource and TranslationCollectionResource for API responses
- **Validation Rules** - TranslationFieldExistRule for ensuring translation uniqueness
- **Events** - Hook into translation lifecycle
- **Real-World Examples** - See how it works in practice

## Contributing

Thank you for participating in `laravel-translation`. A contribution guide can be found [here](CONTRIBUTING.md).

## License

The `laravel-translation` is open-sourced software licensed under the MIT license. See [License File](LICENCE.md) for more information.
