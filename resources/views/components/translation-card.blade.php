@php
    $hasSeo = false;
    $items->each(function (\JobMetric\Translation\ServiceType\Translation $translation) use (&$hasSeo) {
        if (str_contains($translation->customField->params['name'] ?? '', 'meta_title')) {
            $hasSeo = true;
            return false;
        }
    });
@endphp
<!--begin::General Name-->
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
                            replaces: ['locale' => $locale],
                            hasErrorTagForm: true,
                            errorTagClass: 'form-errors',
                            prefixId: 'translation_' . $locale
                        )
                    !!}
                @endforeach


                {{--@if(isset($items['fields']['name']))
                    <div class="mb-10">
                        <label class="form-label d-flex justify-content-between align-items-center">
                            <span class="required">{{ trans($items['fields']['name']['label']) }}</span>
                            <div class="text-gray-600 fs-7 d-none d-md-block d-lg-none d-xl-block">{{ trans($items['fields']['name']['info']) }}</div>
                        </label>
                        <input type="text" name="translation[name]" class="form-control" placeholder="{{ trans($items['fields']['name']['placeholder']) }}" value="{{ $values['name'] ?? '' }}">
                        <div class="text-gray-600 fs-7 mt-2 d-md-none d-lg-block d-xl-none">{{ trans($items['fields']['name']['info']) }}</div>
                        @error('translation.name')
                        <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                @else
                    <div class="mb-10">
                        <label class="form-label d-flex justify-content-between align-items-center">
                            <span class="required">{{ trans('translation::base.components.translation_card.fields.name.label') }}</span>
                            <div class="text-gray-600 fs-7 d-none d-md-block d-lg-none d-xl-block">{{ trans('translation::base.components.translation_card.fields.name.info') }}</div>
                        </label>
                        <input type="text" name="translation[name]" class="form-control" placeholder="{{ trans('translation::base.components.translation_card.fields.name.placeholder') }}" value="{{ $values['name'] ?? '' }}">
                        <div class="text-gray-600 fs-7 mt-2 d-md-none d-lg-block d-xl-none">{{ trans('translation::base.components.translation_card.fields.name.info') }}</div>
                        @error('translation.name')
                        <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                @endif--}}

                {{--@if(isset($items['fields']))
                    @foreach($items['fields'] as $field_key => $field_value)
                        @if($field_key === 'name') @continue @endif
                        <div>
                            <label class="form-label d-flex justify-content-between align-items-center">
                                <span>{{ trans($field_value['label']) }}</span>
                                <div class="text-gray-600 fs-7 d-none d-md-block d-lg-none d-xl-block">{{ trans($field_value['info']) }}</div>
                            </label>
                            @if($field_value['type'] === 'textarea')
                                <textarea name="translation[{{ $field_key }}]" class="form-control" placeholder="{{ trans($field_value['placeholder']) }}">{{ $values[$field_key] ?? '' }}</textarea>
                            @endif
                            @if($field_value['type'] === 'text')
                                <input type="text" name="translation[{{ $field_key }}]" class="form-control" placeholder="{{ trans($field_value['placeholder']) }}" value="{{ $values[$field_key] ?? '' }}">
                            @endif
                            <div class="text-gray-600 fs-7 mt-2 d-md-none d-lg-block d-xl-none">{{ trans($field_value['info']) }}</div>
                            @error('translation.' . $field_key)
                            <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach
                @endif--}}
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
                                replaces: ['locale' => $locale],
                                hasErrorTagForm: true,
                                errorTagClass: 'form-errors',
                                prefixId: 'translation_' . $locale
                            )
                        !!}
                    @endforeach
                    {{--<div class="mb-10">
                        <label class="form-label d-flex justify-content-between align-items-center">
                            <span>{{ trans('translation::base.components.translation_card.fields.meta_title.label') }}</span>
                            <div class="text-gray-600 fs-7 d-none d-md-block d-lg-none d-xl-block">{{ trans('translation::base.components.translation_card.fields.meta_title.info') }}</div>
                        </label>
                        <input type="text" name="translation[meta_title]" class="form-control" placeholder="{{ trans('translation::base.components.translation_card.fields.meta_title.placeholder') }}" value="{{ $values['meta_title'] ?? '' }}">
                        <div class="text-gray-600 fs-7 mt-2 d-md-none d-lg-block d-xl-none">{{ trans('translation::base.components.translation_card.fields.meta_title.info') }}</div>
                        @error('translation.meta_title')
                        <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-10">
                        <label class="form-label d-flex justify-content-between align-items-center">
                            <span>{{ trans('translation::base.components.translation_card.fields.meta_description.label') }}</span>
                            <div class="text-gray-600 fs-7 mt-2 d-none d-md-block d-lg-none d-xl-block">{{ trans('translation::base.components.translation_card.fields.meta_description.info') }}</div>
                        </label>
                        <input type="text" name="translation[meta_description]" class="form-control" placeholder="{{ trans('translation::base.components.translation_card.fields.meta_description.placeholder') }}" value="{{ $values['meta_description'] ?? '' }}">
                        <div class="text-gray-600 fs-7 mt-2 d-md- d-lg-block d-xl-none">{{ trans('translation::base.components.translation_card.fields.meta_description.info') }}</div>
                        @error('translation.meta_description')
                        <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-0">
                        <label class="form-label d-flex justify-content-between align-items-center">
                            <span>{{ trans('translation::base.components.translation_card.fields.meta_keywords.label') }}</span>
                            <div class="text-gray-600 fs-7 mt-2 d-none d-md-block d-lg-none d-xl-block">{!! trans('translation::base.components.translation_card.fields.meta_keywords.info') !!}</div>
                        </label>
                        <input type="text" name="translation[meta_keywords]" class="form-control" placeholder="{{ trans('translation::base.components.translation_card.fields.meta_keywords.placeholder') }}" value="{{ $values['meta_keywords'] ?? '' }}">
                        <div class="text-gray-600 fs-7 mt-2 d-md-none d-lg-block d-xl-none">{!! trans('translation::base.components.translation_card.fields.meta_keywords.info') !!}</div>
                        @error('translation.meta_keywords')
                        <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                        @enderror
                    </div>--}}
                </div>
            @endif
        </div>
    </div>
</div>
<!--end::General Name-->
