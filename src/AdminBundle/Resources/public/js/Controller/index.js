import DashboardController from './DashboardController';
import CategoriesController from "./CategoriesController";
import CategoryEditController from "./CategoryEditController";
import ProductEditController from "./ProductEditController";
import SettingsPageController from "./SettingsPageController";
import AboutUsPageController from "./AboutUsPageController";
import LoginPageController from "./LoginPageController";
import UsersController from "./UsersController";
import UserEditController from "./UserEditController";
import CoreController from "../../../../../../app/Resources/public/js/Controller/CoreController";

let routes = [
    {
        name: 'admin.dashboard',
        controller: DashboardController,
    },
    {
        name: 'admin.categories',
        controller: CategoriesController,
    },
    {
        name: 'admin.add_category_page',
        controller: CategoryEditController,
    },
    {
        name: 'admin.edit_category_page',
        controller: CategoryEditController,
    },
    {
        name: 'admin.add_product_page',
        controller: ProductEditController,
    },
    {
        name: 'admin.edit_product_page',
        controller: ProductEditController,
    },
    {
        name: 'admin.settings_page',
        controller: SettingsPageController,
    },
    {
        name: 'admin.about_us_page',
        controller: AboutUsPageController,
    },
    {
        name: 'admin.login',
        controller: LoginPageController,
    },
    {
        name: 'admin.users',
        controller: UsersController,
    },
    {
        name: 'admin.add_user_page',
        controller: UserEditController,
    },
    {
        name: 'admin.edit_user_page',
        controller: UserEditController,
    },
];

$(document).ready(() => {
    let route = matchRoute();

    let core = new CoreController();

    core.showFlashMsg();
    if (route) {
        new route.controller();
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
