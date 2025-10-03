import toastrService from "../Services/ToastrService";

class CoreController {
    #toastrService;

    constructor() {
        this.#toastrService = toastrService;
    }

    showFlashMsg() {
        if (window.Messages) {
            window.Messages.forEach(message => {
                this.#toastrService.success(message);
            });
        }
    }
}

export default CoreController;
