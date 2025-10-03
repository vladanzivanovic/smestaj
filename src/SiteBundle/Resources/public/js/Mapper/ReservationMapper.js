class ReservationMapper {
    constructor() {
        this.form = $(`#booking-form`);

        this.firstname = $(`#resFirstName`, this.form );
        this.lastname = $(`#resLastName`, this.form );
        this.email = $(`#resUserEmail`, this.form );
        this.adultNumber = $(`#resAdultNumber`, this.form );
        this.childernNumber = $(`#resChildrenNumber`, this.form );
        this.checkIn = $(`#resCheckIn`, this.form );
        this.checkOut = $(`#resCheckOut`, this.form );
        this.mobile = $(`#resMobile`, this.form );
        this.viber = $(`#resViber`, this.form );
        this.note = $(`#resNote`, this.form );
    }
}

export default ReservationMapper;