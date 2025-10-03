import {MESSAGE} from "../Constants/MessageConstants";
import StringFilter from "../Filters/StringFilter";
import AppHelperService from "../../../../../../app/Resources/public/js/Helper/AppHelperService";
import loginMapper from "../Mapper/LoginMapper";
import toastrService from "../../../../../../app/Resources/public/js/Services/ToastrService";
import loginValidator from "../Validation/LoginValidator";

class LoginService {
    #mapper;
    #toastr;
    #validator;

    constructor() {
        this.#mapper = loginMapper;
        this.#toastr = toastrService;

        this.#validator = loginValidator.validate();
    }

    doLogin() {
        if(!this.#mapper.form.valid()){
            return false;
        }

        this.#toastr.showLoadingMessage();

        tjq.post(this.#mapper.form.attr('action'), this.#mapper.form.serialize())
            .then(response => {
                let title, message;
                let url = (location.pathname.indexOf('aktivacija-naloga') === -1) ? 'reload' : Routing.generate('site_index').generateUrl();

                title = null;
                message = StringFilter.stringFormat(MESSAGE.SUCCESS.SIGNIN, [response.user]);

                this.#toastr.addOptions(
                    {onHidden: AppHelperService.redirect(url)}
                )

                this.#toastr.success(message);

                this.#mapper.form.reset();
            })
            .fail(error => {
                let errors = error.responseJSON;
                let url = Routing.generate('site_activate_registration', {id: errors.id});

                if (errors.id) {
                    this.#toastr.addOptions(
                        {onHidden: AppHelperService.redirect(url)}
                    )
                }

                this.#toastr.error(Translator.trans(errors.message, null, 'messages', LOCALE));

                this.#mapper.form.reset();
            })
    }

    reset() {
        this.#mapper.form.trigger('reset');
        this.#validator.resetForm();
    }
};

export default LoginService;
