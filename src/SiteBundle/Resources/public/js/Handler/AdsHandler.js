import DropZoneService from "../../../../../../app/Resources/public/js/Services/DropZoneService";
import YouTubeService from "../Services/YouTubeService";
import AppHelperService from "../../../../../../app/Resources/public/js/Helper/AppHelperService";
import {MESSAGE} from "../Constants/MessageConstants";
import AdsAdditionalInfo from "../Services/AdsAdditionalInfo";
import FormService from "../Services/FormService";
import adsEditMapper from "../Mapper/AdsEditMapper";
import toastrService from "../../../../../../app/Resources/public/js/Services/ToastrService";

export default (() => {

    let Public = {}, Private = {};

    Private.mapper = adsEditMapper;
    Private.toastr = toastrService;

    Public.save = function (cityService) {
        let data,
            method = 'POST',
            url = Routing.generate('site_ads_save');

        Private.cityService = cityService;

        data = $(Private.mapper.adsForm).serializeArray();

        data = FormService.sanitize(data);

        if(sessionStorage.getItem('adsId') > 0){
            method = 'PUT';
            url = Routing.generate('site_ads_update', {id: parseInt(sessionStorage.adsId)});
        }

        data = Private.setAdditionalData(data);

        Private.toastr.showLoadingMessage();

        $.ajax({
            type: method,
            url: url,
            data: data,
            dataType: 'json',
            success: function (response) {
                AppHelperService.redirect('reload');

                return true;
            },
            error: function (jqXHR, textStatus, errorThrown) {
                Private.toastr.error(MESSAGE.ERROR.APPLICATION_ERROR);
            }
        })
    };

    Private.setAdditionalData = function (data) {
        const city = Private.cityService.getCityByParam(tjq(Private.mapper.city).val());
        const contactCity = Private.cityService.getCityByParam(tjq(Private.mapper.contact.city).val());

        const additionalData = [
            {documents: JSON.stringify(DropZoneService().getFilesArray('ads'))},
            {youtube: JSON.stringify(YouTubeService().getLists())},
            {city: city.alias},
            {category: tjq('#category option:selected').val()},
            {'contact[city]': contactCity.alias ?? null},
            {additional_info: JSON.stringify(AdsAdditionalInfo().rooms)}
        ];

        for(var k in additionalData){
            var key = Object.keys(additionalData[k])[0];

            data.push({
                name: key,
                value: additionalData[k][key]
            });
        }

        return data
    };

    Public.removeAd = function (event, slug) {
        event.preventDefault();
        event.stopPropagation();
        let deferred = $.Deferred();

        $.ajax({
            type: "DELETE",
            url: Routing.generate('remove_ad', {alias: slug}),
            dataType: 'json',
            success: function (response) {
                Private.toastr.success(response.message);
                deferred.resolve();
            },
            error: function () {
                Private.toastr.error(MESSAGE.ERROR.APPLICATION_ERROR);
                deferred.reject();
            }
        })

        return deferred.promise();
    }

    return Public;
});
