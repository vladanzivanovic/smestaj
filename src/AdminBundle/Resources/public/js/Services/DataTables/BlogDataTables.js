import dt from 'datatables.net-dt';
import dtResponsive from 'datatables.net-responsive-dt';

export default (() => {
    let Public = {},
        Private = {};

    Private.tableRef = $('#data-table');

    Public.init = () => {
        Private.tableRef.DataTable( {
            responsive: true,
            serverSide: true,
            ajax: {
                url: Routing.generate('admin.blog_list'),
                type: 'POST'
            },
            columns: [
                { data: 'id', name: 'id', title: 'Id' },
                { data: 'title', name: 'title', title: 'Naslov' },
                { data: 'status_text', name: 'status', title: 'Status', width: '200px', render: function (data, type, row, meta) {
                        const checkedAttr = row.status === 1 ? 'checked' : '';

                        let html = CAN_EDIT ? `<p class="status-text">${Translator.trans(data, null, 'messages', LOCALE)}</p><input type="checkbox" class="set-active-blog" data-id="${row.id}" ${checkedAttr}/>` : `<p class="status-text">${Translator.trans(data, null, 'messages', LOCALE)}</p>`;
                        return type === 'display' ? html : data;
                    } },
                { data: 'id', orderable: false, render: function (data, type, row, meta) {
                        const editLink = CAN_EDIT ? `<a class="btn btn-link" href="${Routing.generate('admin.edit_blog_page', {id: data})}">Izmeni</a> ` : '';
                        const removeButton = CAN_REMOVE ?`<button class="btn btn-danger remove-item-button" data-id="${data}">Ukloni</button>` : '';

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