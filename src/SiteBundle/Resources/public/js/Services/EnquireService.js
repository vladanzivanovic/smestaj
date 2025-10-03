import enquire from 'enquire-js';
import {MEDIA_QUERIES} from "../Constants/MediaQueriesConstants";

export default (() => {

    var EnquireService = {},
        $max768, $max1300,
        headerImg = tjq('.header-bg-image'),
        categoryImg = tjq('.header-bg-image-category');

    EnquireService.init = function () {
        enquire.register(MEDIA_QUERIES.MAX_768, $max768());
        enquire.register(MEDIA_QUERIES.MAX_1300, $max1300());
    };

    $max768 = function () {
        return {
            deferSetup : true,
            setup : function() {
                this.unmatch()
            },
            match : function() {
                headerImg.removeClass('parallax');
                categoryImg.removeClass('parallax');
            },
            unmatch : function() {
                headerImg.addClass('parallax');
                categoryImg.addClass('parallax');
            }
        }
    };

    $max1300 = function () {
        return {
            deferSetup : true,
            setup : function() {
                this.unmatch()
            },
            match : function() {
                tjq('#ad-contact-us-btn-group').removeClass('contact-box');
                tjq('#ad-contact-us-btn-group').removeClass('contact-us');
            },
            unmatch : function() {
                tjq('#ad-contact-us-btn-group').addClass('contact-box-mobile');
                tjq('#ad-contact-us-btn-group').addClass('contact-us-mobile');
            }
        }
    };

    return EnquireService;
});