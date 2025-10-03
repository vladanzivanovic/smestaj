import AppHelperService from "../../../../../../app/Resources/public/js/Helper/AppHelperService";
import loginPageMapper from "../Mapper/LoginPageMapper";
import toastrService from "../../../../../../app/Resources/public/js/Services/ToastrService";

class LoginHandler {
    #toastr;

    constructor() {
        this.mapper = loginPageMapper;
        this.#toastr = toastrService;
    }

    doLogin() {
        const urlRoute = Routing.generate(`admin.api.login_check`);
        const type = 'POST';
        const data = {
            username: $(this.mapper.loginEmail).val(),
            password: $(this.mapper.loginPassword).val(),
        };

        this.#toastr.showLoadingMessage();

        $.ajax({
            type,
            contentType: 'application/json',
            url: urlRoute,
            data: JSON.stringify(data),
            dataType: 'json',
            success: (response) => {
                AppHelperService.redirect(Routing.generate('admin.dashboard'));
            },
            error: (error) => {
                this.#toastr.error(Translator.trans(error.responseJSON.message, null, 'messages', LOCALE));
            }
        })
    }
}

export default LoginHandler;
