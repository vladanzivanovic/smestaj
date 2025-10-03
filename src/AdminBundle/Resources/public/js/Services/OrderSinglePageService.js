import NotificationService from "../../../js/NotificationService";
import AppHelperService from "../../../../../../SiteBundle/Resources/public/js/Helper/AppHelperService";

class OrderSinglePageService {
    constructor() {
        this.notification = NotificationService();
    }

    doRequest(url) {
        this.notification.showLoadingMessage();

        $.ajax({
            type: 'GET',
            url,
            dataType: 'json',
            success: response => {
                AppHelperService.redirect('reload');
            },
            error: error => {
                const errors = error.responseJSON;

                if (errors.hasOwnProperty('message')) {
                    this.notification.show('error', errors.message, true);

                    return;
                }

                this.notification.show('error', Translator.trans('generic_error', null, 'messages'. LOCALE), true);
            }
        });
    }
}

export default OrderSinglePageService;