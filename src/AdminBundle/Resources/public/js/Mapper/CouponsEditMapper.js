class CouponsEditMapper {
    constructor() {
        if (!CouponsEditMapper.instance) {
            this.form = $('#edit_form');
            this.validFrom = $('#datePicker_valid_from');
            this.validTo = $('#datePicker_valid_to');
            this.submitBtn = $('#coupon_submit');

            CouponsEditMapper.instance = this;
        }

        return CouponsEditMapper.instance;
    }
}

const couponsEditMapper = new CouponsEditMapper();

Object.freeze(couponsEditMapper);

export default couponsEditMapper;