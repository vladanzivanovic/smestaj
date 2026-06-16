import IndexController from "./IndexController";
import AdsController from "./AdsController";
import SingleAdsController from "./SingleAdsController";
import CoreController from "./CoreController";
import UserDashboardController from "./UserDashboardController";
import ContactController from "./ContactController";
import InfoPageController from "./InfoPageController";

let routes = [
    {
        name: 'site_index',
        controller: () => IndexController,
    },
    {
        name: 'site_ads_view',
        controller: () => {
            if (IS_SINGLE_AD) {
                return SingleAdsController;
            }

            return AdsController;
        },
    },
    {
        name: 'site_user_profile',
        controller: () => UserDashboardController,
    },
    {
        name: 'site_contact_us',
        controller: () => ContactController,
    },
    {
        name: 'site.info_page.view',
        controller: () => InfoPageController,
    },
    {
        name: 'site.info_page.view_en',
        controller: () => InfoPageController,
    },
];

$(document).ready(() => {
    const route = matchRoute();
    const core = new CoreController();
    let controller = null;

    core.showFlashMsg();

    // core.baseCore.showFlashMsg();
    // core.siteMobileMenu();

    if (route) {
        controller = route.controller();
        new controller();
    }
});

let matchRoute = () => {
    for(let i in routes) {
        let route = routes[i];

        if (route.name === ROUTE_NAME) {
            return route;
        }
    }
};
