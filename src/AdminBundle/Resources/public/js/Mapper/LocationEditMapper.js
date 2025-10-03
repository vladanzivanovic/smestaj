class LocationEditMapper {
    constructor() {
        if (!LocationEditMapper.instance) {
            this.form = '#edit_form';
            this.submitBtn = '#location_submit';
            this.street = '#street_rs';
            this.city = '#city_rs';
            this.country = '#country_rs';
            this.countryNorthLat = '#country_north_lat';
            this.countryNorthLng = '#country_north_lng';
            this.countrySouthLat = '#country_south_lat';
            this.countrySouthLng = '#country_south_lng';
            this.countryLat = '#country_lat';
            this.countryLng = '#country_lng';
            this.countryShortCode = '#country_short_code';

            LocationEditMapper.instance = this;
        }

        return LocationEditMapper.instance
    }
}

const locationEditMapper = new LocationEditMapper();

Object.freeze(locationEditMapper);

export default locationEditMapper;