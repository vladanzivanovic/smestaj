import AdsHandler from "../Handler/AdsHandler";
import AdsValidation from "../Validation/AdsValidation";

require ("jquery.scrollto");

export default (() => {

    var Public = {};
    var Private = {};
    var $$cachedWizards,
        $$accordion = $('.wizard[data-toggle="accordion"]'),
        $$tabs = $('.wizard[data-type="tabs"]'),
        $$accordionTab = '.panel',
        $$interests = $('input[name="interests"]');

    Private.options = {
            validation: false
        };
    Private.activeTabIndex = 0;


    $$cachedWizards = {
        tabs: []
    };

    /**
     * Modify wizard options
     * @param options
     */
    Public.setOptions = function (options) {
        tjq.extend(Private.options, options);
    };

    Public.closeAllAccordion = function () {
        $('.collapse').each(function (i, v) {
            $(this).collapse('hide');
        })
    };

    Public.tabsWizardInit = function () {
        var $li = $$tabs.children();
        Public.closeAllAccordion();

        Private.activeTabIndex = 0;
        $$cachedWizards.tabs = [];
        for(var t = 0; t < $li.length; t++){
            var href = $('a', $li[t]).attr('href');
            var total = $('.panel-collapse', $(href)).length;
            $($li[t]).removeClass('active');

            $$cachedWizards.tabs.push({href: href, totalAccordions: total});
        }
        $('a:first', $$tabs).tab('show');

        $('.panel-collapse:first', $($$cachedWizards.tabs[0].href)).collapse('show');
    };

    Public.moveTo = function (event) {
        let wizardType, element, tabIndex;

        let btn = $(event.target);
        let form = btn.closest('form');
        let parent = btn.closest($$accordionTab);

        Private.direction = btn.hasAndGetData('direction');

        if(Private.direction != 'prev' && Private.options.validation && !form.valid()) {
            parent.addClass('error-msg-fix');
            return false;
        }

        if(!btn.parent().hasClass('wizard-buttons')) {
            return;
        }

        if (btn.hasAndGetData('last-step')) {
            AdsHandler().save(btn);
            return;
        }


        wizardType = parent.closest('.wizard').hasAndGetData('type');

        const dropzoneName = parent.find('#dropzone').hasAndGetData('files');

        element = Private.getNextElement(parent);

        switch (wizardType){
            case 'accordion':
                if(element.length > 0) {
                    if (dropzoneName) {
                        const isValid = AdsValidation().validateImages(dropzoneName);

                        if (!isValid) {
                            $('#dropzone-error').removeClass('hide');
                            return false;
                        }
                    }
                    Private.openNextPrevAccordion(element);
                    return true;
                }

                Private.openInTab();
                break;
            case 'tabs':
                $('a[href="'+ $$cachedWizards.tabs[tabIndex].href +'"]', $$tabs).tab('show');

                break;
        }
    };
    
    Private.openInTab = function () {
        Private.getTabIndex();

        var tabIndex = this.activeTabIndex;
        var accordionParent = $('.panel-collapse:eq(0)', $($$cachedWizards.tabs[tabIndex].href)).parent();
        var accordionToOpen = this.direction && this.direction == 'prev' ? $$cachedWizards.tabs[tabIndex].totalAccordions-1 : 0;

        $('a[href="'+ $$cachedWizards.tabs[tabIndex].href +'"]', $$tabs).tab('show');

        if(accordionParent.hasClass('hide')) {
            this.openNextPrevAccordion(Private.getNextElement(accordionParent));

            return true;
        }

        $('.panel-collapse:eq('+accordionToOpen+')', $($$cachedWizards.tabs[tabIndex].href)).collapse('show');

        tjq.scrollTo($('.panel-collapse:eq('+accordionToOpen+')', $($$cachedWizards.tabs[tabIndex].href)));
    };

    Private.openNextPrevAccordion = function (element) {
        Public.closeAllAccordion();
        var accordionId = $('.panel-title a[href^="#"]', element).attr('href');
        var parent = $(accordionId).parent();

        if (element.length === 0) {
            this.openInTab();
        }

        if (parent.hasClass('hide')) {
            this.openNextPrevAccordion(this.getNextElement(parent));

            return false;
        }

        $(accordionId).collapse('show');
    };

    Private.getNextElement = (parent) => Private.direction && Private.direction === 'prev' ? $(parent).prev() : $(parent).next();

    Private.getTabIndex = () => Private.direction && Private.direction === 'prev' ? --Private.activeTabIndex : ++Private.activeTabIndex;

    Public.manualChangeTabDetect = (event) => {
        var href = $(event.target).attr('href');

        if(href)
            Private.activeTabIndex = $$cachedWizards.tabs.indexOf(href);

        return Private.activeTabIndex;

    };

    Public.getInterests = function () {

        $$interests.tagsinput({
            itemValue: function (item) {
                if(item.hasOwnProperty('CategoryId'))
                    return item.Name;

                return item.GenderName;
            },
            itemText: function (item) {
                if(item.hasOwnProperty('CategoryId'))
                    return item.Name;

                return item.GenderName;
            },
            typeahead: {
                source: interest,
                afterSelect: function(val) { this.$element.val(""); }
            }
        });
        return this;
    };

    Public.registerEvents = function () {
        $(".wizard[data-type='tabs'] a[href^='#']").on('click touchend', function (e) {
            e.stopPropagation();
            e.preventDefault();
            return false;
        });
        $(".wizard[data-type='accordion'] a[href^='#']").on('click touchend', function (e) {
            e.stopPropagation();
            e.preventDefault();
            return false;
        });
        $('.wizard .wizard-buttons button').on('click touchend', (e) => {
            e.stopPropagation();
            e.preventDefault();
            this.moveTo(e);
        });
        $('#saveEditAds').on('click touchend', function (e) {
            AdsHandler().save(e.currentTarget);
        });
        $('.wizard[data-type="accordion"]').on('shown.bs.collapse', function () {
            $(window).scrollTo(this.parentNode);
        })
    }

    return Public;
});