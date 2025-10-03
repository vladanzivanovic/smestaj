import ReservationValidator from "../Validation/ReservationValidator";
import {MESSAGE} from "../Constants/MessageConstants";
import AppHelperService from "../../../../../../app/Resources/public/js/Helper/AppHelperService";
import ReservationMapper from "../Mapper/ReservationMapper";
import DateTimeInput from "../Inputs/DateTimePicker";
import toastrService from "../../../../../../app/Resources/public/js/Services/ToastrService";

export default (() => {
    var Reservation = {};
    var Private = {};

    Private.mapper = new ReservationMapper();
    Private.validator = ReservationValidator();
    Private.toastr = toastrService;

    Private.validationForm = null;

    Reservation.init = function () {
        DateTimeInput.range(Private.mapper.checkIn, Private.mapper.checkOut);
        Private.validationForm = Private.validator.reservationValidation();
    };

    Reservation.adReservationSubmit = function (self)
    {
        if(!Private.mapper.form.valid()){
            return false;
        }

        let form, formDOM;

        formDOM = Private.mapper.form[0];
        form = new FormData(formDOM);

        tjq.ajax({
            beforeSend: function () {
                Private.toastr.showLoadingMessage();
            },
            type: 'POST',
            url: Routing.generate('site_ad_reservation', {slug: window.adsAlias}),
            data: form,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function (response) {
                AppHelperService.redirect(Routing.generate('site_reservation_success', { reservationId: response.reservation_id, alias: window.adsAlias }));
            },
            error: function () {
                Private.toastr.error(MESSAGE.ERROR.APPLICATION_ERROR);
            }
        });
    };

    Reservation.confirmationInit = function () {
        if(!window.user || window.user.length === 0) {
            $('#login-button').click();
        }
    };

    Reservation.setReservationReason = function () {
        var data = {},
            reason = $('input[name="rejectionReason"]:checked').val(),
            reasonTxt = $('#rejectionReasonTxt').val();

        if(reasonTxt.length > 0) {
            reason += ' - '+ reasonTxt;
        }

        data['status'] = window.reservatio_status;
        data['rejectreason'] = reason;

        tjq.ajax({
            beforeSend: function () {
                Private.toastr.showLoadingMessage();
            },
            type: 'PUT',
            url: Routing.generate('site_reservation_status', {reservation: window.reservatio_id}),
            data: data,
            dataType: 'json',
            success: function (response) {

                if (response.status) {
                    Private.toastr.success(response.message);
                    return true;
                }

                Private.toastr.error(response.message);
            }
        });
    };

    Reservation.resetForm = function () {
        Private.mapper.form[0].reset();
        Private.validationForm.resetForm();
    }

    Private.setUserToReservation = function (user) {
        $('input[name="user[firstName]"]').val(user.firstname);
        $('input[name="user[lastName]"]').val(user.lastname);
        $('input[name="user[email]"]').val(user.email);
        $('input[name="user[address]"]').val(user.address);
        $('input[name="user[telephone]"]').val(user.telephone);
        $('input[name="user[mobilephone]"]').val(user.mobilephone);

        if(user.cityid) {
            $('input[name="user[city]"]').val(user.cityid.name);
            $('input[name="user[country]"]').val(user.cityid.countryid.name);
        }
    }

    return Reservation;
});
