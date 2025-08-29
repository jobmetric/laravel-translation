[Back To Readme.md](https://github.com/jobmetric/laravel-translation/blob/master/README.md)

# Events

| Event                        | When                                                    |
|-----------------------------|----------------------------------------------------------|
| `TranslationStoredEvent`    | After storing or updating a translation for a locale     |
| `TranslationForgetEvent`    | After forgetting a translation (field or per-locale)     |

## Example listener

```php
// app/Providers/EventServiceProvider.php
protected $listen = [
    JobMetric\Translation\Events\TranslationStoredEvent::class => [
        App\Listeners\InvalidateCaches::class,
    ],
];
```

```php
namespace App\Listeners;

class InvalidateCaches
{
    public function handle($event): void
    {
        // Example: flush a tag for the translatable model
        // Cache::tags([$event->model::class])->flush();
    }
}
```

[Next To Resource - TranslationCollection](https://github.com/jobmetric/laravel-translation/blob/master/dosc/TranslationCollectionResource.md)
