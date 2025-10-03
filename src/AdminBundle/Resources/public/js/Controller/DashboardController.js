import ProductDataTables from "../Services/DataTables/ProductDataTables";
import ConfirmationModalService from "../Services/ConfirmationModalService";
import ProductEditHandler from "../Handler/Product/ProductEditHandler";

const Private = Symbol('private');

class DashboardController {
    constructor() {
        if (CAN_VIEW) {
            ProductDataTables().init();
        }
        this[Private]().registerEvents();
    }

    [Private]() {
        let Private = {};

         Private.registerEvents = () => {
             $(document).on('click touchend', '.remove-item-button', e => {
                 const id = e.currentTarget.dataset.id;
                 const buttons = [
                     {type: 'button', text: 'Obriši', 'class': 'btn btn-primary remove-product', 'data-id': id, 'data-dismiss': "modal"},
                 ];
                 const title = 'Da li ste sigurni da želite obrišete proizvod?';
                 const confirmModal = new ConfirmationModalService(title, buttons);

                 confirmModal.trigger('show');
             });

             $(document).on('change', '.set-active-product', e => {
                 const id = e.currentTarget.dataset.id;
                 const status = e.currentTarget.checked ? 2 : 1;
                 const handler = new ProductEditHandler();

                 handler.changeStatus(e.currentTarget, id, status);
             });

             $(document).on('click touchend', '.remove-product', e => {
                 const id = e.currentTarget.dataset.id;
                 const handler = new ProductEditHandler();

                 handler.remove(id);
             });
         }

         return Private;
    }
};

export default DashboardController;