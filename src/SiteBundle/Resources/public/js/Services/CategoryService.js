import UserDashboardMapper from "../Mapper/UserDashboardMapper";

const singleton = Symbol('CategoryService');
const Private = Symbol('private');

class CategoryService {

    constructor() {
        let Class = new.target;

        if (!Class[singleton]) {
            this.cache = null;
            Class[singleton] = this;
        }

        return Class[singleton];
    }

    getAllCategories() {
         var waitResponse = tjq.Deferred();

         tjq.ajax({
            type: 'GET',
            url: Routing.generate('site_categories'),
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

    getCategories() {
        let waitResponse = tjq.Deferred();
        this.getAllCategories()
            .then((response) => {
                this.cache = response;
                waitResponse.resolve(response);
            })
            .fail(error => {
                waitResponse.reject();
            })

        return waitResponse;
    };

    /**
     * Create html select box
     */
    renderSelectBox(el) {
        el.empty();
        el.val('-1');

        for (let i in CATEGORIES) {
            let category = CATEGORIES[i];

            var options = {
                value: category.id,
                'data-alias': category.alias,
                text: category.name
            };

            el.append(
                $('<option>', options)
            );
        }

        el.next("span.custom-select").remove();
    };

    getById(id) {
        if(this.cache && this.cache.length > 0 && id) {
            for (var i in this.cache) {
                if (this.cache[i].id == id) {
                    return this.cache[i];
                }
            }
        }
    };

    setSelected(id, el) {
        let index = CATEGORIES.map(function (obj) {
                return obj.id
            }).indexOf(id);

        index += 1;

        $('option',el).eq(index).attr('selected', true);
        $(el).val(index);
        el.next("span.custom-select").text(el.find("option:eq("+ index +")").text());
    };

    getCategoriesCache() {
       return this.cache;
    };

    [Private]() {
        let Private = {};

        Private.renderSelectBox = (el) => {
            el.empty();
            el.val('-1');

            $.each(this.cache, (i, v) => {
                var options = {
                    value: v.id,
                    'data-alias': v.alias,
                    text: v.name
                };

                el.append(
                    $('<option>', options)
                );
            });

            el.next("span.custom-select").remove();
        };

        return Private;
    };
};

const instance = new CategoryService();

export default instance;