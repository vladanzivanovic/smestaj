class CategoryEditMapper {
    constructor() {
        this.form = $('#edit_form');
        this.titleRs = $('#title_rs', this.form);
        this.titleEn = $('#title_en', this.form);
        this.parent = $('#parent_category', this.form);
        this.submitBtn = $('#category_submit');
    }
}

export default CategoryEditMapper;