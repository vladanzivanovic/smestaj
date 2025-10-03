/**
 * Created by vlada on 1/31/2017.
 */

var MapsService = function()
{
    var $mapOptions = {
        zoom: 12,
        center: {lat: 42.2885651, lng: 18.8311756}
    }
    var $init = function(selector, options)
    {
        var map = new google.maps.Map(selector, tjq.extend($mapOptions, options));
    }

    return {
        init: $init
    }
}