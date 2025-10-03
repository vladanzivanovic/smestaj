class LoginMapper {
    constructor() {

        if (!LoginMapper.instance) {
            this.form = $(`#login_form_ajax`);

            this.email = $(`#username`, this.form);
            this.password = $(`#password`, this.form);

            LoginMapper.instance = this;
        }

        return LoginMapper.instance;
    }
}

const loginMapper = new LoginMapper();

Object.freeze(loginMapper);

export default loginMapper;
