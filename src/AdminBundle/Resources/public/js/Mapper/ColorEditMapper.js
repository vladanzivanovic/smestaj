class ColorEditMapper {
    constructor() {
        this.form = $('#edit_form');
        this.color = $('#color_field', this.form);
        this.titleRs = $('#color_title_rs', this.form);
        this.titleEn = $('#color_title_en', this.form);
        this.submitBtn = $('#color_submit');
    }
}

export default ColorEditMapper;