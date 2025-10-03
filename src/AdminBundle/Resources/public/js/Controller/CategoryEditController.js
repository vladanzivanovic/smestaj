import CategoryEditMapper from "../Mapper/CategoryEditMapper";
import CategoryHandler from "../Handler/CategoryHandler";
import categoryEditValidator from "../Validators/CategoryEditValidator";
import('bootstrap-colorpicker/dist/css/bootstrap-colorpicker.css');
require('bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min');

class CategoryEditController {
    constructor() {
        this.mapper = new CategoryEditMapper();
        this.validator = categoryEditValidator;

        this.validator.validate(this.mapper.form);

        this.registerEvents();
    }

    registerEvents() {
        this.mapper.submitBtn.on('click touchend', e => {
            const handler = new CategoryHandler();

            handler.save(this.mapper);
        });
    }
}

export default CategoryEditController;