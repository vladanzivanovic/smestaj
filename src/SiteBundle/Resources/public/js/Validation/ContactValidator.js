import baseValidator from "../../../../../../app/Resources/public/js/Validators/BaseValidator";
import contactMapper from "../Mapper/ContactMapper";

class ContactValidator {
    #mapper;
    #baseValidator;

    constructor() {
        if (!ContactValidator.instance) {
            this.#mapper = contactMapper;
            this.#baseValidator = baseValidator;

            ContactValidator.instance = this;
        }

        return ContactValidator.instance;
    }

    validate() {
        let rules = {
            fullName: {
                required: true,
                minlength: 2,
            },
            email: {
                required: true,
                email: true,
            },
            subject: {
                required: true,
                minlength: 2,
            },
            message: {
                required: true,
                minlength: 5,
            },
        };

        return this.#baseValidator.validate($(this.#mapper.form), rules);
    }
}

const contactValidator = new ContactValidator();

Object.freeze(contactValidator);

export default contactValidator;
