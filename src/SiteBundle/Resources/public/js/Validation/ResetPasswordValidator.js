import baseValidator from "../../../../../../app/Resources/public/js/Validators/BaseValidator";
import resetPasswordMapper from "../Mapper/ResetPasswordMapper";

class ResetPasswordValidator {
    #mapper;
    #baseValidator;

    constructor() {
        if (!ResetPasswordValidator.instance) {
            this.#mapper = resetPasswordMapper;
            this.#baseValidator = baseValidator;

            ResetPasswordValidator.instance = this;
        }

        return ResetPasswordValidator.instance;
    }

    validate() {
        let rules = {
            email:{
                required: true,
                email: true,
            },
        };

        return this.#baseValidator.validate($(this.#mapper.form), rules);
    }
};
const resetPasswordValidator = new ResetPasswordValidator();

Object.freeze(resetPasswordValidator);

export default resetPasswordValidator;
