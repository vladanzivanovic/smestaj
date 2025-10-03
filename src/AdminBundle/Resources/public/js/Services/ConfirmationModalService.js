class ConfirmationModalService {
    constructor(title, buttons, body) {
        this.modal = $('#confirmation_modal');
        this.footer = $('.modal-footer', this.modal);
        this.body = $('.modal-body', this.modal);
        this.title = $('.modal-title', this.modal);

        this.setTitle(title);
        this.setButtons(buttons);
        this.setBody(body);
    }

    setButtons(buttons) {
        this.footer.empty();

        for(let i in buttons) {
            let button = buttons[i];

            let buttonHtml = $('<button>', button);

            this.footer.append(buttonHtml);
        }
    }

    setBody(body) {
        if (!body){
            return;
        }

        this.body.empty();
        this.body.removeClass('d-none');
        this.body.append(body);
    }

    setTitle(title) {
        this.title.text(title);
    }

    trigger(event) {
        this.modal.modal(event);
    }
}

export default ConfirmationModalService;