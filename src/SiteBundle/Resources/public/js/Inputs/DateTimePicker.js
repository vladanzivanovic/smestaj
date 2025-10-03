class DateTimePicker {
    constructor() {
        this.defaultOptions = {
            showOn: 'both',
            buttonImage: 'images/icon/blank.png',
            buttonText: '',
            buttonImageOnly: true,
            changeYear: false,
            /*showOtherMonths: true,*/
            minDate: 0,
            dateFormat: "dd.mm.yy",
            dayNames: ["Nedelja", "Ponedeljak", "Utorak", "Sreda", "Četvrtak", "Petak", "Subota"],
            monthNames: ["Januar", "Februar", "Mart", "April", "Maj", "Jun", "Jul", "Avgust", "Septembar", "Oktobar", "Novembar", "Decembar"],
            dayNamesMin: ["N", "P", "U", "S", "Č", "P", "S"],
        }
    }

    range(elFrom, elTo, options) {
        let mergedOptions = Object.assign({}, this.defaultOptions, options);
        let from = elFrom
            .datepicker(mergedOptions)
            .on( "change", () => {
                to.datepicker( "option", "minDate", this.getDate( elFrom ) );
            }),
            to = elTo.datepicker(mergedOptions);
    }

    getDate( element ) {
        var date;
        try {
            date = $.datepicker.parseDate( this.defaultOptions.dateFormat, element.val() );
        } catch( error ) {
            date = null;
        }

        return date;
    }
}

const datetime = new DateTimePicker();

export default datetime;