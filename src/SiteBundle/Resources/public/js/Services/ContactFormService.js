// Uses native fetch for JSON contract clarity (modern code path, distinct from
// the legacy jQuery $.post pattern used by LoginService).
import contactMapper from "../Mapper/ContactMapper";
import contactValidator from "../Validation/ContactValidator";
import toastrService from "../../../../../../app/Resources/public/js/Services/ToastrService";

class ContactFormService {
    #mapper;
    #toastr;
    #validator;

    constructor() {
        this.#mapper = contactMapper;
        this.#toastr = toastrService;
        this.#validator = contactValidator.validate();

        this.#mapper.submitBtn.on('click', () => this.submit());
    }

    submit() {
        this.#clearFeedback();

        if (!this.#mapper.form.valid()) {
            return;
        }

        this.#toastr.showLoadingMessage();

        const payload = {
            name: this.#mapper.name.val() || '',
            email: this.#mapper.email.val() || '',
            subject: this.#mapper.subject.val() || '',
            message: this.#mapper.message.val() || '',
            website: this.#mapper.website.val() || '',
        };

        fetch(Routing.generate('site_send_contact_us'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        })
            .then(async (response) => {
                if (204 === response.status) {
                    this.#handleSuccess('Hvala, vaša poruka je primljena.');
                    return;
                }

                let body = null;
                try {
                    body = await response.json();
                } catch (e) {
                    body = null;
                }

                if (response.ok) {
                    const msg = (body && body.message) ? body.message : 'Vaša poruka je uspešno poslata.';
                    this.#handleSuccess(msg);
                    return;
                }

                if (422 === response.status && body && body.errors) {
                    this.#renderFieldErrors(body.errors);
                    this.#renderGlobalError(body.message || 'Molimo proverite unete podatke.');
                    this.#toastr.error(body.message || 'Molimo proverite unete podatke.');
                    return;
                }

                const errMsg = (body && body.message)
                    ? body.message
                    : 'Trenutno ne možemo poslati vašu poruku. Pokušajte ponovo kasnije.';
                this.#renderGlobalError(errMsg);
                this.#toastr.error(errMsg);
            })
            .catch(() => {
                const errMsg = 'Greška u komunikaciji sa serverom.';
                this.#renderGlobalError(errMsg);
                this.#toastr.error(errMsg);
            });
    }

    #handleSuccess(message) {
        this.#mapper.feedback
            .removeClass('contact-feedback-error')
            .addClass('contact-feedback-success')
            .text(message);
        this.#toastr.success(message);
        this.#mapper.form.trigger('reset');
        this.#validator.resetForm();
    }

    #renderFieldErrors(errors) {
        Object.keys(errors).forEach((field) => {
            const messages = errors[field];
            const text = Array.isArray(messages) ? messages.join(' ') : String(messages);
            this.#mapper.fieldError(field).text(text);
        });
    }

    #renderGlobalError(message) {
        this.#mapper.feedback
            .removeClass('contact-feedback-success')
            .addClass('contact-feedback-error')
            .text(message);
    }

    #clearFeedback() {
        this.#mapper.feedback
            .removeClass('contact-feedback-success contact-feedback-error')
            .text('');
        $('.field-error', this.#mapper.form).text('');
    }
}

export default ContactFormService;
