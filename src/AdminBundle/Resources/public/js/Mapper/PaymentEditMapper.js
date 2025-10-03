class PaymentEditMapper {
    constructor() {
        this.form = '#edit_form';
        this.shipping = '#shipping';
        this.submitBtn = '#submit_button';

        if (!PaymentEditMapper.instance) {
            PaymentEditMapper.instance = this;
        }

        return PaymentEditMapper.instance;
    }
}
const paymentEditMapper = new PaymentEditMapper();
Object.freeze(paymentEditMapper);
export default paymentEditMapper;