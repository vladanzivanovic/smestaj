import DropZoneService from "../../../../../../app/Resources/public/js/Services/DropZoneService";
import adsEditMapper from "../Mapper/AdsEditMapper";

require ('../../../../../../app/Resources/public/js/Validators/ValidationRuleHelper');

export default (() => {
    let Public = {};
    const mapper = adsEditMapper;
    const dropzone = DropZoneService();

    Public.adsValidation = (cityService) => {
        let options = {
            ignore: '.ignore-step *, .note-editor *',
            rules: {
                category: {
                    isSelectBoxEmpty: true,
                },
                place: {
                    required: true,
                    cityNotExists: {cityService}
                },
                street: {
                    required: true,
                },
                pre_price_from: {
                    required: true,
                    number: true
                },
                pre_price_to: {
                    required: true,
                    number: true
                },
                price_from: {
                    required: true,
                    number: true
                },
                price_to: {
                    required: true,
                    number: true
                },
                post_price_from: {
                    required: true,
                    number: true
                },
                post_price_to: {
                    required: true,
                    number: true
                },
                'contact[first_name]': {
                    required: true,
                },
                'contact[surname]': {
                    required: true,
                },
                'contact[contact_email]': {
                    required: true,
                },
                'contact[city]': {
                    required: true,
                    cityNotExists: {cityService}
                },
                'contact[mobile_phone]': {
                    required: true,
                },
                'contact[address]': {
                    required: true,
                },
                'contact[telephone]': {
                    required: true,
                },
                ads: {
                    dropZoneHasImage: true,
                    dropZoneHasMainImage: true,
                },
                price_plan: {
                    required: true
                },
            },
        };

        for (const [code, data] of Object.entries(LOCALES)) {
            options.rules[`title_${code}`] = {
                required: true,
            };
            options.rules[`description_${code}`] = {
                setErrorIfSummernoteIsEmpty: true,
            };
        }

        $.extend(options, window.helpBlock);

        return $(mapper.adsForm).validate(options);
    };

    Public.validateImages = (name) => {
        const files = dropzone.getFilesArray(name).filter(file => !file.isDeleted);
        const mainFile = dropzone.getMainFile(name);

        if (files.length === 0) {
            return false
        }

        if (!mainFile) {
            return false;
        }

        return true;
    };

    return Public;
});
