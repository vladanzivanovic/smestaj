import shippingEditMapper from "../Mapper/ShippingEditMapper";

require ('../../../js/Validators/ValidationRuleHelper');

class ShippingEditValidator {
    constructor() {
        this.mapper = shippingEditMapper;

        if (!ShippingEditValidator.instance) {
            ShippingEditValidator.instance = this;
        }

        return ShippingEditValidator.instance;
    }

    validate() {
        let options;

        options = {
            rules: {
                price: {
                    required: true,
                    number: true
                },
                'countries[]': 'isMultiSelectBoxEmpty',
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

const shippingEditValidator = new ShippingEditValidator();

Object.freeze(shippingEditValidator);

export default shippingEditValidator;