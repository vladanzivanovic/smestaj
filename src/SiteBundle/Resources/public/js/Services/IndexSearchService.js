import CategoryService from "./CategoryService";
import IndexSearchMapper from "../Mapper/IndexSearchMapper";
import AppHelperService from "../../../../../../app/Resources/public/js/Helper/AppHelperService";
import CityService from "./CityService";
import GeneralSearchValidator from "../Validation/GeneralSearchValidator";

export default (() => {
    var Public = {}, Private = {};

    Private.mapper = new IndexSearchMapper();

    Public.init = () => {
        Public.cityService = CityService();
        Public.generalSearch();
        Private.registerEvents();
    }
    /**
     * Set typeahead to general search input
     *
     * @Url /api/general-search
     * @Method GET
     */
    Public.generalSearch = () => {
        Public.cityService.citiesTypeahead(Private.mapper.form);
        CategoryService.getCategories()
            .then(response => {
                CategoryService.renderSelectBox(Private.mapper.category);
                AppHelperService.uiElementsEvents();
            })
    };

    Private.setGeneralSearch = () => {
        if (!GeneralSearchValidator().validate()) {
            return false;
        }

        const categoryId = Private.mapper.category.val();
        const cityVal = Private.mapper.city.val();
        const categoryObj = CategoryService.getById(categoryId);
        const cityObj = Public.cityService.getCityByParam(cityVal);
        const category = categoryObj instanceof Object ? categoryObj.alias : 'smestaj';
        const city = cityObj instanceof Object ? cityObj.alias : null;

        let params = {category: category};

        if (city) {
            params.extraParams = city;
        }

        location.href = Routing.generate('site_ads_view', params);
    };

    Private.registerEvents = () => {
        $(Private.mapper.button).on('click touchend', e => {
            e.preventDefault();
            e.stopPropagation();

            Private.setGeneralSearch();
        })
    }

    return Public;
});