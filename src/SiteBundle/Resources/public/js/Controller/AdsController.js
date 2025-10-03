import SearchService from "../Services/SearchService";
import IndexSearchService from "../Services/IndexSearchService";
import adsPageDom from "../Dom/AdsPageDom";
import AdsPageRouting from "../Routing/AdsPageRouting";
import AdsPageService from "../Services/AdsPageService";
import adsPageMapper from "../Mapper/AdsPageMapper";
import MobileDetection from "../MobileDetection";
import AppHelperService from "../../../../../../app/Resources/public/js/Helper/AppHelperService";
import Tipped from "@staaky/tipped";
import toastrService from "../../../../../../app/Resources/public/js/Services/ToastrService";


require ('select2/dist/js/select2.full.min');

const Private = Symbol('private');

class AdsController {
    #toastr;

    constructor() {
        SearchService().init();
        IndexSearchService().init();

        this.mapper = adsPageMapper;
        this.adsService = new AdsPageService();
        this.router = new AdsPageRouting();
        this.dom = adsPageDom;
        this.md = MobileDetection;
        this.#toastr = toastrService;

        this.showSelectedFiltersOnLoad();

        if (!this.md.mobile() && !this.md.tablet()) {
            $(this.mapper.cityList).select2();
            $(this.mapper.sortList).select2();
        }

        AppHelperService.uiElementsEvents();

        Tipped.create('.ad-tag-icon');

        this[Private]().registerEvents();
    }

    /**
     * Change ads view from grid to list and vice versa
     * @param self
     */
    setAdsView(e, self) {
        e.preventDefault();
        e.stopPropagation();

        if($(self).hasClass('active')) {
            return false;
        }

        $.ajax({
            beforeSend: () => {
                this.#toastr.showLoadingMessage();
            },
            type: "GET",
            url: Routing.generate('site_set_ads_view', {view: $(self).data('view')}),
            success: function (response) {
                location.reload();
            },
            error: function (response) {}
        })
    };

    showSelectedFiltersOnLoad() {
        $.each($('.filters-option .active'), (i, elm) => {
            this.dom.addCriteriaOnPage(elm.dataset.searchName, elm.dataset.search, elm.innerText);
        });

        if (EXTRA_PARAMS) {
            this.dom.addCriteriaOnPage(
                Translator.trans('city', null, 'message', LOCALE),
                EXTRA_PARAMS,
                EXTRA_PARAMS
            );

            $(this.mapper.cityList).val(EXTRA_PARAMS);
            $(this.mapper.cityList).trigger('change');
        }
    }

    toggleFilter(e, name, value, text, onlyOne) {
        e.preventDefault();
        e.stopPropagation();

        const elm = $(e.currentTarget);

        if (null !== text) {
            if (elm.hasClass('active')) {
                elm.removeClass('active');
                $(`[data-value="${value}"]`).remove();
            } else {
                elm.addClass('active');
            }
        }

        this.router.toggleParam(name, value, text, onlyOne);

        const apiUrl = this.router.generateUrl();

        this.adsService.applyFilter(apiUrl);

        $(this.mapper.filterBtnClose).click();
    }

    [Private]() {
        let Private = {};

        Private.registerEvents = () => {
            $('a[href="#send-reservation"]').on('shown.bs.tab', function (e) {
                ReservationService.init();
            });

            $('a.change-view').on('click touchend', function () {
                setAdsView(this);
            });

            $('#facebook-sharer').on('click touchend', function (e) {
                SocialService.shareFacebook(e, this);
            });

            $('#google-sharer').on('click touchend', function (e) {
                SocialService.shareGoogle(e, this);
            });

            $('#booking-form').off().on('submit', function (e) {
                e.preventDefault();
                e.stopPropagation();

                ReservationService.adReservationSubmit(this, e);
            });

            $(this.mapper.filterBtnOpen).on('click touchend', e => {
                $('body').addClass('disable-scroll');
                document.getElementById("myNav").style.height = "100%";
            });

            $(this.mapper.filterBtnClose).on('click touchend', e => {
                $('body').removeClass('disable-scroll');
                document.getElementById("myNav").style.height = "0%";
            })

            $(document).on('click touchend', '.selected-filter-btn', e => {
                e.preventDefault();
                e.stopPropagation();

                const name = e.currentTarget.dataset.name;
                const value = e.currentTarget.dataset.value;

                this.router.toggleParam(name, value);

                this.adsService.applyFilter(this.router.generateUrl());

                $(e.currentTarget).remove();

                if(name === 'city') {
                    $(this.mapper.cityList).val(-1);
                    $(this.mapper.cityList).trigger('change');

                    return;
                }

                $(`[data-search="${value}"]`).removeClass('active');
            });

            $(this.mapper.tags).on('click touchend', e => {
                this.toggleFilter(e, 'tags', e.currentTarget.dataset.search, e.currentTarget.innerText, false);
            });

            $(this.mapper.sortList).on('change', e => {
                const selectedValue = e.currentTarget.value;

                this.toggleFilter(e, 'sort', selectedValue, null, true);
            });

            $(this.mapper.cityList).on('change', e => {
                e.preventDefault();
                e.stopPropagation();

                const selectedValue = $(e.currentTarget).val();

                const elm = $(`.selected-filter-btn[data-name="city"]`);

                if (elm.length > 0) {
                    elm.remove();
                }

                $(e.currentTarget).next("span.custom-select").text($(e.currentTarget).find("option:selected").text());

                this.toggleFilter(e, 'city', selectedValue, $(`option:selected`, $(e.currentTarget)).text(), true);
            });
        }

        return Private;
    }
};

export default AdsController;
