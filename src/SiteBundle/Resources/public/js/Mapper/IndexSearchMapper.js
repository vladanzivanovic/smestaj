class IndexSearchMapper {
    constructor() {
        this.form = $('#index-search-form');
        this.city = $('#search-city', this.form);
        this.category = $('#search-category', this.form);
        this.button = $('#search-index-btn', this.form);
        this.error = $('.general-search__error', this.form);
    }
}

export default IndexSearchMapper;