
class AppHelperService {
    /**
     * Set default option to every select box.
     * If default option is provided then it's set for given select
     */
    static defaultOption(self)
    {
        var placeholder = $(self).data('placeholder');

        placeholder = (placeholder) ? placeholder : 'Izaberite...';

        if($(self[0]).val() != -1) {
            $(self).prepend('<option value="-1">' + placeholder + '</option>');
            $(self).val('-1');
        }
    };

    static isArray(data){
        return Object.prototype.toString.call(data) === '[object Array]';
    };

    static isObject(data){
        return Object.prototype.toString.call(data) === '[object Object]';
    };

    static isBoolean(data){
        return Object.prototype.toString.call(data) === '[object Boolean]';
    };

    static isString(data){
        return Object.prototype.toString.call(data) === '[object String]';
    };

    static isJsonString(str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    };

    static isUrl(url) {
        var regex = /(http|https):\/\/(\w+:{0,1}\w*)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%!\-\/]))?/;
        return regex.test(url);
    }

    static redirect(href) {
        if(href == 'reload') {
            window.location.reload();
        } else{
            window.location.href = href;
        }
    };

    static uiElementsEvents() {
        $(".selector select").each((index, element) => {
            this.defaultOption(element);
            var obj = $(element);
            if (obj.parent().children(".custom-select").length < 1) {
                obj.after("<span class='custom-select'>" + obj.children("option:selected").html() + "</span>");

                if (obj.hasClass("white-bg")) {
                    obj.next("span.custom-select").addClass("white-bg");
                }
                if (obj.hasClass("full-width")) {
                    //obj.removeClass("full-width");
                    //obj.css("width", obj.parent().width() + "px");
                    //obj.next("span.custom-select").css("width", obj.parent().width() + "px");
                    obj.next("span.custom-select").addClass("full-width");
                }
            }
        });
        $(document).on("change", ".selector select", (e) => {
            if ($(e.currentTarget).next("span.custom-select").length > 0) {
                $(e.currentTarget).next("span.custom-select").text($(e.currentTarget).find("option:selected").text());
            }
        });

        $(document).on("keydown", ".selector select", (e) => {
            if ($(e.currentTarget).next("span.custom-select").length > 0) {
                $(e.currentTarget).next("span.custom-select").text($(e.currentTarget).find("option:selected").text());
            }
        });

        // change UI of file input
        $(".fileinput input[type=file]").each((i, el) => {
            var obj = $(el);
            if (obj.parent().children(".custom-fileinput").length < 1) {
                obj.after('<input type="text" class="custom-fileinput" />');
                if (typeof obj.data("placeholder") != "undefined") {
                    obj.next(".custom-fileinput").attr("placeholder", obj.data("placeholder"));
                }
                if (typeof obj.prop("class") != "undefined") {
                    obj.next(".custom-fileinput").addClass(obj.prop("class"));
                }
                obj.parent().css("line-height", obj.outerHeight() + "px");
            }
        });

        $(document).on("change", '.fileinput input[type=file]', (e) => {
            var fileName = e.currentTarget.value;
            var slashIndex = fileName.lastIndexOf("\\");
            if (slashIndex == -1) {
                slashIndex = fileName.lastIndexOf("/");
            }
            if (slashIndex != -1) {
                fileName = fileName.substring(slashIndex + 1);
            }
            $(e.currentTarget).next(".custom-fileinput").val(fileName);
        });
        // checkbox
        $(".checkbox input[type='checkbox'], .radio input[type='radio']").each((i, el) => {
            $(el).closest(".checkbox").removeClass("checked");
            $(el).closest(".radio").removeClass("checked");
            if ($(el).is(":checked")) {
                $(el).closest(".checkbox").addClass("checked");
                $(el).closest(".radio").addClass("checked");
            }
        });
        $(document).on("change", '.checkbox input[type=\'checkbox\']', (e) => {
            if ($(e.currentTarget).is(":checked")) {
                $(e.currentTarget).closest(".checkbox").addClass("checked");
            } else {
                $(e.currentTarget).closest(".checkbox").removeClass("checked");
            }
        });
        //radio
        $(document).on("change", '.radio input[type=\'radio\']', (event, ui) => {
            if ($(event.currentTarget).is(":checked")) {
                var name = $(event.currentTarget).prop("name");
                if (typeof name != "undefined") {
                    $(".radio input[name='" + name + "']").closest('.radio').removeClass("checked");
                }
                $(event.currentTarget).closest(".radio").addClass("checked");
            }
        });

        // datepicker
        $('.datepicker-wrap input').each((i, el) => {
            if ($(el).hasAndGetData('range')) {
                return true;
            }
            var minDate = $(el).data("min-date");
            if (typeof minDate == "undefined") {
                minDate = 0;
            }
            $(el).datepicker({
                showOn: 'both',
                buttonImage: 'images/icon/blank.png',
                buttonText: '',
                buttonImageOnly: true,
                changeYear: false,
                /*showOtherMonths: true,*/
                minDate: minDate,
                dateFormat: "dd.mm.yy",
                dayNames: ["Nedelja", "Ponedeljak", "Utorak", "Sreda", "Četvrtak", "Petak", "Subota"],
                monthNames: ["Januar", "Februar", "Mart", "April", "Maj", "Jun", "Jul", "Avgust", "Septembar", "Oktobar", "Novembar", "Decembar"],
                dayNamesMin: ["N", "P", "U", "S", "Č", "P", "S"],
                beforeShow: function(input, inst) {
                    var themeClass = $(input).parent().attr("class").replace("datepicker-wrap", "");
                    $('#ui-datepicker-div').attr("class", "");
                    $('#ui-datepicker-div').addClass("ui-datepicker ui-widget ui-widget-content ui-helper-clearfix ui-corner-all");
                    $('#ui-datepicker-div').addClass(themeClass);
                },
                onClose: function(selectedDate) {
                    if ( $(el).attr('name') == 'date_from' ) {
                        if ( $(el).closest('form').find('input[name="date_to"]').length > 0 ) {
                            $(el).closest('form').find('input[name="date_to"]').datepicker("option", "minDate", selectedDate);
                        }
                    }
                    if ( $(el).attr('name') == 'date_to' ) {
                        if ( $(el).closest('form').find('input[name="date_from"]').length > 0 ) {
                            $(el).closest('form').find('input[name="date_from"]').datepicker("option", "maxDate", selectedDate);
                        }
                    }
                }
            }).on('change', (e) => {
                if($(e.currentTarget).hasAndGetData('set-validation') === 'yes') {
                    $(e.currentTarget).valid();
                }
            });
        });

        // placeholder for ie8, 9
        try {
            $('input, textarea').placeholder();
        } catch (e) {}
    }

    static setWaypoints() {
        if ($().waypoint) {
            $('.counters-box').waypoint(function () {
                $(this).find('.display-counter').each(function () {
                    var value = $(this).data('value');
                    $(this).countTo({from: 0, to: value, speed: 3000, refreshInterval: 10});
                });
                setTimeout(function () {
                    tjq.waypoints('refresh');
                }, 1000);
            }, {
                triggerOnce: true,
                offset: '100%'
            });
        } else {
            $(".counters-box .display-counter").each(function () {
                var value = $(this).data('value');
                $(this).text(value);
            });
        }
    }

    static capitalize(label) {
        if (typeof label !== 'string') return ''
        return label.charAt(0).toUpperCase() + label.slice(1)
    }
};

export default AppHelperService;