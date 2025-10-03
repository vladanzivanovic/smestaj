import ConfirmationModalService from "../Services/ConfirmationModalService";
import UsersDataTables from "../Services/DataTables/UsersDataTables";
import UserHandler from "../Handler/UserHandler";

const Private = Symbol('private');

class UsersController {
    constructor() {
        if (CAN_VIEW) {
            UsersDataTables().init();
        }

        this[Private]().registerEvents();
    }

    [Private]() {
        let Private = {};

         Private.registerEvents = () => {
             $(document).on('click touchend', '.remove-item-button', e => {
                 const id = e.currentTarget.dataset.id;
                 const buttons = [
                     {type: 'button', text: 'Onemogući', 'class': 'btn btn-primary remove-user', 'data-id': id, 'data-dismiss': "modal"},
                 ];
                 const title = 'Da li ste sigurni da želite da onemogućite korisnika?';
                 const confirmModal = new ConfirmationModalService(title, buttons);

                 confirmModal.trigger('show');
             });

             $(document).on('click touchend', '.remove-user', e => {
                 const id = e.currentTarget.dataset.id;
                 const handler = new UserHandler();

                 const checkbox = $(`[data-id="${id}"]`);

                 handler.changeStatus(checkbox[0], id, 3);
             });


             $(document).on('change', '.set-active-user', e => {
                 const id = e.currentTarget.dataset.id;
                 const status = e.currentTarget.checked ? 2 : 1;
                 const handler = new UserHandler();

                 handler.changeStatus(e.currentTarget, id, status);
             });
         }

         return Private;
    }
};

export default UsersController;
