
class ProductEditMapper {
    constructor() {
        this.form = '#edit_form';
        this.category = '#category';
        this.tags = '#tags';
        this.submitBtn = '#product_submit';
        this.city = '#city';
        this.street = '#street_and_number';
        this.paymentDate = '#payment_date';
        this.contactCity = '#contact_city';
        this.owner = '#owner';

        for (const [code, data] of Object.entries(LOCALES)) {
            this[`title_${code}`] = `#title_${code}`;
            this[`short_description${code}`] = `#short_description_${code}`;
            this[`description_${code}`] = `#description_${code}`;
        }

        if (!ProductEditMapper.instance) {
            ProductEditMapper.instance = this;
        }
        
        return ProductEditMapper.instance;
    }
}

const productEditMapper = new ProductEditMapper();

Object.freeze(productEditMapper);

export default productEditMapper;