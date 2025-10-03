import PopupCoreService from "./PopupCoreService";

export default (() =>  {
    var Public = {};
    var Internal = {};
    Internal.cityName = '';

    tjq.extend(true, Public, PopupCoreService());

    Public.generate = function (cityName) {
        var options = {
            popupId: 'addCity',
            title: 'Dodajte mesto',
        };

        Internal.cityName = cityName;
        var content = Internal.setForm();
        var base = this.getBaseTemplate(options, content);

        tjq('body').append(base);

        tjq('#addCity-fakeClick').click();
    };

    Internal.setInputs = function (name, placeholder, value) {
        var inputWrapper = tjq('<div>', {'class': 'form-group'});
        var input = '<input name="'+ name +'" class="input-text full-width" placeholder="'+ placeholder +'"/>';

        if (value) {
            tjq(input).val(value);
        }

        return inputWrapper.append(input);
    };

    Internal.setButton = function () {
        var button = tjq('<button>', {
            text: 'Pošalji',
            'class': 'close-btn full-width btn-medium',
        });

        return button;
    };

    Internal.setForm = function () {
        var wrapper = tjq('<form>', {
            'id': 'add-city-form',
            method: 'POST',
            enctype: 'multipart/form-data',
            action: '',
        });

        var city = this.setInputs('city', 'Unesite mesto...');
        var country = this.setInputs('country', 'Unesite državu...');

        return wrapper.append(city, country, this.setButton());
    };

    return Public;
});