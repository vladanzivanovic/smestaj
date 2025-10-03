export default (() => {
    var Public = {};

    Public.setPrices = function ($slider, $sliderDOM) {
        if (!$slider && !$sliderDOM) {
            return false;
        }
        var sliderPrices = $slider.slider('option', 'values'),
            prices = [null, null];

        if($slider.slider('option', 'min') != sliderPrices[0] || $slider.slider('option', 'max') != sliderPrices[1])
        {
            if(sliderPrices[0] && sliderPrices[1]) {
                prices = sliderPrices;
            }
        }

        return prices;
    }

    Public.getSelectedCategory = function (selected, categoryDOM, $categoryList)
    {
        var categories = selected && selected.length > 0 ? selected : [];

        if(categories.length === 0) {
            tjq('li.active', categoryDOM).each(function (i, v) {
                var self = tjq(this);
                tjq.each($categoryList, function (i, v) {
                    if (v.Name == self.text()) {
                        categories.push(v.Alias);
                    }
                })
            });
        }

        return categories.length > 0 ? categories.join(',') : null;
    };
    
    Public.getSelectedTags = function () {
        var tags = [];
        tjq('.tag-filter').each(function () {
            tjq('li.active', tjq(this)).each(function (i, v) {
                var tagId = tjq('span', tjq(this)).data('id');

                tags.push(tagId);
            });
        });

        return tags.length > 0 ? tags.join() : null;
    };

    Public.resetTags = function (filter) {
        const parent = tjq(`#${filter}-filter .tag-filter`);

        tjq('li', parent).each(function (i, v) {
            $(v).removeClass('active');
        });
    };

    Public.generateHtmlList = function (dom, listArray, props, selectedVal) {
        if (!listArray) {
           return false;
        }
        tjq.each(listArray, function (i, v) {
            var activeClass = '',
                value = i,
                label = props.labelName;

            if(props.valueName) {
                value = v[props.valueName];
            }

            if(selectedVal && selectedVal.indexOf(value) > -1) {
                activeClass = 'active';
            }

            dom.append('<li class="' + activeClass + '"><span data-alias="' + value + '">' + v[label] + '</span></li>');
        });
    }

    Public.getMainCategory = function () {

    };

    return Public;
});