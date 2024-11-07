<!--begin::Multi General Name-->
<div class="card card-flush py-4">
    <div class="card-body">
        <div class="row">
            <div class="col-1">
                <ul class="nav nav-tabs nav-pills flex-row flex-md-column border-0">
                    @foreach($languages as $language_key => $language_value)
                        <li class="nav-item mb-5 me-0" data-bs-toggle="tooltip" data-bs-placement="left" title="{{ $language_value->name }}">
                            <a class="nav-link @if($language_key === 0) active @endif" data-bs-toggle="tab" href="#tab_general_{{ $language_value->locale }}">
                                <img src="{{ asset('assets/vendor/language/flags/' . $language_value->flag) }}" alt="{{ $language_value->name }}">
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="col-11 ps-7">
                <div class="tab-content">
                    @foreach($languages as $language_key => $language_value)
                        <div class="tab-pane fade @if($language_key === 0) show active @endif" id="tab_general_{{ $language_value->locale }}" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fs-5 fw-bold">{{ trans('translation::base.components.translation_card.multi_title', ['language' => $language_value->name]) }}</span>
                                <div>
                                    @if(isset($items['seo']) && $items['seo'])
                                        <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x fs-6 border-0">
                                            <li class="nav-item">
                                                <a class="nav-link active" data-bs-toggle="tab" href="#tab_general_{{ $language_value->locale }}_public">{{ trans('translation::base.components.translation_card.tabs.basic_info') }}</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-bs-toggle="tab" href="#tab_general_{{ $language_value->locale }}_seo">{{ trans('translation::base.components.translation_card.tabs.seo') }}</a>
                                            </li>
                                        </ul>
                                    @endif
                                </div>
                            </div>
                            <div class="tab-content mt-10">
                                <div class="tab-pane fade show active" id="tab_general_{{ $language_value->locale }}_public" role="tabpanel">
                                    @if(isset($items['fields']['name']))
                                        <div class="mb-10">
                                            <label class="form-label d-flex justify-content-between align-items-center">
                                                <span class="required">{{ trans($items['fields']['name']['label']) }}</span>
                                                <div class="text-gray-600 fs-7 d-none d-md-block d-lg-none d-xl-block">{{ trans($items['fields']['name']['info']) }}</div>
                                            </label>
                                            <input type="text" name="translation[{{ $language_value->locale }}][name]" class="form-control" placeholder="{{ trans($items['fields']['name']['placeholder']) }}" value="{{ $values[$language_value->locale]['name'] ?? '' }}">
                                            <div class="text-gray-600 fs-7 mt-2 d-md-none d-lg-block d-xl-none">{{ trans($items['fields']['name']['info']) }}</div>
                                            @error('translation.' . $language_value->locale . '.name')
                                            <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @else
                                        <div class="mb-10">
                                            <label class="form-label d-flex justify-content-between align-items-center">
                                                <span class="required">{{ trans('translation::base.components.translation_card.fields.name.label') }}</span>
                                                <div class="text-gray-600 fs-7 d-none d-md-block d-lg-none d-xl-block">{{ trans('translation::base.components.translation_card.fields.name.info') }}</div>
                                            </label>
                                            <input type="text" name="translation[{{ $language_value->locale }}][name]" class="form-control" placeholder="{{ trans('translation::base.components.translation_card.fields.name.placeholder') }}" value="{{ $values[$language_value->locale]['name'] ?? '' }}">
                                            <div class="text-gray-600 fs-7 mt-2 d-md-none d-lg-block d-xl-none">{{ trans('translation::base.components.translation_card.fields.name.info') }}</div>
                                            @error('translation.' . $language_value->locale . '.name')
                                            <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @endif

                                    @if(isset($items['fields']))
                                        @foreach($items['fields'] as $field_key => $field_value)
                                            @if($field_key === 'name') @continue @endif
                                            <div>
                                                <label class="form-label d-flex justify-content-between align-items-center">
                                                    <span>{{ trans($field_value['label']) }}</span>
                                                    <div class="text-gray-600 fs-7 d-none d-md-block d-lg-none d-xl-block">{{ trans($field_value['info']) }}</div>
                                                </label>
                                                @if($field_value['type'] === 'textarea')
                                                    <textarea name="translation[{{ $language_value->locale }}][{{ $field_key }}]" class="form-control" placeholder="{{ trans($field_value['placeholder']) }}">{{ $values[$language_value->locale][$field_key] ?? '' }}</textarea>
                                                @endif
                                                @if($field_value['type'] === 'text')
                                                    <input type="text" name="translation[{{ $language_value->locale }}][{{ $field_key }}]" class="form-control" placeholder="{{ trans($field_value['placeholder']) }}" value="{{ $values[$language_value->locale][$field_key] ?? '' }}">
                                                @endif
                                                <div class="text-gray-600 fs-7 mt-2 d-md-none d-lg-block d-xl-none">{{ trans($field_value['info']) }}</div>
                                                @error('translation.' . $language_value->locale . '.' . $field_key)
                                                <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                @if(isset($items['seo']) && $items['seo'])
                                    <div class="tab-pane fade" id="tab_general_{{ $language_value->locale }}_seo" role="tabpanel">
                                        <div class="mb-10">
                                            <label class="form-label d-flex justify-content-between align-items-center">
                                                <span>{{ trans('translation::base.components.translation_card.fields.meta_title.label') }}</span>
                                                <div class="text-gray-600 fs-7 d-none d-md-block d-lg-none d-xl-block">{{ trans('translation::base.components.translation_card.fields.meta_title.info') }}</div>
                                            </label>
                                            <input type="text" name="translation[{{ $language_value->locale }}][meta_title]" class="form-control" placeholder="{{ trans('translation::base.components.translation_card.fields.meta_title.placeholder') }}" value="{{ $values[$language_value->locale]['meta_title'] ?? '' }}">
                                            <div class="text-gray-600 fs-7 mt-2 d-md-none d-lg-block d-xl-none">{{ trans('translation::base.components.translation_card.fields.meta_title.info') }}</div>
                                            @error('translation.' . $language_value->locale . '.meta_title')
                                            <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-10">
                                            <label class="form-label d-flex justify-content-between align-items-center">
                                                <span>{{ trans('translation::base.components.translation_card.fields.meta_description.label') }}</span>
                                                <div class="text-gray-600 fs-7 mt-2 d-none d-md-block d-lg-none d-xl-block">{{ trans('translation::base.components.translation_card.fields.meta_description.info') }}</div>
                                            </label>
                                            <input type="text" name="translation[{{ $language_value->locale }}][meta_description]" class="form-control" placeholder="{{ trans('translation::base.components.translation_card.fields.meta_description.placeholder') }}" value="{{ $values[$language_value->locale]['meta_description'] ?? '' }}">
                                            <div class="text-gray-600 fs-7 mt-2 d-md- d-lg-block d-xl-none">{{ trans('translation::base.components.translation_card.fields.meta_description.info') }}</div>
                                            @error('translation.' . $language_value->locale . '.meta_description')
                                            <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label d-flex justify-content-between align-items-center">
                                                <span>{{ trans('translation::base.components.translation_card.fields.meta_keywords.label') }}</span>
                                                <div class="text-gray-600 fs-7 mt-2 d-none d-md-block d-lg-none d-xl-block">{!! trans('translation::base.components.translation_card.fields.meta_keywords.info') !!}</div>
                                            </label>
                                            <input type="text" name="translation[{{ $language_value->locale }}][meta_keywords]" class="form-control" placeholder="{{ trans('translation::base.components.translation_card.fields.meta_keywords.placeholder') }}" value="{{ $values[$language_value->locale]['meta_keywords'] ?? '' }}">
                                            <div class="text-gray-600 fs-7 mt-2 d-md-none d-lg-block d-xl-none">{!! trans('translation::base.components.translation_card.fields.meta_keywords.info') !!}</div>
                                            @error('translation.' . $language_value->locale . '.meta_keywords')
                                            <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
<!--end::Multi General Name-->
