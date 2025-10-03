import UserDashboardMapper from "../Mapper/UserDashboardMapper";
import AdsService from "../Services/AdsService";
import AdsEditService from "../Services/AdsEditService";
import AppHelperService from "../../../../../../app/Resources/public/js/Helper/AppHelperService";
import PaginationService from "../Services/PaginationService";
import AdsHandler from "../Handler/AdsHandler";
import Loader from "../../../../../../app/Resources/public/js/Dom/Loader";
import singleAdView from "../Dom/Dashboard/SingleAdView";

const Private = Symbol('private');

class UserDashboardController {
    #singleAdView;

    constructor() {
        this.adsPaginateOptions = {
            page: 0,
        };
        this.isRenderFirstTime = false;
        this.ads = null;
        this.#singleAdView = singleAdView;

        this.generateAdsOverview(1);

        this[Private]().registerEvents();
    }

    /**
     * Generate ads overview with pagination via ajax
     * @param event
     * @param direction - it can be page number or direction, eg. next or prev
     * @return {boolean}
     */
    generateAdsOverview(direction) {
        let _private = this[Private]();

        Loader.pageLoaderToggle();

        this.adsPaginateOptions.page = direction == 'next' ? this.adsPaginateOptions.page + 1 : this.adsPaginateOptions.page - 1;
        _private.mapper.adsOverview.empty();

        if(direction > 0) {
            this.adsPaginateOptions.page = direction;
        }

        AdsService().getPagination(this.adsPaginateOptions.page).then(
            response => {

                if(response.data.length > 0){

                    tjq.each(response.data, (i, v) => {
                        let pageUrl = Routing.generate('site_single_ads', { category: v.category_alias, alias: v.alias });
                        let imageUrl = Routing.generate('app.image_show', {entity: 'oglasi', filter: 'list_thumb', name: v.image_slug});
                            // popupUrl = Routing.generate('site_popup_ads_gallery', {id: v.id});

                        _private.mapper.adsOverview.append(this.#singleAdView.generateView(
                            v.alias,
                            imageUrl,
                            v.pre_price_from,
                            v.title,
                            v.short_description,
                            pageUrl
                        ));
                    });

                    if(!this.isRenderFirstTime) {
                        $('.ads-counter').data('value', parseInt(response.pagination.totalRows));
                        AppHelperService.setWaypoints();
                    }

                    this.isRenderFirstTime = true;
                }
                PaginationService().init(event, response.pagination);

                Loader.hideLoader();
            }
        ).fail(error => {
            Loader.hideLoader();
        })
    };

    removeAd(e, id) {
        AdsHandler().removeAd(e, id)
            .then(() => {
                this.generateAdsOverview(this.getCurrentPage());
            }).fail(() => {});
    };

    /**
     * Get Current Page from ads Overview
     * @return {number}
     */
    getCurrentPage() {
        return this.adsPaginateOptions.page;
    };

    [Private]() {
        let Private = {};

        Private.mapper = new UserDashboardMapper();
        Private.adsEditService = AdsEditService();

        Private.registerEvents = () => {
            $('a[href="#set_ad_wrapper"]').on('shown.bs.tab', e => {
                Private.adsEditService.initSetAds(this.ads);
                this.ads = null;
            });
            $('a[href="#set_ad_wrapper"]').on('hide.bs.tab', function (e) {
                Private.adsEditService.beforeExitForm();
            });
            // $('a[href="#ads"]').on('hidden.bs.tab', function (e) {
            //     WizardService().closeAllAccordion();
            // });

            $('a[href="#dashboard"]').on('show.bs.tab', (e) => {
                this.generateAdsOverview(1);
            });

            $('a[href="#calendar"]').on('show.bs.tab', function (e) {
                CalendarService.init();
            });

            $('a[href="#settings"]').on('show.bs.tab', function (e) {
                var form = $(e.currentTarget.hash).find('form');

                form.validate(loginService.signUpValidationOptions);
                cityService.citiesTypeahead();
            });
            $(document).on('click touchend', '.change-ad', (e) => {
                this.ads = e.currentTarget.dataset.alias;
                $('a[href="#set_ad_wrapper"]').tab('show');
            })
            $(document).on('click touchend', '.page-btn', (e) => {
                let page = e.currentTarget.dataset.page;

                this.generateAdsOverview(page);
            });
            $(document).on('click touchend', '.dynamic-li', (e) => {
                let page = e.currentTarget.dataset.page;

                this.generateAdsOverview(page);
            });
            $(document).on('click touchend', '.remove-ad', e => {
                let alias = e.currentTarget.dataset.alias;

                this.removeAd(e, alias);
            })
        };

        return Private;
    }
}

export default UserDashboardController;
