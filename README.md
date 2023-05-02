# Translation for laravel

This is a package for translating the contents of different Laravel projects.

## Install via composer

Run the following command to pull in the latest version:
```bash
composer require jobmetric/translation
```

### Add service provider

Add the service provider to the providers array in the config/app.php config file as follows:

```php
'providers' => [

    ...

    JobMetric\Translation\Providers\TranslationServiceProvider::class,
]
```

### Publish the config
Copy the `config` file from `vendor/jobmetric/translation/config/config.php` to `config` folder of your Laravel application and rename it to `translation.php`

Run the following command to publish the package config file:

```bash
php artisan vendor:publish --provider="JobMetric\Translation\Providers\TranslationServiceProvider" --tag="translation-config"
```

You should now have a `config/translation.php` file that allows you to configure the basics of this package.

### Publish Migrations

You need to publish the migration to create the `translations` table:

```php
php artisan vendor:publish --provider="JobMetric\Translation\Providers\TranslationServiceProvider" --tag="translation-migrations"
```

After that, you need to run migrations.

```php
php artisan migrate
```

## Documentation
