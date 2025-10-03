/**
 * Created by vlada on 3/15/2017.
 */

var NotifyService = function () {
    var $$options, $$settings, $$_instance;
    var $setOptions, $setSettings, $showNotify, $closeNotify;

    $$options = {
        message: MESSAGE.GENERIC.LOADER
    };
    $$settings = {
        type: 'success',
        delay: 5000,
        z_index: 10001,
        onClosed: function () {
            $closeNotify()
        },
        template: '<div data-notify="container" class="col-xs-11 col-sm-3 alert alert-{0}" role="alert">' +
        '<button type="button" aria-hidden="true" class="close" data-notify="dismiss"></button>' +
        '<span data-notify="icon"></span> ' +
        '<span data-notify="title">{1}</span> ' +
        '<span data-notify="message">{2}</span>' +
        '<div class="progress" data-notify="progressbar">' +
        '<div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>' +
        '</div>' +
        '<a href="{3}" target="{4}" data-notify="url"></a>' +
        '</div>'
    };
    $$_instance = null;

    $setOptions = function (options) {
        options = Object.assign($$options, options || {});
        return this;
    };

    $setSettings = function (settings) {
        if( typeof settings === 'boolean'){
            settings = {
                type: settings ? 'success' : 'error'
            }
        }

        $$settings.onClose = settings && settings.reload ? $$setReloadPageOnClosed(settings.reload) : null;

        settings = Object.assign($$settings, settings || {});
        return this;
    };

    $showNotify = function (title, message, update) {
        $$options.title = title;
        $$options.message = message;

        if(null === $$_instance) {
            $$_instance = tjq.notify($$options, $$settings);
        }

        if(null !== $$_instance && update){
            $$_instance.update($$options);
        }
    };

    $closeNotify = function () {
        if($$_instance) {
            $$_instance.close();
        }

        $$_instance = null;
        return this;
    };

    $$setReloadPageOnClosed = function (href) {
        $$settings.onClose = appHelper.redirect(href);
        return this;
    }

    return{
        setOptions: $setOptions,
        setSettings: $setSettings,
        showNotify: $showNotify,
        closeNotify: $closeNotify
    }
};

var notifyService = NotifyService();