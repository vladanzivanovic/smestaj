import AppHelperService from "../../../../../../app/Resources/public/js/Helper/AppHelperService";
import aboutUsPageMapper from "../Mapper/AboutUsPageMapper";
import toastrService from "../../../../../../app/Resources/public/js/Services/ToastrService";

class AboutUsHandler {
    #toastr;

    constructor() {
        this.mapper = aboutUsPageMapper;
        this.#toastr = toastrService;
    }

    save()
    {
        let urlRoute = AppHelperService.generateLocalizedUrl('admin.set_about_us_api');
        let type = 'POST';
        const data = $(this.mapper.form).serializeArray();

        if (! $(this.mapper.form).valid()) {
            return false;
        }

        this.#toastr.showLoadingMessage();

        $.ajax({
            type,
            url: urlRoute,
            data,
            dataType: 'json',
            success: response => {
                AppHelperService.redirect(AppHelperService.generateLocalizedUrl('admin.about_us_page'));
            },
            error: error => {
                this.#toastr.error(Translator.trans('generic_error', null, 'messages', LOCALE));
            }
        })
    }
}

export default AboutUsHandler;
