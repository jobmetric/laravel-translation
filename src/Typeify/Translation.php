<?php

namespace JobMetric\Translation\Typeify;

use JobMetric\CustomField\CustomField;

/**
 * Class Translation
 *
 * @package JobMetric\Translation
 */
class Translation
{
    /**
     * The custom field instance.
     *
     * @var CustomField $customField
     */
    public CustomField $customField;

    /**
     * Translation constructor.
     *
     * @param CustomField $customField
     */
    public function __construct(CustomField $customField)
    {
        $this->customField = $customField;
    }
}
