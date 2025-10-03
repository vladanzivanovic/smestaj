import loginPageMapper from "../Mapper/LoginPageMapper";
import LoginHandler from "../Handler/LoginHandler";

class LoginPageController {
    constructor() {
        this.mapper = loginPageMapper;
        this.handler = new LoginHandler();

        this.registerEvents();
    }

    registerEvents()
    {
        $(this.mapper.submitBtn).on('click touchend', (e) => {
            e.preventDefault();
            e.stopPropagation();

            this.handler.doLogin();
        });
        $('body').on('keyup', (e) => {
            e.preventDefault();
            e.stopPropagation();

            if (e.keyCode === 13) {
                this.handler.doLogin();
            }
        });
    }
}

export default LoginPageController;
