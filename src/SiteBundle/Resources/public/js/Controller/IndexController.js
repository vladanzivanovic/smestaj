import IndexSearchService from "../Services/IndexSearchService";
import PopupSetPassword from "../HtmlService/PopupSetPassword";

const Private = Symbol('private');

class IndexController {

    constructor() {
        IndexSearchService().init();
        this.showResetPasswordModal();
    };

    showResetPasswordModal() {
        const params = $.parseParams(location.href);

        if (!params.token) {
            return false;
        }

        PopupSetPassword().generate();
    }
};

export default IndexController;