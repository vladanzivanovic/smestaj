import paymentEditMapper from "../Mapper/PaymentEditMapper";

require ('../../../js/Validators/ValidationRuleHelper');

class PaymentEditValidator {
    constructor() {
        this.mapper = paymentEditMapper;

        if (!PaymentEditValidator.instance) {
            PaymentEditValidator.instance = this;
        }

        return PaymentEditValidator.instance;
    }

    validate() {
        let options;

        options = {
            rules: {
                'shipping[]': 'isMultiSelectBoxEmpty',
                'payment_type': 'required',
            },
        };

        for (let i = 0; i < LOCALES.length; i++) {
            let field = LOCALES[i];

            options.rules[`title_${field.code}`] = 'required';
            options.rules[`description_${field.code}`] = 'required';
        }

        $.extend(options, window.helpBlock);

        return $(this.mapper.form).validate(options);
    }
}

const paymentEditValidator = new PaymentEditValidator();

Object.freeze(paymentEditValidator);

export default paymentEditValidator;