class ShippingEditMapper {
    constructor() {
        this.form = '#edit_form';
        this.submitBtn = '#submit_button';
        this.countries = '#countries';

        if (!ShippingEditMapper.instance) {
            ShippingEditMapper.instance = this;
        }

        return ShippingEditMapper.instance;
    }
}
const shippingEditMapper = new ShippingEditMapper();
Object.freeze(shippingEditMapper);
export default shippingEditMapper;