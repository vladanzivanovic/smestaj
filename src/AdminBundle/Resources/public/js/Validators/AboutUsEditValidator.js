require ('../../../../../../app/Resources/public/js/Validators/ValidationRuleHelper');

class AboutUsEditValidator {
    constructor() {
        if (!AboutUsEditValidator.instance) {
            AboutUsEditValidator.instance = this;
        }

        return AboutUsEditValidator.instance;
    }

    validate(form) {
        let options;

        options = {
            ignore: '',
            rules: {},
        };

        for (let i = 0; i < LOCALES.length; i++) {
            let field = LOCALES[i];

            options.rules[`${field.code}_description`] = 'setErrorIfSummernoteIsEmpty';
        }

        $.extend(options, window.helpBlock);

        return $(form).validate(options);
    }
}

const aboutUsEditValidator = new AboutUsEditValidator();

Object.freeze(aboutUsEditValidator);

export default aboutUsEditValidator;