import PopupCoreService from "./PopupCoreService";
import ResetPasswordHandler from "../Handler/ResetPasswordHandler";

export default (() =>  {
    var Public = {};
    var Private = {};

    Private.resetPasswordHandler = new ResetPasswordHandler();

    tjq.extend(true, Public, PopupCoreService());

    Public.generate = function () {
        var options = {
            popupId: 'setPassword',
            title: 'Unesite novu lozinku',
        };

        var content = Private.setForm();
        var base = this.getBaseTemplate(options, content);

        tjq('body').append(base);

        tjq('#setPassword-fakeClick').soapPopup({
            wrapId: "set-password-overlay",
        });

        Private.registerEvents();
    };

    Private.setInputs = function (name, placeholder, type, value) {
        var inputWrapper = tjq('<div>', {'class': 'form-group'});
        var input = `<input type="${type}" name="${name}" class="input-text full-width" placeholder="${placeholder}"/>`;

        if (value) {
            tjq(input).val(value);
        }

        return inputWrapper.append(input);
    };

    Private.setButton = function () {
        var button = tjq('<button>', {
            text: 'Pošalji',
            id: 'set-password-button',
            'class': 'full-width btn-medium',
            type: 'button',
        });

        return button;
    };

    Private.setForm = function () {
        var wrapper = tjq('<form>', {
            'id': 'set-password-form',
            method: 'POST',
            enctype: 'multipart/form-data',
            action: '',
        });

        const password = this.setInputs('password', 'Unesite lozinku', 'password');
        const rePassword = this.setInputs('rePassword', 'Ponovite lozinku', 'password');

        return wrapper.append(password, rePassword, this.setButton());
    };

    Private.registerEvents = () => {
        $(document).on('click touchend', '#set-password-overlay', e => {
            if ($("body").hasClass("overlay-open") && !$(e.target).is(".opacity-overlay .popup-content *")) {
                location.href = Routing.generate('site_index');
            }
        });

        $(document).on('click touchend', '#set-password-button', e => {
            Private.resetPasswordHandler.setNewPassword();

            return false;
        });
    }

    return Public;
});
