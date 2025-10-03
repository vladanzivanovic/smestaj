import baseValidator from "../../../../../../app/Resources/public/js/Validators/BaseValidator";
import loginMapper from "../Mapper/LoginMapper";

class LoginValidator {
    #mapper;
    #baseValidator;

    constructor() {
        if (!LoginValidator.instance) {
            this.#mapper = loginMapper;
            this.#baseValidator = baseValidator;

            LoginValidator.instance = this;
        }

        return LoginValidator.instance;
    }

    validate() {
        let rules = {
            _username:{
                required: true,
                email: true,
            },
            _password: {
                required: true,
            },
        };

        return this.#baseValidator.validate($(this.#mapper.form), rules);
    }
};
const loginValidator = new LoginValidator();

Object.freeze(loginValidator);

export default loginValidator;
