<form action="{{ $action }}" method="post" id="object-translation-form">
    @csrf
    <input type="hidden" name="locale" id="modal_translation_locale">
    <input type="hidden" name="translatable_id" id="modal_translation_translatable_id">

    @php
        $hasSeo = false;
        foreach ($items as $item) {
            if (str_contains($item, 'meta_title')) {
                $hasSeo = true;
                break;
            }
        }
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
                @if(str_contains($item ?? '', 'meta_title') || str_contains($item ?? '', 'meta_description') || str_contains($item ?? '', 'meta_keywords'))
                    @continue
                @endif
                <div class="mb-10">
                    <label class="form-label d-flex justify-content-between align-items-center">
                        <span>{{ trans(str_replace('{field}', $item, $trans_scope) . '.title') }}</span>
                        <div class="text-gray-600 fs-7 d-none d-md-block d-lg-none d-xl-block">{{ trans(str_replace('{field}', $item, $trans_scope) . '.info') }}</div>
                    </label>
                    <input type="text" name="translation[{{ app()->getLocale() }}][{{ $item }}]" placeholder="{{ trans(str_replace('{field}', $item, $trans_scope) . '.placeholder') }}" class="form-control modal-translation-field" id="modal_translation_field_{{ $item }}" data-name="translation.{{ app()->getLocale() }}.{{ $item }}">
                    <div class="text-gray-600 fs-7 mt-2 d-md-none d-lg-block d-xl-none">{{ trans(str_replace('{field}', $item, $trans_scope) . '.title') }}</div>
                    <div class="modal-translation-errors text-danger fs-7 mt-2" data-name="translation.{{ app()->getLocale() }}.{{ $item }}"></div>
                </div>
            @endforeach
        </div>

        @if($hasSeo)
            <div class="tab-pane fade" id="modal_translation_tab_general_seo" role="tabpanel">
                @foreach($items as $item)
                    @if(!(str_contains($item, 'meta_title') || str_contains($item, 'meta_description') || str_contains($item, 'meta_keywords')))
                        @continue
                    @endif
                    <div class="mb-10">
                        <label class="form-label d-flex justify-content-between align-items-center">
                            <span>{{ trans('translation::base.components.translation_card.fields.'.$item.'.label') }}</span>
                            <div class="text-gray-600 fs-7 d-none d-md-block d-lg-none d-xl-block">{!! trans('translation::base.components.translation_card.fields.'.$item.'.info') !!}</div>
                        </label>
                        <input type="text" name="translation[{{ app()->getLocale() }}][{{ $item }}]" placeholder="{{ trans('translation::base.components.translation_card.fields.'.$item.'.placeholder') }}" class="form-control modal-translation-field" id="modal_translation_field_{{ $item }}" data-name="translation.{{ app()->getLocale() }}.{{ $item }}">
                        <div class="text-gray-600 fs-7 mt-2 d-md-none d-lg-block d-xl-none">{{ trans(str_replace('{field}', $item, $trans_scope) . '.title') }}</div>
                        <div class="modal-translation-errors text-danger fs-7 mt-2" data-name="translation.{{ app()->getLocale() }}.{{ $item }}"></div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="mt-10 d-flex justify-content-end">
        <button type="submit" class="btn btn-sm btn-primary">{{ trans('panelio::base.button.save') }}</button>
    </div>
</form>
