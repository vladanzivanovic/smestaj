import ConfirmationModalService from "../Services/ConfirmationModalService";
import CategoryDataTables from "../Services/DataTables/CategoryDataTables";
import CategoryHandler from "../Handler/CategoryHandler";

const Private = Symbol('private');

class CategoriesController {
    constructor() {
        if (CAN_VIEW) {
            CategoryDataTables().init();
        }

        this[Private]().registerEvents();
    }

    [Private]() {
        let Private = {};

         Private.registerEvents = () => {
             $(document).on('click touchend', '.remove-item-button', e => {
                 const slug = e.currentTarget.dataset.slug;
                 const buttons = [
                     {type: 'button', text: 'Obriši', 'class': 'btn btn-primary remove-product', 'data-slug': slug, 'data-dismiss': "modal"},
                 ];
                 const title = 'Da li ste sigurni da želite obrišete kategoriju?';
                 const confirmModal = new ConfirmationModalService(title, buttons);

                 confirmModal.trigger('show');
             });

             $(document).on('click touchend', '.remove-product', e => {
                 const slug = e.currentTarget.dataset.slug;
                 const handler = new CategoryHandler();

                 handler.remove(slug);
             });

             $(document).on('click touchend', '.set-home-page', e => {
                 const slug = e.currentTarget.dataset.slug;
                 const status = e.currentTarget.checked ? 1 : 0;
                 const handler = new CategoryHandler();

                 handler.toggleShowHomePage(slug, status);
             });
         }

         return Private;
    }
};

export default CategoriesController;
