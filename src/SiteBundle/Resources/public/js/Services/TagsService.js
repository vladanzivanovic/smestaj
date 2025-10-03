import Cache from "../../../../../../app/Resources/public/js/Helper/CacheHelper";
import AppHelperService from "../../../../../../app/Resources/public/js/Helper/AppHelperService";

export default (() => {
    var Public = {};
    var Private = {};

    Public.getTags = () => {
        var waitResponse = tjq.Deferred();

        tjq.ajax({
            type: 'GET',
            url: Routing.generate('site_ads_options'),
            dataType: 'json',
            success: function (response) {
                Cache.set('tags', response.tags);
                waitResponse.resolve(response);
            },
            error: function (response) {
                waitResponse.reject();
            }
        });

        return waitResponse;
    };

    Public.generateHtml = (tags) => {
        for (var type in TAGS) {
            tjq('#'+type).empty();
            Private.setEditHtml(type, TAGS[type], tags && tags[type] ? tags[type] : null);
        }
        AppHelperService.uiElementsEvents();
    };

    Private.setEditHtml = function (type, typeArray, selectedTags) {
        tjq('#'+type).append(`<h4>${typeArray.name}</h4>`);

        tjq.each(typeArray.tags, function (key, tag) {

            if(type == 'range') {
                var value = selectedTags && selectedTags.hasOwnProperty(tag.id) ? selectedTags[tag.id].value : '';

                var input = '<div class="col-xs-12 col-sm-6 col-md-4">\n' +
                    '<label for="tags-'+type+'-'+tag.id+'">'+tag.name+' - u metrima</label>\n' +
                    '<input type="text" name="tags['+type+']['+tag.id+']" id="tags-'+type+'-'+tag.id+'" ' +
                    'class="input-text full-width" value="'+ value +'">\n' +
                    '</div>';
            }
            if(type != 'range') {
                var checked = selectedTags && selectedTags[tag.id] ? 'checked="checked"' : '';

                var input = '<div class="col-xs-12 col-sm-6 col-md-4">\n' +
                    '<div class="checkbox full-width mtop-5vh">\n' +
                    '<label for="tags-'+type+'-'+tag.id+'">\n' +
                    '<input type="checkbox" id="tags-'+type+'-'+tag.id+'" name="tags['+type+']['+tag.id+']" value="1" '+ checked +'>\n' +
                    tag.name[0].toUpperCase() + tag.name.substring(1) +
                    '</label>\n' +
                    '</div>\n' +
                    '</div>';
            }

            tjq('#'+type).append(input);
        })
    };

    return Public;
});