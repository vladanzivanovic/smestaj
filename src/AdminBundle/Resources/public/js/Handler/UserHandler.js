import AppHelperService from "../../../../../../app/Resources/public/js/Helper/AppHelperService";
import userPageMapper from "../Mapper/UserPageMapper";
import FormHelperService from "../../../../../../app/Resources/public/js/Helper/FormHelperService";
import toastrService from "../../../../../../app/Resources/public/js/Services/ToastrService";

class UserHandler {
    #toastr;
    constructor() {
        this.mapper = userPageMapper;
        this.#toastr = toastrService;
    }

    save() {
        let urlRoute = Routing.generate('admin.add_user_api');
        let type = 'POST';
        const data = FormHelperService.sanitize($(this.mapper.form).serializeArray());

        if (IS_EDIT) {
            urlRoute = Routing.generate('admin.edit_user_api', {id: ID});
            type = 'PUT';
        }

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
                AppHelperService.redirect(Routing.generate('admin.users'));
            },
            error: error => {
                this.#toastr.error(Translator.trans('generic_error', null, 'messages', LOCALE));
            }
        })
    }

    changeStatus(checkbox, id, status) {
        $.ajax({
            type: 'PATCH',
            'url': Routing.generate('admin.api_toggle_user_status', {id, status}),
            dataType: 'json',
            success: (response) => {
                checkbox.parentElement.firstElementChild.innerText = Translator.trans(response.text, null, 'messages', LOCALE);
            },
            error: () => {
                this.#toastr.error(Translator.trans('generic_error', null, 'message', LOCALE));
            }
        })
    }
}

export default UserHandler;
