import dt from 'datatables.net-dt';
import toastrService from "../../../../../../../app/Resources/public/js/Services/ToastrService";

export default (() => {
    let Public = {},
        Private = {};

    Private.tableRef = $('#data-table');
    Private.toastr = toastrService;

    Public.init = () => {
        Private.tableRef.DataTable( {
            serverSide: true,
            ajax: {
                url: Routing.generate('admin.get_product_list'),
                type: 'POST'
            },
            columns: [
                { data: 'id', name: 'id', title: '#', render: function (id, type, row, meta) {
                    let html = `<a target="_blank" href="${Routing.generate(`site_ads_view`, {'category': row.category_slug, 'extraParams': `${row.city_slug}/${row.slug}`})}">${id}</a>`;

                    return type === 'display' ? html : id;
                } },
                { data: 'title', name: 'title', title: 'Naziv' },
                { data: 'category', name: 'category', title: 'Kategorija' },
                { data: 'city', name: 'city', title: 'Mesto' },
                { data: 'isPayed', name: 'isPayed', title: 'Plaćen', render: function (isPayed, type, row, meta) {
                        let html = `<p class="status-text d-block letter-capitalize">${Translator.trans('no', null, 'messages', LOCALE)}</p>`;

                        if (isPayed === '1') {
                            html = `<p class="status-text d-block letter-capitalize">${Translator.trans('yes', null, 'messages', LOCALE)} (${row.payed_date_text})</p>`
                        }
                        ;
                        return type === 'display' ? html : isPayed;
                } },
                { data: 'status_text', name: 'status', title: 'Status', width: '200px', render: function (data, type, row, meta) {
                    const checkedAttr = row.status === 2 ? 'checked' : '';

                    let html = CAN_EDIT ? `<p class="status-text">${data}</p><input type="checkbox" class="set-active-product" data-id="${row.id}" ${checkedAttr}/>` : `<p class="status-text">${data}</p>`;

                    if (row.status === 3) {
                        html = `<p class="status-text">${data}</p>`;
                    }

                    return type === 'display' ? html : data;
                } },
                { data: 'id', searchable: false, orderable: false, render: function (data, type, row, meta) {
                    const editLink = CAN_EDIT ? `<a class="btn btn-link" href="${Routing.generate('admin.edit_product_page', {id: data})}">Izmeni</a> ` : '';
                    const removeButton = CAN_REMOVE ?`<button class="btn btn-danger remove-item-button" data-id="${data}">Ukloni</button>` : '';

                        return type === 'display' ?
                            editLink+removeButton :
                            data;
                    } },
            ],
            order: [[0, 'desc']],
            pageLength: 100,
        })
            .on('search.dt', () => {
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
