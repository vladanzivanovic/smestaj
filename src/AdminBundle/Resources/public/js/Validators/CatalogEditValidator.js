require ('../../../js/Validators/ValidationRuleHelper');

class CatalogEditValidator {
    constructor() {
        if (!CatalogEditValidator.instance) {
            CatalogEditValidator.instance = this;
        }

        return CatalogEditValidator.instance;
    }

    validate(form) {
        let options;

        options = {
            rules: {
                rs_title: 'required',
                en_title: 'required',
                catalog: {
                    dropZoneHasImage: true,
                },
            },
        };

        $.extend(options, window.helpBlock);

        return form.validate(options);
    }
}

const catalogEditValidator = new CatalogEditValidator();

Object.freeze(catalogEditValidator);

export default catalogEditValidator;