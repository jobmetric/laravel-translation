<!--begin::Multi General Name-->
@php
    $hasSeo = false;
    foreach ($items as $item) {
        if (str_contains($item, 'meta_title')) {
            $hasSeo = true;
            break;
        }
    }
@endphp
<div class="card card-flush py-4">
    <div class="card-body">
        <div class="row">
            <div class="col-1">
                <ul class="nav nav-tabs nav-pills flex-row flex-md-column border-0">
                    @foreach($languages as $language_key => $language_value)
                        <li class="nav-item mb-5 me-0 position-relative" data-bs-toggle="tooltip" data-bs-placement="left" title="{{ $language_value->name }}">
                            @error('translation.' . $language_value->locale . '.*')
                                <i class="la la-exclamation-triangle position-absolute text-danger fs-2 animation-shake" style="top:10px;@if(trans('domi::base.direction') == 'rtl') right:-25px @else left:-25px @endif"></i>
                            @enderror
                            <a class="nav-link text-center @if($language_key === 0) active @endif" data-bs-toggle="tab" href="#tab_general_{{ $language_value->locale }}">
                                <img src="{{ asset('assets/vendor/language/flags/' . $language_value->flag) }}" alt="{{ $language_value->name }}" style="width: 30px">
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
                                    @if($hasSeo)
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
                                    @foreach($items as $item)
                                        @if(str_contains($item, 'meta_title') || str_contains($item, 'meta_description') || str_contains($item, 'meta_keywords'))
                                            @continue
                                        @endif
                                        <div class="mb-10">
                                            <label class="form-label d-flex justify-content-between align-items-center">
                                                <span>{{ trans(str_replace('{field}', $item, $transScope) . '.title') }}</span>
                                                <div class="text-gray-600 fs-7 d-none d-md-block d-lg-none d-xl-block">{{ trans(str_replace('{field}', $item, $transScope) . '.info') }}</div>
                                            </label>
                                            <input type="text" name="translation[{{ $language_value->locale }}][{{ $item }}]" placeholder="{{ trans(str_replace('{field}', $item, $transScope) . '.placeholder') }}" value="{{ $values[$language_value->locale][$item] ?? '' }}" id="translation_{{ $language_value->locale }}_{{ $item }}" class="form-control" data-name="translation.{{ $language_value->locale }}.{{ $item }}">
                                            <div class="text-gray-600 fs-7 mt-2 d-md-none d-lg-block d-xl-none">{{ trans(str_replace('{field}', $item, $transScope) . '.title') }}</div>
                                            @error('translation.' . $language_value->locale . '.' . $item)
                                            <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @endforeach
                                </div>
                                @if($hasSeo)
                                    <div class="tab-pane fade" id="tab_general_{{ $language_value->locale }}_seo" role="tabpanel">
                                        @foreach($items as $item)
                                            @if(!(str_contains($item, 'meta_title') || str_contains($item, 'meta_description') || str_contains($item, 'meta_keywords')))
                                                @continue
                                            @endif
                                            <div class="mb-10">
                                                <label class="form-label d-flex justify-content-between align-items-center">
                                                    <span>{{ trans('translation::base.components.translation_card.fields.'.$item.'.label') }}</span>
                                                    <div class="text-gray-600 fs-7 d-none d-md-block d-lg-none d-xl-block">{!! trans('translation::base.components.translation_card.fields.'.$item.'.info') !!}}</div>
                                                </label>
                                                <input type="text" name="translation[{{ $language_value->locale }}][{{ $item }}]" placeholder="{{ trans('translation::base.components.translation_card.fields.'.$item.'.placeholder') }}" value="{{ $values[$item] ?? '' }}" id="translation_{{ $language_value->locale }}_{{ $item }}" class="form-control" data-name="translation.{{ $language_value->locale }}.{{ $item }}">
                                                <div class="text-gray-600 fs-7 mt-2 d-md-none d-lg-block d-xl-none">{{ trans(str_replace('{field}', $item, $transScope) . '.title') }}</div>
                                                @error('translation.' . $language_value->locale . '.' . $item)
                                                <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        @endforeach
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
