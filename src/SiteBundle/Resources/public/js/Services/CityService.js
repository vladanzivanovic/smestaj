export default (() => {
    let Public = {},
        Private = {};
    var $$cities, cache = {};

    Public.getAllCities = function () {
         var waitResponse = tjq.Deferred();

         tjq.ajax({
            type: 'GET',
            url: Routing.generate('site_all_cities'),
            dataType: 'json',
            success: function (response) {
               waitResponse.resolve(response);
            },
            error: function (response) {
                waitResponse.reject();
            }
         });

        return waitResponse;
    };

    Public.citiesTypeahead = function (form) {
        const cityEl = $('.city', form);
        tjq.widget( "custom.catcomplete", tjq.ui.autocomplete, {
            _create: function() {
                this._super();
                this.widget().menu( "option", "items", "> :not(.ui-autocomplete-category)" );
                this.element.on('catcompletechange', $.proxy(this._handleChange,this));
            },
            _renderMenu: function( ul, items ) {
                let that = this;
                tjq.each( items, function( index, item ) {
                    that._renderItemData( ul, item );
                });
            },
            _handleChange: function( event ) {
                this.search( this.element.val(), event );
                $(document).trigger('custom_catcompleteclose');
                this.close();
            },
        });

        cityEl.catcomplete({
            delay: 0,
            source: function( request, response ) {
                var term = request.term;
                if ( term in cache ) {
                    response( cache[ term ] );
                    return;
                }

                tjq.getJSON( Routing.generate('site_cities_by_criteria', {criteria: term}), function( data) {
                    data = data.map(function (item) {
                        item.value = item.label = item.name;

                        return item;
                    });

                    cache[ term ] = data;
                    response( data );
                });
            },
            messages: {
                noResults: '',
                results: function() {}
            },
        });

        return this;
    };

    Public.getCityByParam = function (name, param = 'name') {
        if(cache && Object.keys(cache).length > 0 && name) {
            for (let criteria in cache) {
                const cities = cache[criteria];

                for (let c = 0; c < cities.length; c++) {
                    if (cities[c][param] == name) {
                        return cities[c];
                    }
                }
            }
        }
        return null;
    };

    Public.getCityById = function (id) {
        if($$cities && $$cities.length > 0 && id) {
            for (var i in $$cities) {
                if ($$cities[i].Id == id) {
                    return $$cities[i];
                }
            }
        }
    };

    Private.getCities = function (request, response) {
        var term = request.term;
        if ( term in cache ) {
            response( cache[ term ] );
            return;
        }

        tjq.getJSON( Routing.generate('site_cities_by_criteria', {criteria: term}), function( data) {
            data = data.map(function (item) {
                item.value = item.label = item.name;

                return item;
            });

            cache[ term ] = data;
            response( data );
        });
    }

    return Public;
});
