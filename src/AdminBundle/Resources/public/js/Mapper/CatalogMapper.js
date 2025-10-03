class CatalogMapper {
    constructor() {
        this.form = '#edit_form';
        this.submitBtn = '#catalog_submit';

        if (!CatalogMapper.instance) {
            CatalogMapper.instance = this;
        }

        return CatalogMapper.instance;
    }
}
const catalogMapper = new CatalogMapper();
Object.freeze(catalogMapper);
export default catalogMapper;