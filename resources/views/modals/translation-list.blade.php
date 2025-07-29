<form action="{{ $action }}" method="post" id="object-translation-form">
    @csrf
    <input type="hidden" name="locale" id="modal_translation_locale">
    <input type="hidden" name="translatable_id" id="modal_translation_translatable_id">

    @php
        $hasSeo = false;
        $items->each(function (\JobMetric\Translation\Typeify\Translation $translation) use (&$hasSeo) {
            if (str_contains($translation->customField->params['name'] ?? '', 'meta_title')) {
                $hasSeo = true;
                return false;
            }
        });
    @endphp

    @if($hasSeo)
        <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x fs-6 border-0">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab"
                   href="#modal_translation_tab_general_public">{{ trans('translation::base.components.translation_card.tabs.basic_info') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab"
                   href="#modal_translation_tab_general_seo">{{ trans('translation::base.components.translation_card.tabs.seo') }}</a>
            </li>
        </ul>
    @endif
    <div class="tab-content mt-10">
        <div class="tab-pane fade show active" id="modal_translation_tab_general_public" role="tabpanel">
            @foreach($items as $item)
                @php
                    /**
                     * @var \JobMetric\Translation\Typeify\Translation $item
                     */
                @endphp
                @if(str_contains($item->customField->params['name'] ?? '', 'meta_title') ||
                    str_contains($item->customField->params['name'] ?? '', 'meta_description') ||
                    str_contains($item->customField->params['name'] ?? '', 'meta_keywords'))
                    @continue
                @endif
                {!!
                    $item->customField->render(
                        replaces: ['locale' => app()->getLocale()],
                        class: 'modal-translation-field',
                        hasErrorTagJs: true,
                        errorTagClass: 'modal-translation-errors',
                        prefixId: 'modal_translation_field'
                    )
                !!}
            @endforeach
        </div>

        @if($hasSeo)
            <div class="tab-pane fade" id="modal_translation_tab_general_seo" role="tabpanel">
                @foreach($items as $item)
                    @php
                        /**
                         * @var \JobMetric\Translation\Typeify\Translation $item
                         */
                    @endphp
                    @if(!(str_contains($item->customField->params['name'] ?? '', 'meta_title') ||
                        str_contains($item->customField->params['name'] ?? '', 'meta_description') ||
                        str_contains($item->customField->params['name'] ?? '', 'meta_keywords')))
                        @continue
                    @endif
                    {!!
                        $item->customField->render(
                            replaces: ['locale' => app()->getLocale()],
                            class: 'modal-translation-field',
                            hasErrorTagJs: true,
                            errorTagClass: 'modal-translation-errors',
                            prefixId: 'modal_translation_field'
                        )
                    !!}
                @endforeach
            </div>
        @endif
    </div>

    <div class="mt-10 d-flex justify-content-end">
        <button type="submit" class="btn btn-sm btn-primary">{{ trans('panelio::base.button.save') }}</button>
    </div>
</form>
