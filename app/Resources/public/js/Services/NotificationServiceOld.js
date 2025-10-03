import toastr from "toastr"

export default (() => {
    var Notification = {};

    Notification.showLoadingMessage = function () {
        this.show(
            'info',
            Translator.trans('notifications.please_wait', null, 'messages', LOCALE),
            true,
            '',
            {timeOut: 0}
        );
    };

    Notification.show = function(type, message, reopen = true, title, options) {
        this.reset(reopen);
        let defaultOptions = {timeOut: 3000};

        if (options) {
            defaultOptions = $.extend({}, defaultOptions, toastr.options)
        }

        this.toastr = toastr[type](message, title, defaultOptions);

        return this.toastr;
    };

    Notification.reset = function (reopen) {
        if (reopen && this.toastr) {
            toastr.options = {};
            toastr.clear(this.toastr);
        }
    };

    Notification.setOptions = (options) => {
        toastr.options = options;
    };

    Notification.remove = function() {
        toastr.clear(this.toastr);
    };

    return Notification;
});