import InfoPagesDataTables from "../Services/DataTables/InfoPagesDataTables";
import InfoPageEditHandler from "../Handler/InfoPage/InfoPageEditHandler";
import ConfirmationModalService from "../Services/ConfirmationModalService";

const Private = Symbol('private');

class InfoPagesController {
    constructor() {
        this.dataTables = InfoPagesDataTables();
        this.dataTables.init();

        this[Private]().registerFilterEvents();
        this[Private]().registerRowActions(this.dataTables);
    }

    [Private]() {
        let Private = {};

        Private.debounceTimer = null;

        Private.collectFilters = () => {
            const form = $('#info-pages-filters');

            if (0 === form.length) {
                return {};
            }

            const filters = {};
            const formData = form.serializeArray();

            for (const entry of formData) {
                if (null === entry.value || '' === entry.value) {
                    continue;
                }
                filters[entry.name] = entry.value;
            }

            return filters;
        };

        Private.debouncedReload = () => {
            if (null !== Private.debounceTimer) {
                clearTimeout(Private.debounceTimer);
            }

            Private.debounceTimer = setTimeout(() => {
                this.dataTables.setFilters(Private.collectFilters());
                this.dataTables.reload();
            }, 300);
        };

        Private.registerFilterEvents = () => {
            $(document).on('input change', '#info-pages-filters input, #info-pages-filters select', () => {
                Private.debouncedReload();
            });
        };

        Private.registerRowActions = (dataTables) => {
            $(document).on('click', '.js-info-page-edit', (e) => {
                const id = e.currentTarget.dataset.id;
                window.location.href = Routing.generate('admin.info_pages.edit', { id });
            });

            $(document).on('click', '.js-info-page-view', (e) => {
                const slug = e.currentTarget.dataset.slug;
                window.open(Routing.generate('site.info_page.view', { slug }), '_blank');
            });

            $(document).on('click', '.js-info-page-toggle-publish', (e) => {
                const id = e.currentTarget.dataset.id;
                const currentlyPublished = '1' === e.currentTarget.dataset.published;
                const handler = new InfoPageEditHandler();

                handler.togglePublish(id, false === currentlyPublished).then(() => {
                    dataTables.reload();
                });
            });

            $(document).on('click', '.js-info-page-delete', (e) => {
                const id = e.currentTarget.dataset.id;
                const buttons = [
                    { type: 'button', text: 'Obriši', 'class': 'btn btn-danger js-info-page-confirm-delete', 'data-id': id, 'data-dismiss': 'modal' }
                ];
                const body = $('<div>').append(
                    $('<label>', { for: 'info-page-delete-confirm', class: 'form-label', text: 'Otkucajte "obriši" da potvrdite trajno brisanje:' }),
                    $('<input>', { type: 'text', id: 'info-page-delete-confirm', class: 'form-control', autocomplete: 'off' })
                );
                const modal = new ConfirmationModalService('Da li ste sigurni da želite da obrišete info stranicu?', buttons, body);
                modal.trigger('show');
            });

            $(document).on('click', '.js-info-page-confirm-delete', (e) => {
                const id = e.currentTarget.dataset.id;
                const token = $('#info-page-delete-confirm').val() || '';
                const handler = new InfoPageEditHandler();

                handler.requestDelete(id, token).then(() => {
                    dataTables.reload();
                });
            });
        };

        return Private;
    }
}

export default InfoPagesController;
