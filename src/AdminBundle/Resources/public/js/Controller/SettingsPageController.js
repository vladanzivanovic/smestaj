import AppHelperService from "../../../../../../app/Resources/public/js/Helper/AppHelperService";
import toastrService from "../../../../../../app/Resources/public/js/Services/ToastrService";

class SettingsPageController {
    #toastr;

    constructor() {
        this.#toastr = toastrService;

        this.registerEvents();
    }

    registerEvents() {
        $('#settings_submit').on('click touchend', e => {
            const data = $('#edit_form').serializeArray();

            this.#toastr.showLoadingMessage();

            $.ajax({
                type:   'POST',
                url:    AppHelperService.generateLocalizedUrl('admin.update_settings_api'),
                data,
                dataType: 'json',
                success: e => {
                    AppHelperService.redirect(AppHelperService.generateLocalizedUrl('admin.settings_page'));
                },
                error: () => {
                    this.#toastr.error(Translator.trans('generic_error', null, 'message', LOCALE));
                }
            })
        });
    }
}

export default SettingsPageController;
