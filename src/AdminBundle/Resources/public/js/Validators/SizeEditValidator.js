require ('../../../js/Validators/ValidationRuleHelper');

class SizeEditValidator {
    constructor() {
        if (!SizeEditValidator.instance) {
            SizeEditValidator.instance = this;
        }

        return SizeEditValidator.instance;
    }

    validate(form) {
        let options;

        options = {
            rules: {
                title: 'required',
            },
        };

        $.extend(options, window.helpBlock);

        return form.validate(options);
    }
}

const sizeEditValidator = new SizeEditValidator();

Object.freeze(sizeEditValidator);

export default sizeEditValidator;