require ('../../../../../../app/Resources/public/js/Validators/ValidationRuleHelper');

class BannerEditValidator {
    constructor() {
        if (!BannerEditValidator.instance) {
            BannerEditValidator.instance = this;
        }

        return BannerEditValidator.instance;
    }

    validate(form) {
        let options;

        options = {
            rules: {
                rs_button: 'required',
                rs_link: 'required',
                en_button: 'required',
                en_link: 'required',
                position: 'required',
                banner: {
                    dropZoneHasImage: true,
                    dropZoneHasMainImage: true,
                },
                banner_mobile: {
                    dropZoneHasImage: true,
                    dropZoneHasMainImage: true,
                }
            },
        };

        $.extend(options, window.helpBlock);

        return form.validate(options);
    }
}

const bannerEditValidator = new BannerEditValidator();

Object.freeze(bannerEditValidator);

export default bannerEditValidator;