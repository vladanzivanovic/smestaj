import LoginService from "../Services/LoginService";
import ResetPasswordHandler from "../Handler/ResetPasswordHandler";
import toastrService from "../../../../../../app/Resources/public/js/Services/ToastrService";
import RegistrationService from "../Services/RegistrationService";
import BaseCoreController from "../../../../../../app/Resources/public/js/Controller/CoreController";

class CoreController extends BaseCoreController{
    #registrationService;
    #registrationValidator;
    #loginService;
    #resetPasswordService;

    constructor() {
        super();
        this.#registrationService = new RegistrationService();
        this.#loginService = new LoginService();
        this.#resetPasswordService = new ResetPasswordHandler();

        this.#registerEvents();
    }

    #registerEvents() {
        $(document).on('click', '#signin-button', () => {
            this.#loginService.doLogin();
        });

        $(document).on('click touchend', '#signup-btn', () => {
            this.#registrationService.doRegistration()
        });

        $(document).on('click touchend', '#reset-password-button', () => {
            this.#resetPasswordService.doReset();
        });

        $(document).on('reset-forms', e => {
            this.#registrationService.reset();
            this.#loginService.reset();
        })
    }
}

export default CoreController;
