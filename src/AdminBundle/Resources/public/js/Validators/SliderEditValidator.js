require ('../../../../../../app/Resources/public/js/Validators/ValidationRuleHelper');

class SliderEditValidator {
    constructor() {
        if (!SliderEditValidator.instance) {
            SliderEditValidator.instance = this;
        }

        return SliderEditValidator.instance;
    }

    validate(form) {
        let options;

        options = {
            ignore: '',
            rules: {
                rs_button: 'required',
                rs_link: 'required',
                en_button: 'required',
                en_link: 'required',
                slider: {
                    dropZoneHasImage: true,
                    dropZoneHasMainImage: true,
                },
                slider_mobile: {
                    dropZoneHasImage: true,
                    dropZoneHasMainImage: true,
                }
            },
        };

        $.extend(options, window.helpBlock);

        return form.validate(options);
    }
}

const sliderEditValidator = new SliderEditValidator();

Object.freeze(sliderEditValidator);

export default sliderEditValidator;