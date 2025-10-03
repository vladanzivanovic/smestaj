import productEditMapper from "../Mapper/ProductEditMapper";

require ('../../../../../../app/Resources/public/js/Validators/ValidationRuleHelper');

class ProductEditValidator {
    constructor() {
        this.mapper = productEditMapper;

        if (!ProductEditValidator.instance) {
            ProductEditValidator.instance = this;
        }

        return ProductEditValidator.instance;
    }

    validate() {
        let options;

        options = {
            ignore: '.note-editor *',
            rules: {
                category: {
                    isSelectBoxEmpty: true,
                },
                'tags[]': {
                    isMultiSelectBoxEmpty: true,
                },
                pre_price_from: {
                    required: true,
                    number: true,
                },
                pre_price_to: {
                    required: true,
                    number: true,
                },
                price_from: {
                    required: true,
                    number: true,
                },
                price_to: {
                    required: true,
                    number: true,
                },
                post_price_from: {
                    required: true,
                    number: true,
                },
                post_price_to: {
                    required: true,
                    number: true,
                },
                city: {
                    isSelectBoxEmpty: true,
                },
                street: {
                    required: true,
                },
                product: {
                    dropZoneHasImage: true,
                    dropZoneHasMainImage: true,
                },
                payment_date: {
                    isValidDate: true,
                },
                'contact[first_name]': {
                    required: true,
                },
                'contact[surname]': {
                    required: true,
                },
                'contact[email]': {
                    required: true,
                },
                'contact[address]': {
                    required: true,
                },
                'contact[city]': {
                    isSelectBoxEmpty: true,
                },
                'contact[telephone]': {
                    required: true,
                },
                'contact[mobile_phone]': {
                    required: true,
                }
            },
        };

        for (const [code, data] of Object.entries(LOCALES)) {
            options.rules[`title_${code}`] = {
                required: true,
            };
            options.rules[`short_description_${code}`] = {
                required: true,
            };
            options.rules[`description_${code}`] = {
                setErrorIfSummernoteIsEmpty: true,
            };
        }

        $.extend(options, window.helpBlock);

        return $(this.mapper.form).validate(options);
    }
}

const productEditValidator = new ProductEditValidator();

Object.freeze(productEditValidator);

export default productEditValidator;