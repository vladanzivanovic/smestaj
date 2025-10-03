import AppHelperService from "../../../../../../app/Resources/public/js/Helper/AppHelperService";
import SearchHelper from "../Helper/SearchHelper";
require('select2');

export default (() => {
    var Public = {}, Private = {};

    Private.city = $('#city');
    Private.mainCategory = window.URL_PARAMS;

    Private.searchInput = $('#search-products');
    Private.categoriesDOM = $('.category');

    Private.minVal = 50;
    Private.maxVal = 5000;

    /**
     * Populate input fields with searched values
     * @return {SearchService}
     */
    Public.init = function () {
        var search = $.parseParams(location.href);
        const selectedCity = $('option:selected', Private.city).val();
        AppHelperService.uiElementsEvents();
        Private.registerEvents();
        Private.city.val(selectedCity);
        Private.city.next("span.custom-select").text(Private.city.find(`option[value="${selectedCity}"]`).text());

        search.category = window.URL_PARAMS.subcategory || window.URL_PARAMS.category;


        $.each(search, function (i, v) {
            if(v){
                switch(i){
                    case 'search':
                        Private.searchInput.val(v);
                        break;
                    case 'city':
                        Private.cityId = v;
                        break;
                }
            }
        });

        return this;
    };

    Public.performSearch = function (sortName, manualRedirect)
    {
        var params;

        Private.setSortSession(sortName);
        params = Private.prepareSearchData();

        if(!manualRedirect) {
            location.href = Routing.generate('site_ads_view', params);
            return true;
        }

        return params;
    };

    Private.getMainCategory = function (selectedAlias) {
        $.each(Private.categoryList, function (i, v) {
            if(selectedAlias == v.Alias) {
                selectedAlias = v.ParentAlias ? v.ParentAlias : v.Alias;
            }
        });

        return selectedAlias;
    }

    Private.prepareSearchData = function ()
    {
        var city = Private.city.val();

        if (Private.city.val() == -1 || !Private.city.val()) {
            city = null;
        }

        var searchObj = {
            category: window.Category,
            city: city,
            sortName: sessionStorage.getItem('sortName'),
            sortDirection: sessionStorage.getItem('sortDirection'),
            tags: SearchHelper().getSelectedTags()
        };

        for(var k in searchObj) {
            if(!searchObj[k]) {
                delete searchObj[k];
            }
        }

        return searchObj;
    };

    Private.setSortSession = function(sortName)
    {
        if(!sortName) {

            if(!sessionStorage.getItem('sortName')) {

                sessionStorage.removeItem('sortName');
                sessionStorage.removeItem('sortDirection');
            }

            return;
        }

        if(sortName === sessionStorage.getItem('sortName')) {

            if (sessionStorage.getItem(['sortDirection']) === 'DESC') {
                sessionStorage.removeItem('sortName');
                sessionStorage.removeItem('sortDirection');
                return;
            }

            sessionStorage.setItem(
                'sortDirection',
                sessionStorage.getItem('sortDirection') === 'ASC' ? 'DESC' : 'ASC'
            );
        }else {
            sessionStorage.setItem('sortName', sortName);
            sessionStorage.setItem('sortDirection', 'ASC');
        }
    }

    Public.citiesTypeAhead = function () {
        cityService.citiesTypeahead(Private.cityId);

        return this;
    };

    Public.resetFilter = function (filter) {
        switch(filter) {
            case 'city':
                Private.city.val('');
                break;
            default:
                SearchHelper().resetTags(filter);
                break;
        }

        this.performSearch();
    };

    $.fn.generateSlider = function()
    {
        this.slider({
            range: true,
            min: 50,
            max: 5000,
            values: [ Private.minVal, Private.maxVal ],
            slide: function( event, ui ) {
                $(".range-from").html( ui.values[ 0 ] );
                $(".range-to").html( ui.values[ 1 ] );
            },
        });
        $(".range-from").html(this.slider( "values", 0 ) );
        $(".range-to").html( this.slider( "values", 1 ) );
    };

    Private.registerEvents = () => {
        $(document).on('click touchend', '.sidebar-close', e => {
            document.getElementById("mySidenav").style.width = "0";
        });

        $('.sidebar-open').on('click touchend', function(e) {
            e.preventDefault();
            e.stopPropagation();
            document.getElementById("mySidenav").style.width = "250px";
        });

        $(document).on('click touchend', '.search-btn', () => {
            Public.performSearch();
        });

        $(document).on('click touchend', '.reset-filter', e => {
            const filter = e.currentTarget.dataset.filter;

            Public.resetFilter(filter);
        });
        $(document).on('click touchend', '.search-by-name', e => {
            e.preventDefault();
            e.stopPropagation();
            Public.performSearch('title');
        })
    }

    return Public;
});