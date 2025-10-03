import {MESSAGE} from "../Constants/MessageConstants";
import ReservationMapper from "../Mapper/ReservationMapper";

require ('../../../../../../app/Resources/public/js/Validators/ValidationRuleHelper');

export default (() => {
    const Public = {};
    const mapper = new ReservationMapper();

    Public.reservationValidation = () => {
        let options = {
            ignore: ".ignore",
            rules: {
                firstname: {
                    required: true,
                },
                lastname: {
                    required: true,
                },
                email: {
                    email: true
                },
                mobile: {
                    required: true,
                },
                viber: {
                    number: true
                },
                checkin: {
                    required: true,
                    isValidDate: true,
                    dateFromTo: {
                        selector: '#resCheckOut',
                        names: ['datum od', 'datum do']
                    }
                },
                checkout: {
                    required: true,
                    isValidDate: true,
                    dateToFrom: {
                        selector: '#resCheckIn',
                        names: ['datum od', 'datum do']
                    }
                },
                adultnumber: {
                    required: true,
                    min: 1,
                },
                notificationtype: {
                    required: true,
                },
                hiddenRecaptcha: {
                    required: function () {
                        grecaptcha.execute(window.reservation)
                            .then(response => {
                                if (grecaptcha.getResponse(window.reservation) == '') {
                                    return true;
                                }
                                return false;
                            });

                    }
                },
            },
        };

        $.extend(options, window.helpBlock);

        return mapper.form.validate(options);
    };

    return Public;
});
