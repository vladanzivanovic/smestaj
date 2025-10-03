import dt from 'datatables.net-dt';

export default (() => {
    let Public = {},
        Private = {};

    Private.tableRef = $('#data-table');

    Public.init = () => {
        Private.tableRef.DataTable( {
            serverSide: true,
            ajax: {
                url: Routing.generate('admin.get_country_list'),
                type: 'POST'
            },
            columns: [
                { data: 'id', name: 'id', title: 'Id' },
                { data: 'title_hr', name: 'title_hr', title: 'Naziv'},
                { data: 'status_text', name: 'status', title: 'Status', width: '200px', render: function (data, type, row, meta) {
                        const checkedAttr = row.status === 2 ? 'checked' : '';

                        let html = CAN_EDIT ? `<p class="status-text">${data}</p><input type="checkbox" class="change-item-status" data-id="${row.id}" ${checkedAttr}/>` : `<p class="status-text">${data}</p>`;

                        return type === 'display' ? html : data;
                    } },
                { data: 'id', orderable: false, render: function (data, type, row, meta) {
                    const editLink = CAN_EDIT ? `<a class="btn btn-outline-primary" href="${Routing.generate('admin.edit_country_page', {id: data})}">Izmeni</a> ` : '';
                    const removeButton = CAN_REMOVE ?`<button class="btn btn-outline-danger remove-item-button" data-id="${data}">Ukloni</button>` : '';

                        return type === 'display' ?
                            editLink+removeButton :
                            data;
                    } },
            ],
            order: [[0, 'desc']],
            pageLength: 100,
        });
    };

    Public.reload = () => {
        Private.tableRef.DataTable().ajax.reload(null, false);
    };

    return Public;
});