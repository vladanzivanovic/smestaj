require ('../../../js/Validators/ValidationRuleHelper');

class SliderTextEditValidator {
    constructor() {
        if (!SliderTextEditValidator.instance) {
            SliderTextEditValidator.instance = this;
        }

        return SliderTextEditValidator.instance;
    }

    validate(form) {
        let options;

        options = {
            rules: {
                rs_description: 'required',
                rs_link: 'required',
                en_description: 'required',
                en_link: 'required',
            },
        };

        $.extend(options, window.helpBlock);

        return $(form).validate(options);
    }
}

const sliderTextEditValidator = new SliderTextEditValidator();

Object.freeze(sliderTextEditValidator);

export default sliderTextEditValidator;