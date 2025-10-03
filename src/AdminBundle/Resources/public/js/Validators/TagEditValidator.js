require ('../../../js/Validators/ValidationRuleHelper');

class TagEditValidator {
    constructor() {
        if (!TagEditValidator.instance) {
            TagEditValidator.instance = this;
        }

        return TagEditValidator.instance;
    }

    validate(form) {
        let options;

        options = {
            rules: {
                rs_title: 'required',
                en_title: 'required',
            },
        };

        $.extend(options, window.helpBlock);

        return form.validate(options);
    }
}

const tagEditValidator = new TagEditValidator();

Object.freeze(tagEditValidator);

export default tagEditValidator;