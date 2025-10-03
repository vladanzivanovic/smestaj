import dt from 'datatables.net-dt';
import AppHelperService from "../../../../../../../app/Resources/public/js/Helper/AppHelperService";
import dtrowreorder from 'datatables.net-rowreorder-bs4';

export default (() => {
    let Public = {},
        Private = {};

    Private.tableRef = $('#data-table');
    Private.dataTable = null;

    Public.init = () => {
        Private.dataTable = Private.tableRef.DataTable( {
            serverSide: true,
            ajax: {
                url: Routing.generate('admin.get_home_banner_list'),
                type: 'POST'
            },
            columns: [
                { data: 'id', name: 'id', title: 'Id' },
                { data: 'image', orderble: false, title: 'Slika', width: '200px', render: function (data, type, row, meta) {
                    const image = `<img src="${data}" class="slider-table-image">`

                        return type === 'display' ?
                            image :
                            data;
                    } },
                { data: 'position', name: 'position', title: 'Pozicija' },
                { data: 'status_text', name: 'is_active', title: 'Status', width: '200px', render: function (data, type, row, meta) {
                        const checkedAttr = row.is_active === true ? 'checked' : '';
                        const text = Translator.trans(data, null, 'messages', LOCALE);

                        let html = CAN_EDIT ? `<p class="status-text text-uppercase">${text}</p><input type="checkbox" class="set-active-banner" data-id="${row.id}" ${checkedAttr}/>` : `<p class="status-text">${text}</p>`;

                        return type === 'display' ? html : data;
                    } },
                { data: 'id', orderable: false, render: function (data, type, row, meta) {
                    const editLink = CAN_EDIT ? `<a class="btn btn-outline-primary" href="${AppHelperService.generateLocalizedUrl('admin.edit_home_banner_page', {id: data})}">Izmeni</a> ` : '';
                    const removeButton = CAN_REMOVE ?`<button class="btn btn-outline-danger remove-item-button" data-id="${data}">Ukloni</button>` : '';

                        return type === 'display' ?
                            editLink+removeButton :
                            data;
                    } },
            ],
            order: [[2, 'asc']],
            pageLength: 100,
            rowReorder: {
                dataSrc: 'id',
                update: false,
            }
        });

        Private.registerEvents();
    };

    Public.reload = () => {
        Private.tableRef.DataTable().ajax.reload(null, false);
    };

    Private.registerEvents = () => {
        Private.dataTable.on('row-reorder', (e, diff, edit) => {
            let data = {};
            for(let i = 0; i < diff.length; i++) {
                let rowData = Private.dataTable.row( diff[i].node ).data();

                data[rowData.id] = {
                    'id': rowData.id,
                    'position': diff[i].newPosition + 1,
                };
            }

            $.ajax({
                type: 'POST',
                url: AppHelperService.generateLocalizedUrl('admin.set_sliders_position'),
                data: {'rows': JSON.stringify(data)},
                dataType: 'json',
                success: response => {
                    Public.reload();
                },
                error: error => {

                },
            })
        })
    };

    return Public;
});