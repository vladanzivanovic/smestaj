
export default (() => {
    var Public = {};

    Public.getById = function (alias) {
        var waitResponse = tjq.Deferred();

        tjq.ajax({
            type: 'GET',
            url: Routing.generate('get_ad_dashboard_edit', {alias: alias}) ,
            dataType: 'json',
            success: function (response) {
                waitResponse.resolve(response);
            },
            error: function (response) {
                waitResponse.reject();
            }
        });

        return waitResponse;
    }

    Public.getPagination = function (page) {
        return $.get(Routing.generate('site_ads_paginate', {page: page}));
    };

    return Public;
});
