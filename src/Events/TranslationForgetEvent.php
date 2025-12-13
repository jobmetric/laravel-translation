<?php

namespace JobMetric\Translation\Events;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;
use JobMetric\Translation\Models\Translation;

readonly class TranslationForgetEvent implements DomainEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public Translation $translation
    ) {
    }

    /**
     * Returns the stable technical key for the domain event.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'translation.forgotten';
    }

    /**
     * Returns the full metadata definition for this domain event.
     *
     * @return DomainEventDefinition
     */
    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'translation::base.entity_names.translation', 'translation::base.events.translation_forgotten.title', 'translation::base.events.translation_forgotten.description', 'fas fa-trash', [
            'translation',
            'forget',
            'localization',
        ]);
    }
}
