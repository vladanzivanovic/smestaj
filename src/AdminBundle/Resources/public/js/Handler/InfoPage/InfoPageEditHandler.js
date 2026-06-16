import infoPageEditMapper from "../../Mapper/InfoPageEditMapper";
import infoPageValidator from "../../Validators/InfoPageEditValidator";
import AppHelperService from "../../../../../../../app/Resources/public/js/Helper/AppHelperService";
import DropZoneService from "../../../../../../../app/Resources/public/js/Services/DropZoneService";
import toastrService from "../../../../../../../app/Resources/public/js/Services/ToastrService";

class InfoPageEditHandler {
    #toastr;

    constructor(validator) {
        this.mapper = infoPageEditMapper;
        this.validator = validator;
        this.#toastr = toastrService;
    }

    loadCopyPayload(adsId) {
        return $.ajax({
            type: 'GET',
            url: Routing.generate('admin.api.info_pages.copy_payload', { adsId }),
            dataType: 'json'
        });
    }

    createFromAds(adsId, copyData) {
        const payload = new FormData();
        payload.append('linkedAdsId', adsId);
        payload.append('copyData', true === copyData ? '1' : '0');

        this.#toastr.showLoadingMessage();

        return $.ajax({
            type: 'POST',
            url: Routing.generate('admin.api.info_pages.create'),
            data: payload,
            processData: false,
            contentType: false,
            dataType: 'json'
        }).done((response) => {
            this.#toastr.remove();

            if (undefined !== response && null !== response && undefined !== response.id) {
                AppHelperService.redirect(Routing.generate('admin.info_pages.edit', { id: response.id }));
            }
        }).fail((error) => {
            this.#toastr.remove();
            console.error('InfoPageEditHandler.createFromAds failed', error);
            this.#handleAjaxError(error);
        });
    }

