import {MESSAGE} from "../Constants/MessageConstants";
import AppHelperService from "../../../../../../app/Resources/public/js/Helper/AppHelperService";
import BackendValidator from "../Validation/BackendValidator";
import toastrService from "../../../../../../app/Resources/public/js/Services/ToastrService";
import registrationMapper from "../Mapper/RegistrationMapper";
import registrationValidator from "../Validation/RegistrationValidator";

class RegistrationService {
    #toastr;

    #mapper;

    #validator;

    constructor() {
        this.#toastr = toastrService;
        this.#mapper = registrationMapper;

        this.#validator = registrationValidator.validate();
    }

    doRegistration() {
        if(!this.#mapper.form.valid()){
            return false;
        }

        let data = this.#mapper.form.serializeArray();

        $.post(this.#mapper.form.prop('action'), data)
            .then(response => {
                let message;
                message = MESSAGE.SUCCESS.SIGNUP;

                if (response.facebookId) {
                    AppHelperService.redirect(Routing.generate('hwi_oauth_service_redirect', {service: 'facebook'}));

                    return true;
                }

                this.#toastr.success(message);

                tjq('.opacity-overlay').click();

                return true;
            })
            .fail(error => {
                let errors = error.responseJSON;

                if (errors.error) {
                    this.#toastr.error(errors.error);

                    return;
                }

                BackendValidator().validate(this.#mapper.form, errors);
            });

        return false;
    }

    reset() {
        this.#mapper.form.trigger('reset');
        this.#validator.resetForm();
    }
}

export default RegistrationService;
