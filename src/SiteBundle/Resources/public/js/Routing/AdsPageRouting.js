import adsPageDom from "../Dom/AdsPageDom";

class AdsPageRouting {
    constructor() {
        this.dom = adsPageDom;
        this.params = {};

        if (Object.keys(SEARCH_CRITERIA).length > 0) {
            this.params = SEARCH_CRITERIA;
        }

        if (EXTRA_PARAMS) {
            this.params.city = [window.EXTRA_PARAMS];
        }
    }

    getUrlParams(category = null, extraParams = null, isApiUrl = false) {
        let path = `${ROUTE_NAME}`;

        if (!category) {
            category = CATEGORY;
        }

        if (!extraParams) {
            extraParams = window.EXTRA_PARAMS;
        }

        if (isApiUrl) {
            path = `${ROUTE_NAME.replace('site', 'site_api')}`
        }

        return {
            path,
            category,
            extraParams
        }
    }

    toggleParam(paramName, paramValue, text, onlyOne)
    {
        if (!this.params.hasOwnProperty(paramName) || true === onlyOne) {
            this.params[paramName] = [];
        }

        const valueIndex = this.params[paramName].indexOf(paramValue);

        if (valueIndex > -1) {
            this.params[paramName].splice(valueIndex, 1);

            if (this.params[paramName].length === 0) {
                delete this.params[paramName];

                if (paramName === 'city') {
                    window.EXTRA_PARAMS = null;
                }
            }

            return;
        }

        if (paramValue == -1) {
            delete this.params[paramName];

            return;
        }

        if (text) {
            this.dom.addCriteriaOnPage(paramName, paramValue, text);
        }

        this.params[paramName].push(paramValue);
    }

    generateUrl(){
        let urlParams = this.getUrlParams();
        let apiUrlParams = this.getUrlParams(null, null, true);
        let questionMark = '';
        let params = {};

        if (Object.keys(this.params).length > 0) {
            questionMark = '?';
        }

        for (let paramName in this.params) {
            if (paramName === 'city') {
                urlParams = this.getUrlParams(null, this.params[paramName]);
                apiUrlParams = this.getUrlParams(null, this.params[paramName], true);

                continue;
            }
            if (params.length === 0) {

            }

            params[Translator.trans(paramName, null, 'messages', LOCALE)] = this.params[paramName];

        }

        params['category'] = urlParams.category;
        params['extraParams'] = urlParams.extraParams;


        window.history.pushState(
            { path: Routing.generate(urlParams.path, params) },
            '',
            Routing.generate(urlParams.path, params)
        );

        return Routing.generate(apiUrlParams.path, params);
    }
}

export default AdsPageRouting;
