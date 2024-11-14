<form action="{{ $action }}" method="post" id="object-translation-form">
    @csrf
    <input type="hidden" name="locale" id="modal_translation_locale">
    <input type="hidden" name="translatable_id" id="modal_translation_translatable_id">

    @if(isset($items['seo']) && $items['seo'])
        <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x fs-6 border-0">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#modal_translation_tab_general_public">{{ trans('translation::base.components.translation_card.tabs.basic_info') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#modal_translation_tab_general_seo">{{ trans('translation::base.components.translation_card.tabs.seo') }}</a>
            </li>
        </ul>
    @endif
    <div class="tab-content mt-10">
        <div class="tab-pane fade show active" id="modal_translation_tab_general_public" role="tabpanel">
            @if(isset($items['fields']['name']))
                <div class="mb-10">
                    <label class="form-label d-flex justify-content-between align-items-center">
                        <span class="required">{{ trans($items['fields']['name']['label']) }}</span>
                        <div class="text-gray-600 fs-7 d-none d-md-block d-lg-none d-xl-block">{{ trans($items['fields']['name']['info']) }}</div>
                    </label>
                    <input type="text" name="translation[name]" class="form-control modal-translation-field" placeholder="{{ trans($items['fields']['name']['placeholder']) }}" id="modal_translation_field_name">
                    <div class="text-gray-600 fs-7 d-md-none d-lg-block d-xl-none">{{ trans($items['fields']['name']['info']) }}</div>
                    <div class="modal-translation-errors text-danger fs-7 mt-2" data-name="translation.name"></div>
                </div>
            @else
                <div class="mb-10">
                    <label class="form-label d-flex justify-content-between align-items-center">
                        <span class="required">{{ trans('translation::base.components.translation_card.fields.name.label') }}</span>
                        <div class="text-gray-600 fs-7 d-none d-md-block d-lg-none d-xl-block">{{ trans('translation::base.components.translation_card.fields.name.info') }}</div>
                    </label>
                    <input type="text" name="translation[name]" class="form-control modal-translation-field" placeholder="{{ trans('translation::base.components.translation_card.fields.name.placeholder') }}" id="modal_translation_field_name">
                    <div class="text-gray-600 fs-7 d-md-none d-lg-block d-xl-none">{{ trans('translation::base.components.translation_card.fields.name.info') }}</div>
                    <div class="modal-translation-errors text-danger fs-7 mt-2" data-name="translation.name"></div>
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
                            <textarea name="translation[{{ $field_key }}]" class="form-control modal-translation-field" placeholder="{{ trans($field_value['placeholder']) }}" id="modal_translation_field_{{ $field_key }}"></textarea>
                        @endif
                        @if($field_value['type'] === 'text')
                            <input type="text" name="translation[{{ $field_key }}]" class="form-control modal-translation-field" placeholder="{{ trans($field_value['placeholder']) }}" id="modal_translation_field_{{ $field_key }}">
                        @endif
                        <div class="text-gray-600 fs-7 d-md-none d-lg-block d-xl-none">{{ trans($field_value['info']) }}</div>
                        <div class="modal-translation-errors text-danger fs-7 mt-2" data-name="translation.{{ $field_key }}"></div>
                    </div>
                @endforeach
            @endif
        </div>

        @if(isset($items['seo']) && $items['seo'])
            <div class="tab-pane fade" id="modal_translation_tab_general_seo" role="tabpanel">
                <div class="mb-10">
                    <label class="form-label d-flex justify-content-between align-items-center">
                        <span>{{ trans('translation::base.components.translation_card.fields.meta_title.label') }}</span>
                        <div class="text-gray-600 fs-7 d-none d-md-block d-lg-none d-xl-block">{{ trans('translation::base.components.translation_card.fields.meta_title.info') }}</div>
                    </label>
                    <input type="text" name="translation[meta_title]" class="form-control modal-translation-field" placeholder="{{ trans('translation::base.components.translation_card.fields.meta_title.placeholder') }}" id="modal_translation_field_meta_title">
                    <div class="text-gray-600 fs-7 d-md-none d-lg-block d-xl-none">{{ trans('translation::base.components.translation_card.fields.meta_title.info') }}</div>
                    <div class="modal-translation-errors text-danger fs-7 mt-2" data-name="translation.meta_title"></div>
                </div>
                <div class="mb-10">
                    <label class="form-label d-flex justify-content-between align-items-center">
                        <span>{{ trans('translation::base.components.translation_card.fields.meta_description.label') }}</span>
                        <div class="text-gray-600 fs-7 mt-2 d-none d-md-block d-lg-none d-xl-block">{{ trans('translation::base.components.translation_card.fields.meta_description.info') }}</div>
                    </label>
                    <input type="text" name="translation[meta_description]" class="form-control modal-translation-field" placeholder="{{ trans('translation::base.components.translation_card.fields.meta_description.placeholder') }}" id="modal_translation_field_meta_description">
                    <div class="text-gray-600 fs-7 d-md- d-lg-block d-xl-none">{{ trans('translation::base.components.translation_card.fields.meta_description.info') }}</div>
                    <div class="modal-translation-errors text-danger fs-7 mt-2" data-name="translation.meta_description"></div>
                </div>
                <div class="mb-0">
                    <label class="form-label d-flex justify-content-between align-items-center">
                        <span>{{ trans('translation::base.components.translation_card.fields.meta_keywords.label') }}</span>
                        <div class="text-gray-600 fs-7 d-none d-md-block d-lg-none d-xl-block">{!! trans('translation::base.components.translation_card.fields.meta_keywords.info') !!}</div>
                    </label>
                    <input type="text" name="translation[meta_keywords]" class="form-control modal-translation-field" placeholder="{{ trans('translation::base.components.translation_card.fields.meta_keywords.placeholder') }}" id="modal_translation_field_meta_keywords">
                    <div class="text-gray-600 fs-7 mt-2 d-md-none d-lg-block d-xl-none">{!! trans('translation::base.components.translation_card.fields.meta_keywords.info') !!}</div>
                    <div class="modal-translation-errors text-danger fs-7 mt-2" data-name="translation.meta_keywords"></div>
                </div>
            </div>
        @endif
    </div>

    <div class="mt-10 d-flex justify-content-end">
        <button type="submit" class="btn btn-sm btn-primary">ذخیره</button>
    </div>
</form>
