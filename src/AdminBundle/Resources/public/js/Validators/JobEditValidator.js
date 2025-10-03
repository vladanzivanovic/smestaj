require ('../../../js/Validators/ValidationRuleHelper');

class JobEditValidator {
    constructor() {
        if (!JobEditValidator.instance) {
            JobEditValidator.instance = this;
        }

        return JobEditValidator.instance;
    }

    validate(form) {
        let options;

        options = {
            ignore: '',
            rules: {
                rs_title: 'required',
                rs_description: 'setErrorIfSummernoteIsEmpty',
                en_title: 'required',
                en_description: 'setErrorIfSummernoteIsEmpty',
                main_images: {
                    dropZoneHasImage: true,
                    dropZoneHasMainImage: true,
                }
            },
        };

        $.extend(options, window.helpBlock);

        return $(form).validate(options);
    }
}

const jobEditValidator = new JobEditValidator();

Object.freeze(jobEditValidator);

export default jobEditValidator;