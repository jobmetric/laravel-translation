function datatableColumnTranslationList(e) {
    let flag_check_all = true
    $.each(localize.languages, function(key, language) {
        if (e.translations[language.locale] === undefined) {
            flag_check_all = false
            return true
        }
    })
    let theme = `<div class="d-flex justify-content-center align-items-center">
                            <div class="btn-group ms-3" role="group">
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
                                                          <a href="javascript:void(0)" class="dropdown-item d-flex justify-content-between" onclick="translation_list.click(this)" data-locale="${language.locale}">
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
                               </div>
                            </div>`
    return theme
}

const translation_list = {
    click: function (element){
        let locale = $(element).data('locale')
        const tr = $(element).closest('tr')
        const row = dt.row(tr)

        let language_data = []
        $.each(localize.languages, function(key, language) {
            if (locale === language.locale) {
                language_data = language
                return false
            }
        })

        $('#modal-translation').modal('show')

        $('#modal_translation_label').text(localize.language.translation.modals.translation_list.title.replace('{language}', language_data.name))
        $('#modal_translation_locale').val(locale)
        $('#modal_translation_translatable_id').val(row.data().id)

        $('.modal-translation-field').val('').each(function() {
            let nameAttr = $(this).attr('name')
            if (nameAttr) {
                $(this).attr('name', nameAttr.replace(/\[.*?\]/, `[${locale}]`))
            }

            let nameData = $(this).attr('data-name')
            if (nameData) {
                $(this).attr('data-name', nameData.replace(/\.([a-z]{2})\./, `.${locale}.`))
            }
        })

        $('.modal-translation-errors').text('').each(function() {
            let nameData = $(this).attr('data-name')
            if (nameData) {
                $(this).attr('data-name', nameData.replace(/\.([a-z]{2})\./, `.${locale}.`))
            }
        })

        $.each(eval(`row.data().translations?.${locale}`), function(language_key, language_value) {
            $(`#modal_translation_field_${language_key}`).val(language_value)
        })
    }
}
$(document).ready(function(){
    $('#object-translation-form').ajaxForm({
        dataType : 'json',
        beforeSubmit: function () {
            $('.modal-translation-errors').text('')
        },
        success: function (json) {
            $('#modal-translation').modal('hide')

            if (dt) {
                dt.ajax.reload();
            }

            Swal.fire({
                icon: 'info',
                title: json.message,
                showConfirmButton: true,
                confirmButtonText: localize.language.panelio.button.realized,
                allowOutsideClick: false
            })
        },
        error: function (error_data) {
            $('.modal-translation-errors').text('')
            $.each(error_data.responseJSON.errors, function (field, errors) {
                console.log(field)
                $(`.modal-translation-errors[data-name="${field}"]`).text(errors[0])
            })
        }
    })
})
