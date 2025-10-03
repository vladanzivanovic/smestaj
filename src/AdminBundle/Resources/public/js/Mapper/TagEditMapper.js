class TagEditMapper {
    constructor() {
        this.form = $('#edit_form');
        this.titleRs = $('#title_rs', this.form);
        this.titleEn = $('#title_en', this.form);
        this.submitBtn = $('#tag_submit');
    }
}

export default TagEditMapper;