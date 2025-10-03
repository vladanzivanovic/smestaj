require ('../../../../../../app/Resources/public/js/Validators/ValidationRuleHelper');

class BlogEditValidator {
    constructor() {
        if (!BlogEditValidator.instance) {
            BlogEditValidator.instance = this;
        }

        return BlogEditValidator.instance;
    }

    validate(form) {
        let options;

        options = {
            ignore: '',
            rules: {
                rs_title: 'required',
                rs_short_description: 'required',
                rs_description: 'setErrorIfSummernoteIsEmpty',
                en_title: 'required',
                en_short_description: 'required',
                en_description: 'setErrorIfSummernoteIsEmpty',
                'tags[]': 'isMultiSelectBoxEmpty',
                main_images: {
                    dropZoneHasImage: true,
                    dropZoneHasMainImage: true,
                }
            },
        };

        $.extend(options, window.helpBlock);

        return form.validate(options);
    }
}

const blogEditValidator = new BlogEditValidator();

Object.freeze(blogEditValidator);

export default blogEditValidator;