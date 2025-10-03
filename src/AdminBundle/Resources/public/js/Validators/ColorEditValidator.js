require ('../../../js/Validators/ValidationRuleHelper');

class ColorEditValidator {
    constructor() {
        if (!ColorEditValidator.instance) {
            ColorEditValidator.instance = this;
        }

        return ColorEditValidator.instance;
    }

    validate(form) {
        let options;

        options = {
            rules: {
                color: 'required',
                rs_title: 'required',
                en_title: 'required',
            },
        };

        $.extend(options, window.helpBlock);

        return form.validate(options);
    }
}

const colorEditValidator = new ColorEditValidator();

Object.freeze(colorEditValidator);

export default colorEditValidator;