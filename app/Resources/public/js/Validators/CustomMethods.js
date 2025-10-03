import validator from 'jquery-validation';

let moment = require('moment');

$.validator.addMethod(
    'isHigherOrEqualThen',
    function (val, elm, params) {
        return val > $(params.selector).val();
    },
    function (params, elm) {
        var compareToId = $(elm).attr('id'),
            compareFromId = $(params.selector).attr('id');

        const field = $(`label[for="${compareToId}"]`).text();
        const comparedField = $(`label[for="${compareFromId}"]`).text();

        return Translator.trans('fields.is_lower_then', {field, comparedField}, 'validators', LOCALE);
    }
);

$.validator.addMethod('isValidDate', function (val, elm, params) {
    if (!elm.value) {
        return true;
    }
    const dateFormat = "DD.MM.YYYY";

    return moment(val, dateFormat, true).isValid();
}, function (params, elm) {
    return Translator.trans('fields.date_not_valid', null, 'validators', LOCALE);
});

$.validator.addMethod('dateFromTo', function (val, elm, params) {
    if (!elm.value || $(params.selector).val().length === 0) {
        return true;
    }

    return moment(val, "DD.MM.YYYY").isBefore(moment($(params.selector).val(), "DD.MM.YYYY"));
}, function (params, elm) {
    const field = params.names[0];
    const comparedField = params.names[1];

    return Translator.trans('fields.date_is_higher_then', {'fromDate': field, 'toDate': comparedField}, 'validators', LOCALE);
});

$.validator.addMethod('dateToFrom', function (val, elm, params) {
    var fromElm = $(params.selector)[0];
    var fromParams = {
        selector: '#'+elm.id,
        names: params.names
    };

    return $.validator.methods.dateFromTo(fromElm.value, fromElm, fromParams);
}, function (params, elm) {
    const field = params.names[0];
    const comparedField = params.names[1];

    return Translator.trans('fields.date_is_higher_then', {'fromDate': field, 'toDate': comparedField}, 'validators', LOCALE);
});

$.validator.addMethod(
    'isSelectBoxEmpty',
    function (val, elm, params) {
        return (val && val != -1) || val != -1;
    }, Translator.trans('fields.not_blank', null, 'validators', LOCALE)
);

$.validator.addMethod(
    'isMultiSelectBoxEmpty',
    function (val, elm, params) {
        return val && val.length > 0;
    }, Translator.trans('fields.not_blank', null, 'validators', LOCALE)
);

$.validator.addMethod(
    'checkPhoneInText',
    function (val, elm, params) {
        var regEx = new RegExp(/(\+)?(\d{1,3})([ ]|[\/._-])(\d{1,3})[-._-\s]?(\d{2,4})[-._-\s](\d{2,4})/g);
        return !regEx.test(val);
    }, Translator.trans('fields.phone_not_allowed', null, 'validators', LOCALE)
);

$.validator.addMethod(
    'checkEmailInText',
    function (val, elm, params) {
        var regEx = /(?:[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])/g;
        return !regEx.test(val);

    }, Translator.trans('fields.email_not_allowed', null, 'validators', LOCALE)
);

$.validator.addMethod(
    'checkWebInText',
    function (val, elm, params) {
        var regEx = /(:\/\/|(http):\/\/www\.|https:\/\/www\.|(htt)(:\/\/)|https:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?/gm;

        return !regEx.test(val);

    }, Translator.trans('fields.web_not_allowed', null, 'validators', LOCALE)
);

$.validator.addMethod('backEndValidation', function (value, element, params) {
    return this.optional(element);
});

$.validator.addMethod(
    'dropZoneHasImage',
    function (val, elm, params) {
        return elm.parentElement.lastElementChild.children.length > 0;
    }, Translator.trans('fields.images_required', null, 'validators', LOCALE)
);

$.validator.addMethod(
    'dropZoneHasMainImage',
    function (val, elm, params) {
        const imageList = elm.parentElement.lastElementChild.children;

        return Array.from(imageList).findIndex(image => image.classList.contains('main-image')) > -1;

    }, Translator.trans('fields.main_image_required', null, 'validators', LOCALE)
);

$.validator.addMethod(
    'setErrorOnCheckedNotification',
    function (val, elm, params) {
        const notification = $(`${params.selector}:checked`).get(0);

        if (notification != undefined) {
            switch (params.type) {
                case 'email':
                    if (notification.value == 2 || notification.value == 4) {
                        return !!val;
                    }
                    break;
                case 'phone':
                    if (notification.value == 1 || notification.value == 3 || notification.value == 4) {
                        return !!val;
                    }
                    break;
            }
        }

        return true;

    }, Translator.trans('fields.required', null, 'validators', LOCALE)
);

$.validator.addMethod(
    'setErrorIfAnyIsChecked',
    function (val, elm, params) {
        const notification = $(`${params.selector}:checked`).get(0);

        if (notification != undefined && notification.value == 4) {
            return !!val;
        }
        return true;

    }, Translator.trans('fields.required', null, 'validators', LOCALE)
);

$.validator.addMethod(
    'requiredOnDemand',
    function (val, elm, params) {
        if ($(`${params.selector}:checked`).length > 0) {
            return !!val;
        }
        return true;
    }, Translator.trans('fields.not_blank', null, 'validators', LOCALE)
);

$.validator.addMethod(
    'setErrorIfSummernoteIsEmpty',
    function (val, elm, params) {

        return (val !== "<p><br></p>" && val !== "<p></p>" && val !== '')
    }, Translator.trans('fields.not_blank', null, 'validators', LOCALE)
);

$.validator.addMethod(
    'fieldsGroupValidation',
    function (val, elm, params) {
        let isValid = false;

        $.each($(`[data-group="${params.group}"]`), function (i, elm) {
            if ($(elm).val() && $(elm).val().length > 0) {
                isValid = true;
                return;
            }

            isValid = false;
        })

        return isValid;
    }, Translator.trans('fields.not_blank', null, 'validators', LOCALE)
);

$.validator.addMethod(
    'cityNotExists',
    function (val, elm, params) {
        let isValid = false;
        let cityService = params.cityService;
        let city = cityService.getCityByParam(val);

        if (null === city) {
            return false;
        }

        return true;
    }, Translator.trans('fields.city_not_exits', null, 'validators', LOCALE)
);
