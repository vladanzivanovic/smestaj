import ReservationService from "../Services/ReservationService";
import SocialService from "../Services/SocialService";
import MapsService from "../../../../../../app/Resources/public/js/Services/MapsService";
import singleAdMapper from "../Mapper/SingleAdMapper";

require ('bootstrap-timepicker');
require ('fotorama/fotorama.js')

const Private = Symbol('private');

class SingleAdsController {
    constructor() {
        $('.photo-gallery').fotorama({
            data: window.slideImages,
            minwidth: '100%',
            maxwidth: '100%',
            maxheight: window.isMobile ? '300px' : '500px',
        });

        this.mapper = singleAdMapper;
        this.gmap = new MapsService();
        this.reservationService = ReservationService();

        if (typeof COORDINATES !== 'undefined' && COORDINATES.lat) {

            this.gmap.load().then(() => {
                this.gmap.showMap();
            });
            this.gmap.setCoordinates(COORDINATES.lat, COORDINATES.lng);
        }

        this.reservationService.init();

        this[Private]().registerEvents();
    };

    manualChangeTab(self, e) {
        e.preventDefault();
        e.stopPropagation();

        var href = $(self).data('tab-href'),
            date = $(self).hasAndGetData('date');

        $('a[href="'+ href +'"]').click().scrollToPosition('a[href="'+ href +'"]');

        if(date) {
            $('input[name="eventDate"]').datepicker('setDate', new Date(date));
        }

    };

    [Private]() {
        let Private = {};

        Private.registerEvents = () => {
            $('#facebook-sharer').on('click touchend', function (e) {
                SocialService().shareFacebook(e, this);
            });

            $('#google-sharer').on('click touchend', function (e) {
                SocialService.shareGoogle(e, this);
            });

            $(this.mapper.reservationSubmit).off().on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                ReservationService().adReservationSubmit(this, e);
            });

            $(document).on('click touchend', '.showTelNumber', e => {
                $('.telNumber').each((i, e) => {
                    $(e).removeClass('hide');
                });
                $(e.currentTarget).addClass('hide');

                $.ajax({
                    type: 'PUT',
                    url: Routing.generate('set_ad_number_counter', {alias: window.adsAlias}),
                    dataType: 'json',
                });
            });

            $('#filter-btn-open').on('click touchend', e => {
                const modalHeight = window.innerHeight * 0.95;

                $('body').addClass('disable-scroll');

                $('.booking-section').css({'height': `${modalHeight}px`, 'overflow-y': 'scroll'});
                document.getElementById("myNav").style.height = "100%";
                this.reservationService.init();
            });

            $(this.mapper.cancelReservation).on('click touchend', e => {
                $('body').removeClass('disable-scroll');
                document.getElementById("myNav").style.height = "0%";

                this.reservationService.resetForm();
            })
        };

        return Private;
    }
}

export default SingleAdsController;
