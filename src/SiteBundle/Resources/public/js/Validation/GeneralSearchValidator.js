import {MESSAGE} from "../Constants/MessageConstants";
import StringFilter from "../Filters/StringFilter";
import IndexSearchMapper from "../Mapper/IndexSearchMapper";

export default (() => {
    var Public = {},
        Private = {};

    Private.mapper = new IndexSearchMapper();

    Public.validate = () => {
        if(!Private.mapper.city.val() && Private.mapper.category.val() == -1) {
            Private.mapper.error.removeClass('hide');
            return false;
        }

        Private.mapper.error.addClass('hide');

        return true;
    };


    return Public;
});