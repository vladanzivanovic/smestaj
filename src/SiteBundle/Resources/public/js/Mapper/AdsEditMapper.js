class AdsEditMapper {
    constructor() {
        this.contact = {};

        this.originForm = '#ads-origin';
        this.adTemplate = '#ads';
        this.formWrapper = '#set_ad_wrapper';
        this.adsForm = '#setAd';
        this.wizard = '#smartwizard';
        this.title = '#title';
        this.category = '#category';
        this.city = '#city';
        this.street = '#street_and_number';
        this.website = '#website';
        this.facebook = '#facebook';
        this.instagram = '#instagram';
        this.tags = '#tags';
        this.submitBtn = '#product_submit';
        this.dismissBtn = '#dismissEditAd';
        this.city = '#city';
        this.street = '#street_and_number';
        this.contact.city = '#contact_city';
        this.contact.telephone = '#contact_phone';
        this.contact.mobile = '#contact_mobile_phone';
        this.contact.firstName = '#contact_first_name';
        this.contact.surname = '#contact_surname';
        this.contact.email = '#contact_email';
        this.contact.street = '#contact_street';
        this.contact.viber = '#contact_viber';
        this.priceFromPreSeason = '#price_from_pre_season';
        this.priceToPreSeason = '#price_to_pre_season';
        this.priceFrom = '#price_from';
        this.priceTo = '#price_to';
        this.priceFromPostSeason = '#price_from_post_season';
        this.priceToPostSeason = '#price_to_post_season';
        this.paymentType = 'input[name="price_plan"]';

        this.saveBtn = '#saveEditAds';

        for (const [code, data] of Object.entries(LOCALES)) {
            this[`title_${code}`] = `#title_${code}`;
            this[`description_${code}`] = `#description_${code}`;
        }

        if (!AdsEditMapper.instance) {
            AdsEditMapper.instance = this;
        }

        return AdsEditMapper.instance;
    }
}

const adsEditMapper = new AdsEditMapper();

Object.freeze(adsEditMapper);

export default adsEditMapper;
