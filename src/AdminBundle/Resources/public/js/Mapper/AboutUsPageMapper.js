class AboutUsPageMapper {
    constructor() {
        this.form = '#edit_form';

        for (let i = 0; i < LOCALES.length; i++) {
            let field = LOCALES[i];

            this[`desc_${field.code}`] = '#about_us_description_'+field.code;
        }

        this.submitBtn = '#about_us_submit';

        if (!AboutUsPageMapper.instance) {
            AboutUsPageMapper.instance = this;
        }

        return AboutUsPageMapper.instance;
    }
}

const aboutUsPageMapper = new AboutUsPageMapper();

Object.freeze(aboutUsPageMapper);

export default aboutUsPageMapper;