import IndexController from "./IndexController";
import AdsController from "./AdsController";
import SingleAdsController from "./SingleAdsController";
import CoreController from "./CoreController";
import UserDashboardController from "./UserDashboardController";

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
