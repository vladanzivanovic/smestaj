class RegistrationMapper {
    constructor() {

        if (!RegistrationMapper.instance) {
            this.form = $(`#signup-form`);

            this.firstName = $(`#registration-firstName`, this.form);
            this.lastName = $(`#registration-lastName`, this.form);
            this.email = $(`#registration-email`, this.form);
            this.password = $(`#registration-password`, this.form);
            this.rePassword = $(`#registration-rePassword`, this.form);

            RegistrationMapper.instance = this;
        }

        return RegistrationMapper.instance;
    }
}

const registrationMapper = new RegistrationMapper();

Object.freeze(registrationMapper);

export default registrationMapper;
