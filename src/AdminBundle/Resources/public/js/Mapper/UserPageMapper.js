class UserPageMapper {
    constructor() {
        this.form = '#edit_form';
        this.firstName = '#first_name';
        this.lastName = '#last_name';
        this.email = '#email';
        this.password = '#password';
        this.role = '#role';
        this.address = '#address';
        this.city = '#city';
        this.zipCode = '#zipCode';
        this.country = '#country';
        this.submitBtn = '#user_submit';

        if (!UserPageMapper.instance) {
            UserPageMapper.instance = this;
        }

        return UserPageMapper.instance;
    }
}

const userPageMapper = new UserPageMapper();

Object.freeze(userPageMapper);

export default userPageMapper;