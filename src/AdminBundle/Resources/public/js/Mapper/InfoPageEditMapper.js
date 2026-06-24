class InfoPageEditMapper {
    constructor() {
        this.form = '#info-page-edit-form';
        this.slug = '#slug';
        this.propertyName = '#propertyName';
        this.published = '#published';
        this.hostFirstName = '#host_first_name';
        this.hostLastName = '#host_last_name';
        this.hostMobile = '#host_mobile';
        this.facebookUrl = '#facebook_url';
        this.instagramUrl = '#instagram_url';
        this.whatsappPhone = '#whatsapp_phone';
        this.viberPhone = '#viber_phone';
        this.bookingUrl = '#booking_url';
        this.airbnbUrl = '#airbnb_url';
        this.googleReviewInput = '#google_review_input';
        this.addressStreet = '#address_street';
        this.addressPostalCode = '#address_postal_code';
        this.addressCity = '#address_city';
        this.googleMapsLat = '#lat';
        this.googleMapsLng = '#lng';
        this.checkInTime = '#check_in_time';
        this.checkOutTime = '#check_out_time';
        this.wifiUsername = '#wifi_username';
        this.wifiPassword = '#wifi_password';
        this.houseRules = '#house_rules';
        this.adsAutocomplete = '#info-page-ads-autocomplete';
        this.existingWarning = '#info-page-existing-warning';
        this.submitBtn = '#info_page_submit';
        this.slugChangeWarning = '#slug-change-warning';

        if (!InfoPageEditMapper.instance) {
            InfoPageEditMapper.instance = this;
        }

        return InfoPageEditMapper.instance;
    }

    formToFormData(form) {
        const data = new FormData();
        const $form = $(form);

        $form.find('input, select, textarea').each((index, element) => {
            const $el = $(element);
            const name = $el.attr('name');

            if (undefined === name || null === name || '' === name) {
                return;
            }

            const type = (element.type || '').toLowerCase();

            if ('file' === type) {
                return;
            }

            if ('checkbox' === type) {
                if (true === element.checked) {
                    data.append(name, element.value);
                }
                return;
            }

            if ('radio' === type) {
                if (true === element.checked) {
                    data.append(name, element.value);
                }
                return;
            }

            data.append(name, $el.val() ?? '');
        });

        return data;
    }

    fileFormData(form, fileInputName) {
        const data = new FormData();
        const $form = $(form);
        const fileInput = $form.find(`input[name="${fileInputName}"]`)[0];

        if (undefined === fileInput || null === fileInput) {
            return data;
        }

        if (undefined === fileInput.files || null === fileInput.files || 0 === fileInput.files.length) {
            return data;
        }

        for (let i = 0; i < fileInput.files.length; i++) {
            data.append(fileInputName, fileInput.files[i]);
        }

        return data;
    }
}

const infoPageEditMapper = new InfoPageEditMapper();

Object.freeze(infoPageEditMapper);

export default infoPageEditMapper;
