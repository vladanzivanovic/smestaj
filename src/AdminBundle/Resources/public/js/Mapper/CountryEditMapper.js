class CountryEditMapper {
    constructor() {
        this.form = '#edit_form';
        this.submitBtn = '#submit_button';

        if (!CountryEditMapper.instance) {
            CountryEditMapper.instance = this;
        }

        return CountryEditMapper.instance;
    }
}
const countryEditMapper = new CountryEditMapper();
Object.freeze(countryEditMapper);
export default countryEditMapper;