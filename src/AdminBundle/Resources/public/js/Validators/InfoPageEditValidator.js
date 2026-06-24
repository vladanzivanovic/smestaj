// InfoPage admin form validation — jquery-validation wiring.
// Pattern source: src/SiteBundle/Resources/public/js/Validation/AdsValidation.js (canonical).
// Required-ness driven by Doctrine nullable=false on AdsInfoPage and AdsInfoPageImage
// per CONTEXT_SPEC.md answer 2.
// Serbian messages are hardcoded per spec answer 4 — do NOT migrate to Bazinga JS.
// The window.helpBlock global (app/Resources/public/js/Validators/ValidationRuleHelper.js)
// owns DOM placement/styling — do NOT replace with a bespoke renderer (spec Must-Not).
import infoPageEditMapper from "../Mapper/InfoPageEditMapper";

require('../../../../../../app/Resources/public/js/Validators/ValidationRuleHelper');

export default (() => {
    let Public = {};
    const mapper = infoPageEditMapper;

    Public.infoPageValidation = () => {
        let options = {
            ignore: '',
            rules: {
                slug: {
                    required: true,
                    maxlength: 255,
                    pattern: /^[a-z0-9-]+$/
                },
                propertyName: {
                    required: true,
                    maxlength: 255
                },
                host_first_name: { maxlength: 100 },
                host_last_name:  { maxlength: 100 },
                host_mobile:     { maxlength: 50 },
                facebook_url:    { url: true, maxlength: 255 },
                instagram_url:   { url: true, maxlength: 255 },
                whatsapp_phone:  { pattern: /^\+?[0-9 ()-]{7,30}$/, maxlength: 50 },
                viber_phone:     { pattern: /^\+?[0-9 ()-]{7,30}$/, maxlength: 50 },
                booking_url:     { url: true, maxlength: 255 },
                airbnb_url:      { url: true, maxlength: 255 },
                google_review_input: { maxlength: 500 },
                address_street:  { maxlength: 255 },
                address_postal_code: { maxlength: 30 },
                address_city:    { maxlength: 150 },
                check_in_time:   { time: true },
                check_out_time:  { time: true },
                wifi_username:   { maxlength: 100 },
                wifi_password:   { maxlength: 100 }
            }
        };

        for (const [code, data] of Object.entries(LOCALES)) {
            // Locale-paired fields (tagline/short_description/welcome_message) are Doctrine-nullable text with no length cap; intentionally no rules.
        }

        // Serbian-only hardcoded per CONTEXT_SPEC.md answer 4. Do NOT migrate to Bazinga JS translations without explicit re-approval.
        options.messages = {
            slug: {
                required: 'Slug je obavezan.',
                maxlength: $.validator.format('Slug može imati najviše {0} karaktera.'),
                pattern: 'Slug može sadržati samo mala slova (a-z), cifre (0-9) i crtice (-).'
            },
            propertyName: {
                required: 'Naziv objekta je obavezan.',
                maxlength: $.validator.format('Naziv objekta može imati najviše {0} karaktera.')
            },
            host_first_name: { maxlength: $.validator.format('Ime može imati najviše {0} karaktera.') },
            host_last_name:  { maxlength: $.validator.format('Prezime može imati najviše {0} karaktera.') },
            host_mobile:     { maxlength: $.validator.format('Mobilni telefon može imati najviše {0} karaktera.') },
            facebook_url:    { url: 'Unesite ispravan Facebook URL.',  maxlength: $.validator.format('URL može imati najviše {0} karaktera.') },
            instagram_url:   { url: 'Unesite ispravan Instagram URL.', maxlength: $.validator.format('URL može imati najviše {0} karaktera.') },
            whatsapp_phone:  { pattern: 'Unesite ispravan WhatsApp telefon (npr. +381 60 1234567).', maxlength: $.validator.format('Telefon može imati najviše {0} karaktera.') },
            viber_phone:     { pattern: 'Unesite ispravan Viber telefon (npr. +381 60 1234567).',    maxlength: $.validator.format('Telefon može imati najviše {0} karaktera.') },
            booking_url:     { url: 'Unesite ispravan Booking URL.',   maxlength: $.validator.format('URL može imati najviše {0} karaktera.') },
            airbnb_url:      { url: 'Unesite ispravan Airbnb URL.',    maxlength: $.validator.format('URL može imati najviše {0} karaktera.') },
            google_review_input: { maxlength: $.validator.format('Polje može imati najviše {0} karaktera.') },
            address_street:  { maxlength: $.validator.format('Ulica može imati najviše {0} karaktera.') },
            address_postal_code: { maxlength: $.validator.format('Poštanski broj može imati najviše {0} karaktera.') },
            address_city:    { maxlength: $.validator.format('Grad može imati najviše {0} karaktera.') },
            check_in_time:   { time: 'Unesite vreme u formatu HH:MM (00:00 – 23:59).' },
            check_out_time:  { time: 'Unesite vreme u formatu HH:MM (00:00 – 23:59).' },
            wifi_username:   { maxlength: $.validator.format('Wi-Fi korisničko ime može imati najviše {0} karaktera.') },
            wifi_password:   { maxlength: $.validator.format('Wi-Fi lozinka može imati najviše {0} karaktera.') }
        };

        $.extend(options, window.helpBlock);

        return $(mapper.form).validate(options);
    };

    return Public;
});
