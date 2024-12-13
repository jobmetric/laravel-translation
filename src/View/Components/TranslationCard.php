<?php

namespace JobMetric\Translation\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use JobMetric\Language\Facades\Language;
use Throwable;

class TranslationCard extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public Collection|null $items = null,
        public array $values = [],
        public bool $multiple = false,
    )
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @throws Throwable
     */
    public function render(): View|Closure|string
    {
        $data = [];

        if ($this->multiple) {
            $data['languages'] = Language::all();
        } else {
            $data['locale'] = app()->getLocale();
        }

        if ($this->multiple) {
            return $this->view('translation::components.multi-translation-card', $data);
        }

        return $this->view('translation::components.translation-card', $data);
    }

}
