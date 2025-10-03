/*
 * Title:   Travelo - Travel, Tour Booking HTML5 Template - Custom Javascript file
 * Author:  http://themeforest.net/user/soaptheme
 */
import SocialService from "./Services/SocialService";
import EnquireService from './Services/EnquireService';

SocialService.fbInit();

tjq(document).ready(function() {
    EnquireService().init();
    tjq('.disabled').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
    });

    tjq('input').on('keypress', function (e) {
        if (e.which == 13) {
           var form = tjq(this).closest('.parent-form');
           form.find('button.master').click();
           return false;
        }
    })

    // tjq.cookieBar({
    //     message: 'Sajt je u pripremi. Sve vaše sugestije i probleme možete nam slati na email adresu -<a href="mailto:'+ SITE_EMAIL +'" class="btn btn-medium">'+ SITE_EMAIL +'</a>',
    //     acceptButton: true,
    //     acceptText: 'U redu',
    //     declineButton: false,
    //     declineText: 'Disable Cookies',
    //     policyButton: false,
    //     policyText: 'Više informacija',
    //     policyURL: null,
    //     autoEnable: true,
    //     acceptOnContinue: false,
    //     expireDays: 1,
    //     // forceShow: false,
    //     effect: 'slide',
    //     element: 'body',
    //     // append: false,
    //     fixed: false,
    //     bottom: false,
    //     zindex: '',
    //     // redirect: '/',
    //     // domain: 'www.example.com',
    //     // referrer: 'www.example.com'
    // });

    tjq('#facebook-share-button').on('click', function (e) {
        SocialService.shareFacebook(e, tjq(this).data('url'));
    })
});

/**
 * Check if data-{value} property exist in element and return data value or null
 * @param key
 * @returns {null}
 */
tjq.fn.hasAndGetData = function(key) {
    var $this = tjq(this);
    return typeof $this.data(key) !== 'undefined' ? $this.data(key) : null;
};

tjq.fn.scrollToPosition = function (selector) {
    tjq("html, body").animate({ scrollTop: tjq(selector).offset().top - 70 }, 1000);
};


(function ($) {
    //
    var re = /([^&=]+)=?([^&]*)/g;
    var decode = function (str) {
        return decodeURIComponent(str.replace(/\+/g, ' '));
    };
    $.parseParams = function (query) {

        // recursive function to construct the result object
        function createElement(params, key, value) {
            key = key + '';

            // if the key is a property
            if (key.indexOf('.') !== -1) {
                // extract the first part with the name of the object
                var list = key.split('.');

                // the rest of the key
                var new_key = key.split(/\.(.+)?/)[1];

                // create the object if it doesnt exist
                if (!params[list[0]]) params[list[0]] = {};

                // if the key is not empty, create it in the object
                if (new_key !== '') {
                    createElement(params[list[0]], new_key, value);
                } else console.warn('parseParams :: empty property in key "' + key + '"');
            } else
            // if the key is an array
            if (key.indexOf('[') !== -1) {
                // extract the array name
                var list = key.split('[');
                key = list[0];

                // extract the index of the array
                var list = list[1].split(']');
                var index = list[0]

                // if index is empty, just push the value at the end of the array
                if (index == '') {
                    if (!params) params = {};
                    if (!params[key] || !$.isArray(params[key])) params[key] = [];
                    params[key].push(value);
                } else
                // add the value at the index (must be an integer)
                {
                    if (!params) params = {};
                    if (!params[key] || !$.isArray(params[key])) params[key] = [];
                    params[key][parseInt(index)] = value;
                }
            } else
            // just normal key
            {
                if (!params) params = {};
                params[key] = value;
            }
        }

        // be sure the query is a string
        query = query + '';

        if (query === '') query = window.location + '';

        var params = {}, e;
        if (query) {
            // remove # from end of query
            if (query.indexOf('#') !== -1) {
                query = query.substr(0, query.indexOf('#'));
            }

            // remove ? at the begining of the query
            if (query.indexOf('?') !== -1) {
                query = query.substr(query.indexOf('?') + 1, query.length);
            } else return {};

            // empty parameters
            if (query == '') return {};

            // execute a createElement on every key and value
            while (e = re.exec(query)) {
                var key = decode(e[1]);
                var value = decode(e[2]);
                createElement(params, key, value);
            }
        }
        return params;
    };
})(jQuery);

/* Set the width of the side navigation to 250px */
function openNav(e) {
    e.preventDefault();
    e.stopPropagation();
    document.getElementById("mySidenav").style.width = "250px";
}

/* Set the width of the side navigation to 0 */
function closeNav() {
    document.getElementById("mySidenav").style.width = "0";
}

window.onscroll = function() {scrollFunction()};

function scrollFunction() {
    if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
        tjq('#sideBtn a').css('left', '0');
    } else {
        tjq('#sideBtn a').css('left', '-200px');
    }
}
function shareFacebook(e, url){
    e.preventDefault();
    e.stopPropagation();

    FB.ui({
        method: 'share',
        mobile_iframe: true,
        //link: url,
        //picture: image,
        //description: 'Lea shoes&accessorise',
        //caption: url,
        href: url
    }, function(response){});
};