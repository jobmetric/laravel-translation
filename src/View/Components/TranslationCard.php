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
        public Collection|array|null $items = null,
        public array $values = [],
        public string $transScope = '',
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
            if (is_array($this->items)){
                return $this->view('translation::components.multi-translation-array-card', $data);
            }

            return $this->view('translation::components.multi-translation-card', $data);
        }

        if (is_array($this->items)){
            return $this->view('translation::components.translation-array-card', $data);
        }

        return $this->view('translation::components.translation-card', $data);
    }

}
