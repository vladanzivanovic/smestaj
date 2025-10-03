import userPageMapper from "../Mapper/UserPageMapper";
import UserHandler from "../Handler/UserHandler";
import userValidator from "../Validators/UserValidator";

class UserEditController {
    constructor() {
        this.mapper = userPageMapper;
        this.handler = new UserHandler();
        this.validator = userValidator;

        this.validator.validate(this.mapper.form);

        this.registerEvents();
    }

    registerEvents() {
        $(this.mapper.submitBtn).on('click touchend', e => {
            e.stopPropagation();
            e.preventDefault();

            this.handler.save();
        });
    }
}

export default UserEditController;