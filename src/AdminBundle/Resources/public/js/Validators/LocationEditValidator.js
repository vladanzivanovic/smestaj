require ('../../../js/Validators/ValidationRuleHelper');

class LocationEditValidator {
    constructor() {
        if (!LocationEditValidator.instance) {
            LocationEditValidator.instance = this;
        }

        return LocationEditValidator.instance;
    }

    validate(form) {
        let options;

        options = {
            rules: {
                rs_title: 'required',
                rs_street: 'required',
                rs_city: 'required',
                zip_code: 'required',
                rs_country: 'required',
                rs_description: 'required',
                en_title: 'required',
                en_street: 'required',
                en_city: 'required',
                en_country: 'required',
                en_description: 'required',
                working_hours: 'required',
                working_hours_weekend: 'required',
                email: 'email',
                location: {
                    dropZoneHasImage: true,
                    dropZoneHasMainImage: true,
                }
            },
        };

        $.extend(options, window.helpBlock);

        return $(form).validate(options);
    }
}

const locationEditValidator = new LocationEditValidator();

Object.freeze(locationEditValidator);

export default locationEditValidator;