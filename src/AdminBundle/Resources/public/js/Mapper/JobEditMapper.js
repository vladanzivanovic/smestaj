class JobEditMapper {
    constructor() {
        if (! JobEditMapper.instance) {
            this.form = '#edit_form';
            this.title_rs = '#job_title_rs';
            this.desc_rs = '#job_description_rs';
            this.title_en = '#job_title_en';
            this.desc_en = '#job_description_en';
            this.submitBtn = '#job_submit';

            JobEditMapper.instance = this;
        }

        return JobEditMapper.instance;
    }
}

const jobEditMapper = new JobEditMapper();

Object.freeze(jobEditMapper);

export default jobEditMapper;