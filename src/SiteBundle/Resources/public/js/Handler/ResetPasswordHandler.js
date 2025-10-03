import BackendValidator from "../Validation/BackendValidator";
import resetPasswordMapper from "../Mapper/ResetPasswordMapper";
import toastrService from "../../../../../../app/Resources/public/js/Services/ToastrService";
import resetPasswordValidator from "../Validation/ResetPasswordValidator";

class ResetPasswordHandler {
    #mapper;
    #toastr;
    #validator;

    constructor() {
        this.#mapper = resetPasswordMapper;
        this.#toastr = toastrService;

        this.#validator = resetPasswordValidator.validate();
    }

    doReset() {
        if(!this.#mapper.form.valid()){
            return false;
        }

        const emailVal = this.#mapper.email.val();

        this.#toastr.showLoadingMessage();

        $.ajax({
            type: 'PUT',
            url: Routing.generate('site_user_reset_password', {email: emailVal}),
            dataType: 'json'
        })
            .then(response => {
                this.#toastr.success(Translator.trans('data_success_send', null, 'messages', LOCALE));

                tjq('.opacity-overlay').click();

                this.#mapper.email.val('');

                return true;
            })
            .fail(error => {
                let errors = error.responseJSON;

                this.#toastr.error(errors);

            });

        return true;
    };

    setNewPassword() {
        const params = $.parseParams(location.href);
        let formData = $(`#set-password-form`).serializeArray();

        this.#toastr.showLoadingMessage();

        $.post(Routing.generate('site_user_set_password', {token: params.token}), formData)
            .then(() => {
                location.href = Routing.generate('site_index');
            })
            .fail(errors => {
                BackendValidator().validate(this.#mapper.setForm, errors.responseJSON);
            });
    };

    reset() {
        this.#mapper.form.trigger('reset');
        this.#validator.resetForm();
    }
};

export default ResetPasswordHandler;
