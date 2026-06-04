class ContactMapper {
    constructor() {

        if (!ContactMapper.instance) {
            this.form = $(`#contact-form-ajax`);

            this.name = $(`input[name="fullName"]`, this.form);
            this.email = $(`input[name="email"]`, this.form);
            this.subject = $(`input[name="subject"]`, this.form);
            this.message = $(`textarea[name="message"]`, this.form);
            this.website = $(`#contact-website`, this.form);

            this.submitBtn = $(`#contact-submit-btn`);
            this.feedback = $(`#contact-form-feedback`);

            ContactMapper.instance = this;
        }

        return ContactMapper.instance;
    }

    fieldError(name) {
        return $(`.field-error[data-field="${name}"]`, this.form);
    }
}

const contactMapper = new ContactMapper();

Object.freeze(contactMapper);

export default contactMapper;
