<!--begin::General Name-->
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
                    @if(str_contains($item, 'meta_title') || str_contains($item, 'meta_description') || str_contains($item, 'meta_keywords'))
                        @continue
                    @endif
                    <div class="mb-10">
                        <label class="form-label d-flex justify-content-between align-items-center">
                            <span>{{ trans(str_replace('{field}', $item, $transScope) . '.title') }}</span>
                            <div class="text-gray-600 fs-7 d-none d-md-block d-lg-none d-xl-block">{{ trans(str_replace('{field}', $item, $transScope) . '.info') }}</div>
                        </label>
                        <input type="text" name="translation[{{ app()->getLocale() }}][{{ $item }}]" placeholder="{{ trans(str_replace('{field}', $item, $transScope) . '.placeholder') }}" value="{{ $values[$item] ?? '' }}" id="translation_{{ app()->getLocale() }}_{{ $item }}" class="form-control" data-name="translation.{{ app()->getLocale() }}.{{ $item }}">
                        <div class="text-gray-600 fs-7 mt-2 d-md-none d-lg-block d-xl-none">{{ trans(str_replace('{field}', $item, $transScope) . '.title') }}</div>
                        @error('translation.' . app()->getLocale() . '.' . $item)
                            <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach
            </div>

            @if($hasSeo)
                <div class="tab-pane fade" id="tab_general_seo" role="tabpanel">
                    @foreach($items as $item)
                        @if(!(str_contains($item, 'meta_title') || str_contains($item, 'meta_description') || str_contains($item, 'meta_keywords')))
                            @continue
                        @endif
                        <div class="mb-10">
                            <label class="form-label d-flex justify-content-between align-items-center">
                                <span>{{ trans('translation::base.components.translation_card.fields.'.$item.'.label') }}</span>
                                <div class="text-gray-600 fs-7 d-none d-md-block d-lg-none d-xl-block">{!! trans('translation::base.components.translation_card.fields.'.$item.'.info') !!}</div>
                            </label>
                            <input type="text" name="translation[{{ app()->getLocale() }}][{{ $item }}]" placeholder="{{ trans('translation::base.components.translation_card.fields.'.$item.'.placeholder') }}" value="{{ $values[$item] ?? '' }}" id="translation_{{ app()->getLocale() }}_{{ $item }}" class="form-control" data-name="translation.{{ app()->getLocale() }}.{{ $item }}">
                            <div class="text-gray-600 fs-7 mt-2 d-md-none d-lg-block d-xl-none">{{ trans(str_replace('{field}', $item, $transScope) . '.title') }}</div>
                            @error('translation.' . app()->getLocale() . '.' . $item)
                                <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
<!--end::General Name-->
