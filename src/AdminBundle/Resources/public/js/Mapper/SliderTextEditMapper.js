class SliderTextEditMapper {
    constructor() {
        if (!SliderTextEditMapper.instance) {
            this.form = '#edit_form';
            this.submitBtn = '#slider_text_submit';

            SliderTextEditMapper.instance = this;
        }

        return SliderTextEditMapper.instance;
    }
}

const sliderTextEditMapper = new SliderTextEditMapper();

Object.freeze(sliderTextEditMapper);

export default sliderTextEditMapper;