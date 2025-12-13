<?php

namespace JobMetric\Translation\Events;

use Illuminate\Database\Eloquent\Model;
use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;

readonly class TranslationStoredEvent implements DomainEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public Model $model,
        public string $locale,
        public array $data
    ) {
    }

    /**
     * Returns the stable technical key for the domain event.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'translation.stored';
    }

    /**
     * Returns the full metadata definition for this domain event.
     *
     * @return DomainEventDefinition
     */
    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'translation::base.entity_names.translation', 'translation::base.events.translation_stored.title', 'translation::base.events.translation_stored.description', 'fas fa-save', [
            'translation',
            'storage',
            'localization',
        ]);
    }
}
