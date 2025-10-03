import AdsValidation from "../Validation/AdsValidation";
import YouTubeService from "./YouTubeService";
import DropZoneService from "../../../../../../app/Resources/public/js/Services/DropZoneService";
import CategoryService from "./CategoryService";
import TagsService from "./TagsService";
import CityService from "./CityService";
import AppHelperService from "../../../../../../app/Resources/public/js/Helper/AppHelperService";
import AdsAdditionalInfo from "./AdsAdditionalInfo";
import AdsService from "./AdsService";
import SmartWizardMapper from "../Mapper/SmartWizardMapper";
import MapsService from "../../../../../../app/Resources/public/js/Services/MapsService";
import AdsHandler from "../Handler/AdsHandler";
import MapsDomHelper from "../../../../../../app/Resources/public/js/Services/MapsDomHelper";
import Loader from "../../../../../../app/Resources/public/js/Dom/Loader";
import SummerNote from "../../../../../AdminBundle/Resources/public/js/Services/SummerNote";
import adsEditMapper from "../Mapper/AdsEditMapper";

require('summernote');
require ('smartwizard/dist/js/jquery.smartWizard');

export default (() => {
    const Public = {},
          Private = {};

    Private.youtubeService = YouTubeService();
    Private.dropZoneService = DropZoneService();
    Private.roomsInfo = AdsAdditionalInfo();
    Private.ads = null;
    Private.mapper = adsEditMapper;
    Private.cityService = CityService();
    Private.summernote = new SummerNote();
    Private.handler = AdsHandler();
    Private.initialization = true;

    Public.initSetAds = function (ads) {
        Loader.pageLoaderToggle();

        $(document).on('custom_catcompleteclose', () => {
            Loader.hideLoader();
            Private.initialization = false;
        });

        Private.ads = ads;
        Private.youtubeService.init();
        Private.wizardMapper = new SmartWizardMapper();

        Private.setForm();

        Private.setEditAd();

        Private.wizardInstance = Private.setWizard();

        Private.gmapApi = new MapsService();

        Private.gmapApi.load().then(() => {
            Private.gmapApi.showMap();
            Private.gmapApi.registerEvents();

            Private.helper = new MapsDomHelper(Private.mapper, Private.gmapApi);

            this.resetForm();
            // Private.roomsInfo.roomModalInit();

            if(ads){
                AdsService().getById(ads).then(
                    Public.populateFormForEdit
                );
                return true;
            }

            $(Private.wizardMapper.stepTab, $(Private.mapper.adsForm)).each((i, el) => {
                el.classList.remove('done');
            });

            AppHelperService.uiElementsEvents();

            Loader.pageLoaderToggle();

            Private.registerEvents();

            Private.initialization = false;
        });
    };

    Public.beforeExitForm = function () {
        Private.wizardInstance = null;
        $(Private.mapper.adTemplate, $('#set_ad_wrapper')).remove();
        $('.room-btn').off();
        $(Private.mapper.category).off();
        $(Private.mapper.dismissBtn).off();
        $(Private.mapper.wizard).off();
        $(Private.mapper.city).off();
        $(Private.mapper.street).off();
        $('#saveEditAds').off();
    }

    Private.setForm = function () {
        const form = $(Private.mapper.originForm);
        const clonedForm = form.clone();

        clonedForm.removeClass('hide');
        clonedForm.attr('id', 'ads');
        $('form', clonedForm).attr('id', 'setAd');
        $('#smartwizard-origin', clonedForm).attr('id', 'smartwizard');
        $('[data-files="ads-origin"]', clonedForm).attr('data-files', 'ads');
        $('#dropzone-input', clonedForm).attr('name', 'ads');

        $(Private.mapper.formWrapper).append(clonedForm[0]);

        Private.summernote.setToolbar([Private.summernote.styleOptions, Private.summernote.fontOptions]);

        for (const [code, data] of Object.entries(LOCALES)) {
            Private.summernote.initialize($(Private.mapper[`description_${code}`], $(Private.mapper.adsForm)));
        }

        Private.validate = AdsValidation().adsValidation(Private.cityService);
    }

    Public.resetForm = function () {
        $(Private.mapper.adsForm)[0].reset();
        Private.validate.resetForm();

        sessionStorage.adsId = '';
        Private.dropZoneService.init($('[data-files="ads"]'));
        CategoryService.renderSelectBox($(Private.mapper.category));

        if(!Public.adsId) {
            TagsService().generateHtml();
        }
        Private.cityService.citiesTypeahead($(Private.mapper.adsForm));
        $(`${Private.mapper.paymentType}[value="1"]`).attr('checked', true);

        AppHelperService.uiElementsEvents();
    };

    Public.populateFormForEdit = (response) => {
        $('#Id').val(response.ads.id);

        for (const [code, data] of Object.entries(LOCALES)) {
            $(Private.mapper[`title_${code}`]).val(response ? response.ads.title : '');
            $(Private.mapper[`description_${code}`]).summernote('code', response ? response.ads.description : '');
        }

        $(Private.mapper.priceFrom).val(response ? response.ads.price_from : '');
        $(Private.mapper.priceTo).val(response ? response.ads.price_to : '');
        $(Private.mapper.priceFromPreSeason).val(response ? response.ads.pre_price_from : '');
        $(Private.mapper.priceToPreSeason).val(response ? response.ads.pre_price_to : '');
        $(Private.mapper.priceFromPostSeason).val(response ? response.ads.post_price_from : '');
        $(Private.mapper.priceToPostSeason).val(response ? response.ads.post_price_to : '');
        $(Private.mapper.city).val(response ? response.ads.city : '');
        $(Private.mapper.city).trigger('catcompletechange');
        $(Private.mapper.street).val(response ? response.ads.street : '');
        $(Private.mapper.website).val(response.ads.website ?? '');
        $(Private.mapper.facebook).val(response.ads.facebook ?? '');
        $(Private.mapper.instagram).val(response.ads.instagram ?? '');
        $(`${Private.mapper.paymentType}[value="${response.ads.payment_type}"]`).attr('checked', true);

        if (null === response.ads.lat) {
            Private.helper.getMapByAddress();
        }

        Private.helper.setCoordinates(response.ads.lat, response.ads.lng);

        if (response && response.user) {
            $(Private.mapper.contact.firstName).val(response.user.FirstName);
            $(Private.mapper.contact.surname).val(response.user.LastName);
            $(Private.mapper.contact.email).val(response.user.ContactEmail);
            $(Private.mapper.contact.city).val(response.user.CityName);
            $(Private.mapper.contact.street).val(response.user.Address);
            $(Private.mapper.contact.telephone).val(response.user.Telephone);
            $(Private.mapper.contact.mobile).val(response.user.MobilePhone);
            $(Private.mapper.contact.viber).val(response.user.Viber);
        }

        if(response.media) {
            Private.dropZoneService.setFiles(response.media, 'ads');
        }
        if(response.youtube.length > 0){
            Private.youtubeService.setFromArray(response.youtube);
        }
        if(Object.keys(response.tags).length > 0) {
            TagsService().generateHtml(response.tags);
        }

        Public.populateDropDowns(response);
        Private.setHiddenTags(response.ads.category_id);

        AppHelperService.uiElementsEvents();

        sessionStorage.adsId = response.ads.id;

        Private.registerEvents();
    };

    Public.populateDropDowns = (data) => {
        CategoryService.setSelected(data.ads.category_id, $(Private.mapper.category));
        // Public.toggleRooms();
        // Public.toggleRooms(data.ads.category_id);

        // if (data.additional_info) {
        //     Private.roomsInfo.populateAndRenderHtml(data.additional_info);
        // }
    };

    // Public.toggleRooms = (category) => {
    //     var categoryObj = CategoryService.getById(category);
    //
    //     $('.room-btn-wrapper').hide();
    //     if(categoryObj && categoryObj.Alias == 'sale-za-veselja') {
    //         $('.room-btn-wrapper').show();
    //     } else {
    //         Private.roomsInfo.removeAll();
    //     }
    // };

    Private.registerEvents = function () {
        $('.room-btn').on('click touchend', function () {
            Private.roomsInfo.setAddModal();
        });
        $(Private.mapper.category).on('change', function (e) {
            var category = $(this).val();

            Private.setHiddenTags(category);
        });

        $(Private.mapper.dismissBtn).on('click touchend', function (e) {
            $('a[href="#dashboard"]').click();
        })

        $(Private.mapper.wizard).on('leaveStep', (e, anchorObject, stepNumber, stepDirection) => {
            return $(Private.mapper.adsForm).valid();
        })

        $(Private.mapper.wizard).on('showStep', (e, anchorObject, stepNumber, stepDirection) => {
            for (let i = 0; i <= 7; i++) {
                if (i !== stepNumber) {
                    let stepHash = $('li > a', Private.wizardInstance.children('ul')).eq(i).prop('hash');

                    if (false === $(stepHash).hasClass('ignore-step')) {
                        $(stepHash).addClass('ignore-step');
                    }

                    continue;
                }

                $(anchorObject.prop('hash')).removeClass('ignore-step');
            }

            $(Private.mapper.contact.city).trigger('catcompletechange');

            if (stepNumber < 7) {
                $(Private.wizardMapper.extraBtnWrapper, $(Private.mapper.formWrapper)).addClass('hide');
                Private.wizardMapper.navigationBtnWrapper.removeClass('hide');

                return;
            }
            $(Private.wizardMapper.extraBtnWrapper, $(Private.mapper.formWrapper)).removeClass('hide');
        });

        $(Private.mapper.city).on('change', () => {
            if (false === Private.initialization) {
                Private.helper.getMapByAddress();
            }
        });

        $(Private.mapper.street).on('keyup', () => {
            if (false === Private.initialization) {
                Private.helper.getMapByAddress();
            }
        });

        $(Private.mapper.saveBtn).on('click touchend', function (e) {
            e.preventDefault();
            e.stopPropagation();

            if(e.type == 'touchend'){
                $(this).off('click');
            }

            if(!$(Private.mapper.adsForm).valid()){
                return false;
            }

            Private.handler.save(Private.cityService);
        });
    };

    Private.setEditAd = function () {
        if(Private.ads) {
            $('form #saveAds').addClass('hide');
            $('.edit-buttons').removeClass('hide');

            return true;
        }

        $('form #saveAds').removeClass('hide');
        $('.edit-buttons').addClass('hide');
    }

    Private.setHiddenTags = function (category) {
        var categoryObj = CategoryService.getById(category);

        $('#roomTypeAccordion').parent().removeClass('hide');

        if (categoryObj && categoryObj.Alias != 'sobe-apartmani') {
            $('#roomTypeAccordion').parent().addClass('hide');
        }
    }

    Private.setWizard = function () {
        const btnFinish = $('<button></button>').text(Translator.trans('save', null, 'messages', LOCALE))
            .addClass('btn-medium dark-blue1')
            .attr('id', 'saveEditAds')
            .attr('type', 'button');

        const options = {
            theme: 'arrows',
            showStepURLhash: false,
            toolbarSettings: {
                toolbarButtonPosition: 'end',
                toolbarExtraButtons: [btnFinish]
            },
            lang: {
                next: AppHelperService.capitalize(Translator.trans('next_page', null, 'messages', LOCALE)),
                previous: AppHelperService.capitalize(Translator.trans('prev_page', null, 'messages', LOCALE)),
            },
        };

        if (Private.ads) {
            $(Private.wizardMapper.stepTab, $(Private.mapper.formWrapper)).each((i, el) => {
                if (!el.classList.contains('active')) {
                    el.classList.add('done');
                }
            })
        }

        const wizardInstance = $(Private.mapper.wizard).smartWizard(options);

        $(Private.wizardMapper.extraBtnWrapper, $(Private.mapper.formWrapper)).addClass('hide');

        return wizardInstance;
    }

    return Public;
});
