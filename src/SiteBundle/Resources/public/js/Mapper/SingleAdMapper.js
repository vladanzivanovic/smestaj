class SingleAdMapper {
    constructor() {
        this.reservationSubmit = '#reservation-submit-btn';
        this.cancelReservation = '.cancel-btn';

        if (!SingleAdMapper.instance) {
            SingleAdMapper.instance = this;
        }

        return SingleAdMapper.instance;
    }
}

const singleAdMapper = new SingleAdMapper();
Object.freeze(singleAdMapper);

export default singleAdMapper;