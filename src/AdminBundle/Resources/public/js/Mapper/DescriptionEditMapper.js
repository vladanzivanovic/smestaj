class DescriptionEditMapper {
    constructor() {
        this.form = '#edit_form';
        this.typeBox = '#description_select_box';
        this.desc_rs = '#description_rs';
        this.desc_en = '#description_en';
        this.submitBtn = '#description_submit';

        if (!DescriptionEditMapper.instance) {
            DescriptionEditMapper.instance = this;
        }

        return DescriptionEditMapper.instance;
    }
}

const descriptionEditMapper = new DescriptionEditMapper();

Object.freeze(descriptionEditMapper);

export default descriptionEditMapper;