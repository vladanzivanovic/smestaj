require ('../../../../../../app/Resources/public/js/Validators/ValidationRuleHelper');

class CategoryEditValidator {
    constructor() {
        if (!CategoryEditValidator.instance) {
            CategoryEditValidator.instance = this;
        }

        return CategoryEditValidator.instance;
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

const categoryEditValidator = new CategoryEditValidator();

Object.freeze(categoryEditValidator);

export default categoryEditValidator;