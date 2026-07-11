import UserDashboardMapper from "../Mapper/UserDashboardMapper";
import toastrService from "../../../../../../app/Resources/public/js/Services/ToastrService";
import {MESSAGE} from "../Constants/MessageConstants";

export default (() => {

    let Public = {}, Private = {};

    Private.mapper = new UserDashboardMapper();
    Private.toastr = toastrService;

    Public.save = function () {
        const FIELD_MAP = {
            firstName: 'firstname',
            lastName: 'lastname',
            newPassword: 'password',
            reNewPassword: 'repassword',
        };

        const payload = Private.mapper.settingsForm.serializeArray()
            .filter(({ value }) => value && value.length > 0)
            .reduce((acc, { name, value }) => ({ ...acc, [FIELD_MAP[name] ?? name]: value }), {});

        Private.toastr.showLoadingMessage();

        $.ajax({
            type: 'PUT',
            url: Routing.generate('site_user_update', { id: USER_ID }),
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(payload),
            success: () => Private.toastr.success(MESSAGE.SUCCESS.USER_UPDATE),
            error: () => Private.toastr.error(MESSAGE.ERROR.APPLICATION_ERROR),
        });
    };

    return Public;
});
