require ('../../../../../../app/Resources/public/js/Validators/ValidationRuleHelper');

class UserValidator {
    constructor() {
        if (!UserValidator.instance) {
            UserValidator.instance = this;
        }

        return UserValidator.instance;
    }

    validate(form) {
        let options;

        options = {
            rules: {
                first_name: 'required',
                last_name: 'required',
                email:{
                    required: true,
                    email: true,
                },
                password    : {
                    minlength: 5,
                    required: () => {
                        return !IS_EDIT
                    }
                },
                role: 'isSelectBoxEmpty'
            },
        };

        $.extend(options, window.helpBlock);

        return $(form).validate(options);
    }
}

const userValidator = new UserValidator();

Object.freeze(userValidator);

export default userValidator;