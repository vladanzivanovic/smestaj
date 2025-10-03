
require ('./ValidationRuleHelper');

class BaseValidator {
    constructor() {
        if (!BaseValidator.instance) {
            BaseValidator.instance = this;
        }

        return BaseValidator.instance;
    }

    /**
     *
     * @param {jquery} element
     * @param {Object} rules
     * @param {string} ignore
     * @returns {*|jQuery}
     */
    validate(element, rules, ignore = '') {
        let options;

        options = {
            ignore,
            rules
        };

        $.extend(options, window.helpBlock);

        return element.validate(options);
    }
}

const baseValidator = new BaseValidator();

Object.freeze(baseValidator);

export default baseValidator;
