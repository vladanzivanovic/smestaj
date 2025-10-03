class MapsDomHelper {
    constructor(mapper, gmapApi) {
        this.mapper = mapper;
        this.gmapApi = gmapApi;
    }

    getMapByAddress() {
        let addressArray = [
            $(this.mapper.street).val(),
            $(this.mapper.city).val(),
            'Montenegro',
        ];

        this.gmapApi.getMapsDataByAddress(addressArray)
            .then(() => {
                $('input[data-lat]:checked').each((i, v) => {
                    this.measureDistance(v);
                })
            });
    }

    setCoordinates(lat, lng) {
        this.gmapApi.setCoordinates(lat, lng);

        this.gmapApi.setPositionOnMap();
    }

    measureDistance(e) {
        const lat = e.dataset.lat;
        const lng = e.dataset.lng;

        e.value = this.gmapApi.measureDistance(lat, lng);
    }
}

export default MapsDomHelper;
