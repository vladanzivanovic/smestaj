require ('../../../js/Validators/ValidationRuleHelper');

class CouponsEditValidator {
    constructor() {
        if (!CouponsEditValidator.instance) {
            CouponsEditValidator.instance = this;
        }

        return CouponsEditValidator.instance;
    }

    validate(form) {
        let options;

        options = {
            rules: {
                code: 'required',
                valid_from: 'required',
                valid_to: 'required',
                discount: 'required',
            },
        };

        $.extend(options, window.helpBlock);

        return form.validate(options);
    }
}

const couponsEditValidator = new CouponsEditValidator();

Object.freeze(couponsEditValidator);

export default couponsEditValidator;