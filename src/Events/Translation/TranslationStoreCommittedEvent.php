<?php

namespace JobMetric\Translation\Events\Translation;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;

/**
 * Describe a committed Translation created outcome using allowlisted identifiers.
 */
readonly class TranslationStoreCommittedEvent implements DomainEvent
{
    /**
     * Capture identifiers without models, input values or credentials.
     *
     * @param int|string $id
     *
     * @return void
     */
    public function __construct(
        public int|string $id
    ) {
    }

    /**
     * Return the stable key used by automation consumers.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'translation.translation.stored.committed';
    }

    /**
     * Describe the outcome for translated discovery interfaces.
     *
     * @return DomainEventDefinition
     */
    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'translation::automation.translation_stored.group', 'translation::automation.translation_stored.title', 'translation::automation.translation_stored.description', 'fas fa-bolt', ['automation', 'committed', 'translation']);
    }
}
