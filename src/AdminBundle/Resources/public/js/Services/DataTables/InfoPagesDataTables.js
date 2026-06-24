import dt from 'datatables.net-dt';
import toastrService from "../../../../../../../app/Resources/public/js/Services/ToastrService";

export default (() => {
    let Public = {},
        Private = {};

    Private.tableRef = $('#info-pages-data-table');
    Private.dataTable = null;
    Private.toastr = toastrService;
    Private.filters = {};

    Private.renderDateTime = (value) => {
        if (null === value || undefined === value || '' === value) {
            return '';
        }

        const date = new Date(value);

        if (true === isNaN(date.getTime())) {
            return value;
        }

        const pad = (n) => (n < 10 ? '0' + n : '' + n);

        return `${pad(date.getDate())}.${pad(date.getMonth() + 1)}.${date.getFullYear()} ${pad(date.getHours())}:${pad(date.getMinutes())}`;
    };

    Private.renderHostCell = (data, type, row) => {
        if ('display' !== type) {
            return data || '';
        }

        return data || '<span class="text-muted">—</span>';
    };

    Private.renderLinkedAdsCell = (data, type, row) => {
        if ('display' !== type) {
            return data || '';
        }

        return data || '<span class="text-muted">—</span>';
    };

    Private.renderPublishedCell = (data, type, row) => {
        if ('display' !== type) {
            return data ? '1' : '0';
        }

        const label = data
            ? Translator.trans('yes', null, 'messages', LOCALE)
            : Translator.trans('no', null, 'messages', LOCALE);
        const badgeClass = data ? 'badge bg-success' : 'badge bg-secondary';

        return `<span class="${badgeClass}">${label}</span>`;
    };

    Private.renderDateCell = (data, type) => {
        if ('display' !== type) {
            return data || '';
        }

        return Private.renderDateTime(data);
    };

    Public.init = () => {
        Private.dataTable = Private.tableRef.DataTable({
            serverSide: true,
            ajax: {
                url: Routing.generate('admin.api.info_pages.list'),
                type: 'POST',
                data: (d) => {
                    d.filters = Private.filters;
                }
            },
            columns: [
                { data: 'id', name: 'id', title: '#', width: '60px' },
                { data: 'propertyName', name: 'propertyName', title: 'Naziv objekta' },
                { data: 'host', name: 'host', title: 'Domaćin', render: Private.renderHostCell },
                { data: 'addressCity', name: 'addressCity', title: 'Grad' },
                { data: 'linkedAdsTitle', name: 'linkedAdsTitle', title: 'Vezan oglas', render: Private.renderLinkedAdsCell },
                { data: 'published', name: 'published', title: 'Objavljeno', width: '110px', render: Private.renderPublishedCell },
                { data: 'createdAt', name: 'createdAt', title: 'Kreirano', render: Private.renderDateCell },
                { data: 'updatedAt', name: 'updatedAt', title: 'Ažurirano', render: Private.renderDateCell },
                { data: 'actions', name: 'actions', title: 'Akcije', orderable: false, searchable: false }
            ],
            order: [[0, 'desc']],
            pageLength: 100,
            lengthMenu: [[25, 50, 100, 200], [25, 50, 100, 200]],
            searching: false
        })
            .on('preXhr.dt', () => {
                Private.toastr.showLoadingMessage();
            })
            .on('draw', () => {
                Private.toastr.remove();
            });
    };

    Public.reload = () => {
        if (null === Private.dataTable) {
            return;
        }

        Private.dataTable.ajax.reload(null, false);
    };

    Public.setFilters = (filters) => {
        Private.filters = filters || {};
    };

    return Public;
});
