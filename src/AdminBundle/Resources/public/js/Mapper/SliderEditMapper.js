class SliderEditMapper {
    constructor() {
        this.form = $('#edit_form');
        this.descriptionRs = $('#description_rs', this.form);
        this.buttonTextRs = $('#button_rs', this.form);
        this.buttonLinkRs = $('#linkRs', this.form);
        this.descriptionEn = $('#description_en', this.form);
        this.buttonTextEn = $('#button_en', this.form);
        this.buttonLinkEn = $('#button_en', this.form);
        this.submitBtn = $('#slider_submit');
    }
}

export default SliderEditMapper;