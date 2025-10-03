import ProductEditHandler from "../Handler/Product/ProductEditHandler";
import productEditValidator from "../Validators/ProductEditValidator";
import Tipped from "@staaky/tipped";
import productEditMapper from "../Mapper/ProductEditMapper";
import DropZoneService from "../../../../../../app/Resources/public/js/Services/DropZoneService";
import MapsService from "../../../../../../app/Resources/public/js/Services/MapsService";
import MapsDomHelper from "../../../../../../app/Resources/public/js/Services/MapsDomHelper";
import YouTubeService from "../../../../../SiteBundle/Resources/public/js/Services/YouTubeService";
import datePicker from 'bootstrap-datepicker';
import SummerNote from "../Services/SummerNote";

require ('select2/dist/js/select2.full.min');
require('jquery-tags-input/dist/jquery.tagsinput.min');


class ProductEditController {
    constructor() {
        this.mapper = productEditMapper;
        this.dropZone = DropZoneService();
        this.validator = productEditValidator;
        this.mapService = new MapsService();
        this.mapsDomHelper = new MapsDomHelper(this.mapper, this.mapService);
        this.youtubeService = YouTubeService();
        this.summernote = new SummerNote();

        this.mapService.load().then(() => {
            this.mapService.showMap();
            this.mapService.registerEvents();
        });

        this.setDatePickerElm($(this.mapper.paymentDate));

        this.summernote.setToolbar([this.summernote.styleOptions, this.summernote.fontOptions]);
        this.summernote.initialize($(this.mapper.description_rs));

        this.registerEvents();

        this.initializeForm();
    }

    initializeForm()
    {
        this.dropZone.init($('[data-files="product"]'));
        this.initializeSelect();
        this.youtubeService.init();

        Tipped.create('.cleaning-icons');

        if (IS_EDIT && COORDINATES) {
            this.mapService.setCoordinates(COORDINATES.lat, COORDINATES.lng);
        }

        this.validator.validate();
    }

    initializeSelect() {
        $(this.mapper.tags).select2();
        $(this.mapper.category).select2();
        $(this.mapper.city).select2();
        $(this.mapper.contactCity).select2();
        $(this.mapper.owner).select2();

        if (IS_EDIT) {
            this.dropZone.setFiles(IMAGES, 'product');

            $(this.mapper.category).trigger('change');
        }
    }

    setDatePickerElm(elm) {
        elm.datepicker({
            format: "dd.mm.yyyy",
            todayHighlight: true,
            autoclose: true
        });
    };

    registerEvents() {
        $(this.mapper.submitBtn).on('click touchend', e => {
            const handler = new ProductEditHandler();

            handler.save();
        })

        $(this.mapper.city).on('change', () => {

            this.mapsDomHelper.getMapByAddress();
        });

        $(this.mapper.street).on('keyup', () => {
            this.mapsDomHelper.getMapByAddress();
        });

        // $(this.mapper.category).on('change', e => {
        //     const selectedCategory = $(e.currentTarget).val();
        //
        //     let type = null;
        //
        //     $(this.mapper.materialClothes).addClass('d-none');
        //     $(this.mapper.materialShoes).addClass('d-none');
        //
        //     for(let i in CATEGORIES) {
        //         let category = CATEGORIES[i];
        //
        //         if (category.value === selectedCategory){
        //             type = category.type;
        //
        //             break;
        //         }
        //     }
        //
        //     $(`#material_${type}`).removeClass('d-none');
        // })
    }
}

export default ProductEditController;