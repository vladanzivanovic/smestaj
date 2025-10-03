/**
 * Created by vlada on 6/24/2017.
 */

"use strict";

var EventTypesService;

EventTypesService = function () {
    var EventType = {};

    EventType.typeAhead = function (eventId) {
        var cities = $getAllCities().then(
            function (response) {
                var $$cities = response;
                tjq.each($$cities, function (i, v) {
                    if(v.Id == cityId){
                        $$city.val(v.City);
                    }
                });
                $$city.typeahead({
                    source: response,
                    autoSelect: true,
                    displayText: function(item){
                        sessionStorage.cityId = item.Id;
                        return item.City;
                    }
                });
            }
        );
        return this;
    };

    EventType.getAll = function () {
        var waitResponse = tjq.Deferred();

        tjq.ajax({
            type: 'GET',
            url: EVENT_TYPES_ALL.generateUrl(),
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

    return EventType;
}();