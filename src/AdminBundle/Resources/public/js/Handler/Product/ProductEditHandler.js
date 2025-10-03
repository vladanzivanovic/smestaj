import DropZoneService from "../../../../../../../app/Resources/public/js/Services/DropZoneService";
import AppHelperService from "../../../../../../../app/Resources/public/js/Helper/AppHelperService";
import ProductDataTables from "../../Services/DataTables/ProductDataTables";
import productEditMapper from "../../Mapper/ProductEditMapper";
import YouTubeService from "../../../../../../SiteBundle/Resources/public/js/Services/YouTubeService";
import AdsAdditionalInfo from "../../../../../../SiteBundle/Resources/public/js/Services/AdsAdditionalInfo";
import FormService from "../../../../../../SiteBundle/Resources/public/js/Services/FormService";
import toastrService from "../../../../../../../app/Resources/public/js/Services/ToastrService";

class ProductEditHandler {
    #toastr;

    constructor() {
        this.mapper = productEditMapper;
        this.#toastr = toastrService;
    }

    save() {
        let urlRoute = Routing.generate('admin.add_product_api');
        let type = 'POST';
        let data = $(this.mapper.form).serializeArray();

        if (IS_EDIT) {
            urlRoute = Routing.generate('admin.edit_product_api', {id: ID});
            type = 'PUT';
        }

        this.setAdditionalData(data);

        if (! $(this.mapper.form).valid()) {
            return false;
        }

        data = FormService.sanitize(data);

        this.#toastr.showLoadingMessage();

        $.ajax({
            type,
            url: urlRoute,
            data,
            dataType: 'json',
            success: () => {
                AppHelperService.redirect(Routing.generate('admin.dashboard'));
            },
            error: () => {
                let errors = error.responseJSON;

                if (!AppHelperService.isJsonString(errors.error)) {
                    this.#toastr.error(Translator.trans('generic_error', null, 'messages', LOCALE));
                }
            }
        })
    }

    setAdditionalData(data) {
        const additionalData = [
            {documents: JSON.stringify(DropZoneService().getFilesArray('product'))},
            {youtube: JSON.stringify(YouTubeService().getLists())},
            {additional_info: JSON.stringify(AdsAdditionalInfo().rooms)}
        ];

        for(var k in additionalData){
            var key = Object.keys(additionalData[k])[0];

            data.push({
                name: key,
                value: additionalData[k][key]
            });
        }

        return data
    };

    changeStatus(checkbox, id, status) {
        $.ajax({
            type: 'PATCH',
            'url': Routing.generate('admin.api_product_change_status', {id, status}),
            dataType: 'json',
            success: (response) => {
                checkbox.parentElement.firstElementChild.innerText = Translator.trans(response.text, null, 'messages', LOCALE);
            },
            error: () => {
                this.#toastr.error(Translator.trans('generic_error', null, 'message', LOCALE));
            }
        })
    }

    remove(id) {
        this.#toastr.showLoadingMessage();

        $.ajax({
            type: 'DELETE',
            url: Routing.generate(`admin.remove_product_api`, {id}),
            success: () => {
                ProductDataTables().reload();
                this.#toastr.remove();
            },
            error: (error) => {
                const errors = error.responseJSON;

                if (errors.hasOwnProperty('message')) {
                    this.#toastr.error(errors.message);

                    return;
                }

                this.#toastr.error(Translator.trans('generic_error', null, 'messages'. LOCALE));
            }
        })
    }
}

export default ProductEditHandler;
