import countryEditMapper from "../Mapper/CountryEditMapper";

require ('../../../../../../app/Resources/public/js/Validators/ValidationRuleHelper');

class CountryEditValidator {
    constructor() {
        this.mapper = countryEditMapper;

        if (!CountryEditValidator.instance) {
            CountryEditValidator.instance = this;
        }

        return CountryEditValidator.instance;
    }

    validate() {
        let options;

        options = {
            rules: {
                code: {
                    required: true,
                    maxlength: 2
                }
            },
        };

        for (let i = 0; i < LOCALES.length; i++) {
            let field = LOCALES[i];

            options.rules[`title_${field.code}`] = 'required';
        }

        $.extend(options, window.helpBlock);

        return $(this.mapper.form).validate(options);
    }
}

const countryEditValidator = new CountryEditValidator();

Object.freeze(countryEditValidator);

export default countryEditValidator;