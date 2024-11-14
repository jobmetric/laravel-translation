export function generate_column_translation_list(e) {
    let flag_check_all = true
    $.each(localize.languages, function(key, language) {
        if (e.translations[language.locale] === undefined) {
            flag_check_all = false
            return true
        }
    })
    let theme = `<div class="btn-group ms-3" role="group">
                            <div class="dropdown dropdown-menu-end">
                                <button type="button" class="btn btn-sm btn-outline btn-outline-dashed bg-light-success btn-color-gray-800 dropdown-big-cursor-rtl pe-12" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="la la-language fs-2 position-absolute"></i>
                                    <span class="ps-9">ترجمه‌ها</span>`
                                        if (flag_check_all) {
                                            theme += `<i class="la la-check text-primary fs-2 ms-3 position-absolute"></i>`
                                        } else {
                                            theme += `<i class="la la-close text-danger fs-2 ms-3 position-absolute"></i>`
                                        }
                              theme += `</button>
                                            <ul class="dropdown-menu w-225px">
                                                <li class="dropdown-item d-flex justify-content-between align-items-center">
                                                    <span class="fs-7 fw-bold">زبان مقصد ترجمه را انتخاب کنید</span>
                                                    <i class="la la-info-circle fs-2"></i>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>`

                                                $.each(localize.languages, function(key, language) {
                                                    let check = ``
                                                    if (e.translations[language.locale] === undefined) {
                                                        check = `<i class="la la-close text-danger fs-2"></i>`
                                                    } else {
                                                        check = `<i class="la la-check text-primary fs-2"></i>`
                                                    }
                                                    theme += `<li>
                                                                  <a href="javascript:void(0)" class="dropdown-item d-flex justify-content-between change-language-list" data-locale="${language.locale}">
                                                                      <div>
                                                                          <img src="assets/vendor/language/flags/${language.flag}" alt="${language.name}" class="w-20px h-20px">
                                                                          <span class="ms-2">${language.name}</span>
                                                                      </div>
                                                                      <div>${check}</div>
                                                                  </a>
                                                              </li>`
                                                })

                                    theme += `</ul>
                                           </div>
                                       </div>`
    return theme
}

$(document).ready(function(){

})
