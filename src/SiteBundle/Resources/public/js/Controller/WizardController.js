/*
 * Title:   Travelo - Travel, Tour Booking HTML5 Template - Custom Javascript file
 * Author:  http://themeforest.net/user/soaptheme
 */

tjq(document).ready(function() {

    ReservationService.init();

    wizardService.getInterests();

    tjq('.timepicker input').timepicker({
        showMeridian: false
    });
});