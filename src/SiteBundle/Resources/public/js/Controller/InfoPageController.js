import MapsService from "../../../../../../app/Resources/public/js/Services/MapsService";

require('fotorama/fotorama.js');

class InfoPageController {
    constructor() {
        $('.photo-gallery').fotorama({
            data: window.slideImages,
            autoplay: 5000,
            loop: true,
            nav: false,
            minwidth: '100%',
            maxwidth: '100%',
            maxheight: window.isMobile ? '300px' : '500px',
        });

        if (typeof COORDINATES !== 'undefined' && COORDINATES.lat) {
            this.gmap = new MapsService();

            this.gmap.load().then(() => {
                this.gmap.showMap();
            });
            this.gmap.setCoordinates(COORDINATES.lat, COORDINATES.lng);
        }
    };
}

export default InfoPageController;
