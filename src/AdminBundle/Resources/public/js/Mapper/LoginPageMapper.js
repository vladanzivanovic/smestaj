class LoginPageMapper {
    constructor() {
        this.form = '#login_form';
        this.submitBtn = '#login_btn';
        this.loginEmail = '#email';
        this.loginPassword = '#password';

        if (!LoginPageMapper.instance) {
            LoginPageMapper.instance = this;
        }

        return LoginPageMapper.instance;
    }
}

const loginPageMapper = new LoginPageMapper();

Object.freeze(loginPageMapper);

export default loginPageMapper;