class ResetPasswordMapper {
    constructor() {
        if(!ResetPasswordMapper.instance) {
            this.form = $(`#reset-password-form`);
            this.setForm = $(`#set-password-form`);

            this.email = $(`#resetEmail`, this.form);

            ResetPasswordMapper.instance = this;
        }

        return ResetPasswordMapper.instance;
    }
}
const resetPasswordMapper = new ResetPasswordMapper();

Object.freeze(resetPasswordMapper);
export default resetPasswordMapper;
