import AppHelperService from "../../../../../../app/Resources/public/js/Helper/AppHelperService";
import CategoryDataTables from "../Services/DataTables/CategoryDataTables";
import toastrService from "../../../../../../app/Resources/public/js/Services/ToastrService";

class CategoryHandler {
    #toastr;
    constructor() {
        this.#toastr = toastrService;
    }

    save(mapper) {
        let urlRoute = AppHelperService.generateLocalizedUrl('admin.add_category_api');
        let type = 'POST';
        const data = mapper.form.serializeArray();

        if (! mapper.form.valid()) {
            return false;
        }

        if (IS_EDIT) {
            urlRoute = AppHelperService.generateLocalizedUrl('admin.edit_category_api', {slug: SLUG});
            type = 'PUT';
        }

        this.#toastr.showLoadingMessage();

        $.ajax({
            type,
            url: urlRoute,
            data,
            dataType: 'json',
            success: response => {
                AppHelperService.redirect(AppHelperService.generateLocalizedUrl('admin.categories'));
            },
            error: error => {
                this.#toastr.error(Translator.trans('generic_error', null, 'messages', LOCALE));
            }
        })
    }

    remove(slug) {
        this.notification.showLoadingMessage();

        $.ajax({
            type: 'DELETE',
            url: AppHelperService.generateLocalizedUrl('admin.remove_category_api', {slug}),
            dataType: 'json',
            success: () => {
                CategoryDataTables().reload();
                this.#toastr.remove();
            },
            error: jxHR => {
                const errors = jxHR.responseJSON;

                if (errors.hasOwnProperty('message')) {
                    this.#toastr.error(Translator.trans(errors.message, {item: 'Kategorija'}, 'messages', LOCALE));

                    return;
                }

                this.#toastr.error(Translator.trans('generic_error', null, 'messages', LOCALE+'_RS'));
            }
        })
    }

    toggleShowHomePage(slug, status) {
        $.ajax({
            type: 'PATCH',
            'url': AppHelperService.generateLocalizedUrl('admin.api_category_change_home_page', {slug, status}),
            dataType: 'json',
            success: () => {},
            error: () => {
                const errors = error.responseJSON;

                if (errors.hasOwnProperty('message')) {
                    this.#toastr.error(errors.message);

                    return;
                }

                this.#toastr.error(Translator.trans('generic_error', null, 'message', LOCALE));
            }
        })
    }
}

export default CategoryHandler;
