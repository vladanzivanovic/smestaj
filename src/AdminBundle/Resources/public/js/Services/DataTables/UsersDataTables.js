import dt from 'datatables.net-dt';
import toastrService from "../../../../../../../app/Resources/public/js/Services/ToastrService";

export default (() => {
    let Public = {},
        Private = {};

    Private.tableRef = $('#data-table');
    Private.dataTable = null;
    Private.toastr = toastrService;

    Public.init = () => {
        Private.dataTable = Private.tableRef.DataTable( {
            serverSide: true,
            ajax: {
                url: Routing.generate('admin.get_user_list'),
                type: 'POST'
            },
            columns: [
                { data: 'id', name: 'id', title: 'Id' },
                { data: 'full_name', name: 'full_name', title: 'Ime i prezime' },
                { data: 'email', name: 'email', title: 'Email' },
                { data: 'role', name: 'role', title: 'Tip', render: function (data, type, row, meta) {
                        return type === 'display' ? Translator.trans(data, null, 'messages', LOCALE) : data;
                    } },
                { data: 'status_text', name: 'status', title: 'Status', width: '200px', render: function (data, type, row, meta) {
                        const checkedAttr = row.status === 2 ? 'checked' : '';
                        const text = Translator.trans(data, null, 'messages', LOCALE);

                        let html = CAN_EDIT ? `<p class="status-text text-uppercase">${text}</p><input type="checkbox" class="set-active-user" data-id="${row.id}" ${checkedAttr}/>` : `<p class="status-text">${text}</p>`;

                        return type === 'display' ? html : data;
                    } },
                { data: 'id', orderable: false, render: function (data, type, row, meta) {
                    const editLink = CAN_EDIT ? `<a class="btn btn-outline-primary" href="${Routing.generate('admin.edit_user_page', {id: data})}">Izmeni</a> ` : '';
                    const removeButton = CAN_REMOVE ?`<button class="btn btn-outline-danger remove-item-button" data-id="${data}">Onemogući</button>` : '';

                        return type === 'display' ?
                            editLink+removeButton :
                            data;
                    } },
            ],
            order: [[2, 'asc']],
            pageLength: 100,
        })
            .on('search.dt', () => {
                Private.dataTable.context[0].jqXHR.abort();
                Private.toastr.showLoadingMessage();
            })
            .on('draw', () => {
                Private.toastr.remove();
            });
    };

    Public.reload = () => {
        Private.tableRef.DataTable().ajax.reload(null, false);
    };


    return Public;
});
