/**
 * Created by vlada on 6/16/2017.
 */
"use strict";

var CalendarService = function () {
    var CalendarService = {};

    var $calendar, $getReservedDays, $setHtml,
        $calendarSelector = tjq(".calendar"),
        $calendarInput = tjq('.calendar-input');

    CalendarService.init = function () {
        var current_date = new Date();

        $calendar = new Calendar();
        $getReservedDays(current_date);

        $calendarInput.datepicker('option', {
            minDate: current_date,
            onClose: function (dateStr, instance) {
                var date = new Date();
                date.setMonth(instance.selectedMonth);
                date.setFullYear(instance.selectedYear);

                $getReservedDays(date);
            }
        });
    };

    $getReservedDays = function (date) {
        tjq.ajax({
            beforeSend: function () {
            //    set loader
            },
            type: 'GET',
            url: '/'+stringFormat(CALENDAR_RESERVED_DAYS, [date.getFullYear(), date.getMonth()+1, AdsController.getAdId()]).generateUrl(),
            dataType: 'json',
            success: function (response) {
                if(response.success) {
                    var reservedDays;
                    if(response.data[0].ReservedDays)
                        reservedDays = response.data[0].ReservedDays.split(',').map(function (day) {
                            return parseInt(day);
                        });
                    $setHtml(date, reservedDays);

                }
            }
        })
    };

    $setHtml = function (date, unavailable_days) {
        $calendar.generateHTML(date.getMonth(), date.getFullYear(), unavailable_days);
        $calendarSelector.html($calendar.getHTML());
    };

    return CalendarService;
}();