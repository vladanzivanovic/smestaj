import infoPageEditMapper from "../Mapper/InfoPageEditMapper";
import InfoPageEditHandler from "../Handler/InfoPage/InfoPageEditHandler";
import InfoPageValidator from "../Validators/InfoPageEditValidator";
import DropZoneService from "../../../../../../app/Resources/public/js/Services/DropZoneService";
import MapsService from "../../../../../../app/Resources/public/js/Services/MapsService";
import MapsDomHelper from "../../../../../../app/Resources/public/js/Services/MapsDomHelper";
import toastrService from "../../../../../../app/Resources/public/js/Services/ToastrService";
import ConfirmationModalService from "../Services/ConfirmationModalService";
import SummerNote from "../Services/SummerNote";

require('select2/dist/js/select2.full.min');

class InfoPageEditController {
    constructor() {
        this.mapper = infoPageEditMapper;
        this.toastr = toastrService;
        this.dropZone = DropZoneService();

        if (0 === IS_EDIT) {
            this.initSelectAdsScreen();
            return;
        }

        this.initEditScreen();
    }

    initSelectAdsScreen() {
        this.handler = new InfoPageEditHandler(null);

        const $select = $(this.mapper.adsAutocomplete);

        if (0 === $select.length) {
            return;
        }

        $select.select2({
            placeholder: 'Pretraži oglase...',
            allowClear: true,
            ajax: {
                url: Routing.generate('admin.api.info_pages.ads_autocomplete'),
                dataType: 'json',
                delay: 250,
                data: (params) => ({ q: params.term || '' }),
                processResults: (response) => ({
                    results: (response.results || []).map((row) => ({
                        id: row.id,
                        text: `${row.text} (#${row.id})${true === row.hasInfoPage ? ' \u2014 već ima info stranicu' : ''}`,
                        hasInfoPage: row.hasInfoPage,
                        infoPageId: row.infoPageId
                    }))
                })
            }
        });

        $select.on('select2:select', (e) => {
            const data = e.params.data;
            const $warning = $(this.mapper.existingWarning);

            if (true === data.hasInfoPage) {
                const editUrl = Routing.generate('admin.info_pages.edit', { id: data.infoPageId });
                $warning
                    .removeClass('d-none')
                    .html(`Ovaj oglas već ima info stranicu. <a href="${editUrl}">Otvori postojeću</a>.`);
                $select.val(null).trigger('change');
                return;
            }

            $warning.addClass('d-none').empty();
            this.promptCopyDecision(data.id);
        });

        $(document).off('click', '.js-info-page-copy-decision').on('click', '.js-info-page-copy-decision', (e) => {
            const adsId = parseInt(e.currentTarget.dataset.adsId, 10);
            const copy = e.currentTarget.dataset.copy;
            const copyData = '1' === copy;

            // Defensive: spec answer Q10 — "Da, kopiraj" only proceeds with a real selected Ads entity. The select2:select handler already gates the modal opening; this is the second line of defence.
            const currentSelection = $(this.mapper.adsAutocomplete).val();
            if (null === currentSelection || '' === currentSelection || String(adsId) !== String(currentSelection)) {
                this.toastr.error('Prvo izaberite oglas iz liste.');
                return;
            }

            if (true === isNaN(adsId) || 0 >= adsId) {
                this.toastr.error(Translator.trans('generic_error', null, 'messages', LOCALE));
                return;
            }

            try {
                this.handler.createFromAds(adsId, copyData);
            } catch (err) {
                console.error('InfoPageEditController.createFromAds dispatch failed', err);
                this.toastr.error(Translator.trans('generic_error', null, 'messages', LOCALE));
            }
        });
    }

    promptCopyDecision(adsId) {
        const title = 'Kopirati podatke iz oglasa?';
        const body = 'Možete uvesti osnovne podatke (naziv, adresa, kontakt, fotografije) ili započeti praznu stranicu. Zatvorite prozor za otkazivanje.';
        const buttons = [
            { type: 'button', text: 'Da, kopiraj', 'class': 'btn btn-primary js-info-page-copy-decision', 'data-dismiss': 'modal', 'data-ads-id': String(adsId), 'data-copy': '1' },
            { type: 'button', text: 'Ne, prazna stranica', 'class': 'btn btn-outline-primary js-info-page-copy-decision', 'data-dismiss': 'modal', 'data-ads-id': String(adsId), 'data-copy': '0' }
        ];

        new ConfirmationModalService(title, buttons, body).trigger('show');
    }

    initEditScreen() {
        this.validator = InfoPageValidator().infoPageValidation();
        this.handler = new InfoPageEditHandler(this.validator);

        this.mapService = new MapsService();
        // MapsDomHelper reads `mapper.street`/`mapper.city` literally; shim translates from the
        // info-page mapper's `addressStreet`/`addressCity` selectors so we don't bloat the mapper
        // with cross-cutting alias properties.
        this.mapsDomHelper = new MapsDomHelper(
            { street: this.mapper.addressStreet, city: this.mapper.addressCity },
            this.mapService
        );

        this.mapService.load().then(() => {
            this.mapService.showMap();
            this.mapService.registerEvents();

            if ('undefined' !== typeof COORDINATES && null !== COORDINATES) {
                this.mapService.setCoordinates(COORDINATES.lat, COORDINATES.lng);
                this.mapService.setPositionOnMap();
            }
        });

        $(this.mapper.addressCity).on('change keyup', () => {
            this.mapsDomHelper.getMapByAddress();
        });
        $(this.mapper.addressStreet).on('keyup', () => {
            this.mapsDomHelper.getMapByAddress();
        });

        this.initDropzone();
        this.initSlugWarning();
        this.registerSubmit();

        this.summernote = new SummerNote();
        this.summernote.setToolbar([this.summernote.styleOptions, this.summernote.fontOptions]);
        this.summernote.initialize($(this.mapper.houseRules));
    }

    initDropzone() {
        const $dropzoneTarget = $('[data-files="info_page_images"]');

        if (0 === $dropzoneTarget.length) {
            return;
        }

        this.dropZone.init($dropzoneTarget);

        if ('undefined' !== typeof EXISTING_IMAGES && Array.isArray(EXISTING_IMAGES) && 0 < EXISTING_IMAGES.length) {
            this.dropZone.setFiles(EXISTING_IMAGES, 'info_page_images');
        }
    }

    initSlugWarning() {
        const $slug = $(this.mapper.slug);
        const initialValue = ($slug.val() || '').trim();

        $slug.on('input', () => {
            const currentValue = ($slug.val() || '').trim();
            const $warning = $(this.mapper.slugChangeWarning);

            if (currentValue !== initialValue) {
                $warning.removeClass('d-none');
                return;
            }

            $warning.addClass('d-none');
        });
    }

    registerSubmit() {
        $(this.mapper.submitBtn).on('click', () => {
            this.handler.submitForm();
        });
    }
}

export default InfoPageEditController;