    submitForm() {
        const $form = $(this.mapper.form);
        const formElement = $form[0];

        if (undefined === formElement || null === formElement) {
            return $.Deferred().reject().promise();
        }

        this.validator.resetForm();

        const isValid = $(this.mapper.form).valid();

        if (false === isValid) {
            this.#toastr.error('Ispravite označena polja pre slanja.');

            return $.Deferred().reject({ violations: {} }).promise();
        }

        const $houseRules = $(this.mapper.houseRules);

        if (0 < $houseRules.length && undefined !== $houseRules.summernote) {
            $houseRules.val($houseRules.summernote('code'));
        }

        const formData = this.mapper.formToFormData(formElement);

        formData.append('google_maps_lat', $(this.mapper.googleMapsLat).val() ?? '');
        formData.append('google_maps_lng', $(this.mapper.googleMapsLng).val() ?? '');

        formData.append('uploadedImages', JSON.stringify(this.#collectDropzoneFiles()));
        formData.append('removedImageIds', JSON.stringify(this.#collectRemovedImageIds()));

        this.#toastr.showLoadingMessage();

        return $.ajax({
            type: 'POST',
            url: Routing.generate('admin.api.info_pages.update', { id: ID }),
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json'
        }).done((response) => {
            this.#toastr.remove();

            if (undefined !== response.violations && null !== response.violations && 0 < Object.keys(response.violations).length) {
                this.#applyServerViolations(response.violations);

                return;
            }

            this.#toastr.success(Translator.trans('saved', null, 'messages', LOCALE));
        }).fail((error) => {
            this.#toastr.remove();
            this.#handleAjaxError(error);
        });
    }

    togglePublish(id, target) {
        const payload = new FormData();
        payload.append('published', true === target ? '1' : '0');

        this.#toastr.showLoadingMessage();

        return $.ajax({
            type: 'POST',
            url: Routing.generate('admin.api.info_pages.toggle_publish', { id }),
            data: payload,
            processData: false,
            contentType: false,
            dataType: 'json'
        }).done((response) => {
            this.#toastr.remove();

            if (true === response.ok) {
                this.#toastr.success(Translator.trans('saved', null, 'messages', LOCALE));
                return;
            }

            const message = (undefined !== response.violations && 0 < Object.keys(response.violations).length)
                ? Translator.trans('info_page.error_publish_blocked', null, 'messages', LOCALE)
                : Translator.trans('generic_error', null, 'messages', LOCALE);

            this.#toastr.error(message);
        }).fail((error) => {
            this.#toastr.remove();
            this.#handleAjaxError(error);
        });
    }

    requestDelete(id, token) {
        this.#toastr.showLoadingMessage();

        const url = Routing.generate('admin.api.info_pages.remove', { id }) + '?confirm=' + encodeURIComponent(token || '');

        return $.ajax({
            type: 'DELETE',
            url: url,
            dataType: 'json'
        }).done(() => {
            this.#toastr.remove();
        }).fail((error) => {
            this.#toastr.remove();
            this.#handleAjaxError(error);
        });
    }

    #collectDropzoneFiles() {
        const dropzoneFiles = DropZoneService().getFilesArray('info_page_images');

        return Array.isArray(dropzoneFiles) ? dropzoneFiles : [];
    }

    #collectRemovedImageIds() {
        const removed = [];

        $('input[name="removed_image_id[]"]').each((index, input) => {
            const value = parseInt(input.value, 10);

            if (false === isNaN(value)) {
                removed.push(value);
            }
        });

        return removed;
    }

    #applyServerViolations(violations) {
        // Frozen contract — see IMPLEMENTATION_PLAN.md §3.1. Adding a new server-side violation key requires a corresponding DOM-name entry here. 'images' is handled separately because the dropzone has no input name.
        const FIELD_MAP = {
            slug: 'slug',
            propertyName: 'propertyName',
            hostFirstName: 'host_first_name',
            hostLastName: 'host_last_name',
            hostMobile: 'host_mobile',
            facebookUrl: 'facebook_url',
            instagramUrl: 'instagram_url',
            addressStreet: 'address_street',
            addressCity: 'address_city',
            checkInTime: 'check_in_time',
            checkOutTime: 'check_out_time',
            wifiUsername: 'wifi_username',
            wifiPassword: 'wifi_password',
            houseRules: 'house_rules'
        };

        const errorMap = {};
        let imageError = null;

        for (const key in violations) {
            if (false === Object.prototype.hasOwnProperty.call(violations, key)) {
                continue;
            }

            const message = violations[key];

            if ('images' === key) {
                imageError = message;
                continue;
            }

            if (true === Object.prototype.hasOwnProperty.call(FIELD_MAP, key)) {
                errorMap[FIELD_MAP[key]] = message;
                continue;
            }

            errorMap[key] = message;
        }

        if (null !== this.validator && 0 < Object.keys(errorMap).length) {
            this.validator.showErrors(errorMap);
        }

        $('.js-info-page-image-error').remove();

        if (null !== imageError) {
            const $dropzone = $('[data-files="info_page_images"]');
            $dropzone.parent().addClass('dropzone--error');
            $dropzone.parent().after(`<em class="help-block js-info-page-image-error">${imageError}</em>`);
        }

        this.#toastr.error('Server je odbio podatke. Ispravite označena polja.');
    }

    #handleAjaxError(error) {
        if (undefined === error || null === error) {
            this.#toastr.error(Translator.trans('generic_error', null, 'messages', LOCALE));
            return;
        }

        const body = error.responseJSON;

        if (undefined !== body && null !== body && undefined !== body.violations && 0 < Object.keys(body.violations).length) {
            this.#applyServerViolations(body.violations);
            return;
        }

        if (undefined !== body && null !== body && undefined !== body.error) {
            this.#toastr.error(body.error);
            return;
        }

        this.#toastr.error(Translator.trans('generic_error', null, 'messages', LOCALE));
    }
}

export default InfoPageEditHandler;
