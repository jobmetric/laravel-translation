<!--begin::General Name-->
@php
    $hasSeo = false;
    $items->each(function (\JobMetric\Translation\ServiceType\Translation $translation) use (&$hasSeo) {
        if (str_contains($translation->customField->params['name'] ?? '', 'meta_title')) {
            $hasSeo = true;
            return false;
        }
    });
@endphp
<div class="card card-flush py-4">
    <div class="card-header">
        <div class="card-title">
            <span class="fs-5 fw-bold">{{ trans('translation::base.components.translation_card.title') }}</span>
        </div>
        @if($hasSeo)
            <div class="card-toolbar">
                <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x fs-6 border-0">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#tab_general_public">{{ trans('translation::base.components.translation_card.tabs.basic_info') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tab_general_seo">{{ trans('translation::base.components.translation_card.tabs.seo') }}</a>
                    </li>
                </ul>
            </div>
        @endif
    </div>
    <div class="card-body">
        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab_general_public" role="tabpanel">
                @foreach($items as $item)
                    @php
                        /**
                         * @var \JobMetric\Translation\ServiceType\Translation $item
                         */
                    @endphp
                    @if(str_contains($item->customField->params['name'] ?? '', 'meta_title') ||
                        str_contains($item->customField->params['name'] ?? '', 'meta_description') ||
                        str_contains($item->customField->params['name'] ?? '', 'meta_keywords'))
                        @continue
                    @endif
                    {!!
                        $item->customField->render(
                            $values[$item->customField->params['uniqName']] ?? null,
                            ['locale' => $locale],
                            hasErrorTagForm: true,
                            errorTagClass: 'form-errors',
                            prefixId: 'translation_' . $locale
                        )
                    !!}
                @endforeach
            </div>

            @if($hasSeo)
                <div class="tab-pane fade" id="tab_general_seo" role="tabpanel">
                    @foreach($items as $item)
                        @php
                            /**
                             * @var \JobMetric\Translation\ServiceType\Translation $item
                             */
                        @endphp
                        @if(!(str_contains($item->customField->params['name'] ?? '', 'meta_title') ||
                            str_contains($item->customField->params['name'] ?? '', 'meta_description') ||
                            str_contains($item->customField->params['name'] ?? '', 'meta_keywords')))
                            @continue
                        @endif
                        {!!
                            $item->customField->render(
                                $values[$item->customField->params['uniqName']] ?? null,
                                ['locale' => $locale],
                                hasErrorTagForm: true,
                                errorTagClass: 'form-errors',
                                prefixId: 'translation_' . $locale
                            )
                        !!}
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
<!--end::General Name-->
