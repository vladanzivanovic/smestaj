export default (() => {
    var Core = {};
    var Internal = {};
    Internal.options = {
        popupClass: '',
        popupId: '',
        title: '',
    };

    Core.getBaseTemplate = function (options, content) {
        tjq.extend(true, Internal.options, options);

        return Internal.template(content);
    };

    Internal.template = function (content) {
        var wrapper = tjq('<div>');
        var fakeClick = tjq('<a>', {
            href: '#'+ this.options.popupId,
            id: this.options.popupId+ '-fakeClick',
            'class': 'soap-popupbox',
        });
        var popup = tjq('<div>', {
            id: this.options.popupId,
            'class': 'travelo-login-box travelo-box '+ this.options.popupClass,
        });
        var title = tjq('<p>', { id: 'room-title', text: this.options.title });
        var separator = tjq('<div>', {'class': 'seperator'});

        return wrapper.append(fakeClick, popup.append(title, separator, content));
    };

    return Core;
});