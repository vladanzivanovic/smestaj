import Cache from "../Helper/CacheHelper";
import DropZoneMapper from "../Mapper/DropZoneMapper";
import Loader from "../Dom/Loader";
import DropZoneEvents from "./DropZoneEvents";
import DropZoneDom from "../Dom/DropZoneDom";
import toastrService from "./ToastrService";

export default (() => {

    var Public = {}, Private = {};

    Private.isFilesBeingUpload = false;
    Private.arrayName = null;
    Private.toastr = toastrService;
    Private.mapper = null;
    Public.dom = new DropZoneDom();

    Public.init = function (parentWrapper) {
        Private.mapper = new DropZoneMapper(parentWrapper);

        new DropZoneEvents(Public, Private.mapper);

        if (!Cache.has('adsFiles')) {
            Cache.set('adsFiles', []);
            Cache.set('adsFileNames', []);
            Cache.set('adsDeletedFiles', []);
        }

        this.reset(parentWrapper.data('files'));

        Private.enableDropzone();

        Private.mapper.input.off().on("change", function (e) {
            let files = e.target.files;

            Private.addFiles($(e.target).parents('#dropzone'), files);
            e.currentTarget.value = '';
        });
    };

    Public.reset = function ($name) {
        if ($name) {
            Private.arrayName = $name;
        }

        Cache.get('adsFiles')[Private.arrayName] = [];
        Cache.get('adsDeletedFiles')[Private.arrayName] = [];
        Cache.get('adsFileNames')[Private.arrayName] = [];

        Private.mapper.file.each((i, el) => {
            $(el).remove();
        });
    };

    Public.deleteFile = function (self) {
        Private.arrayName = $(self).parents('.dropzone').data('files');

        const listEl = $(self).parents('li');
        const index = $('li', Private.mapper.fileWrapper).index(listEl);
        const filesCache = Cache.get('adsFiles')[Private.arrayName];
        const deletedFilesCache = Cache.get('adsDeletedFiles')[Private.arrayName];
        const isMainImg = filesCache[index].isMain;
        const fileName = filesCache[index].fileName;

        if (filesCache[index].id) {
            filesCache[index].deleted = true;
            deletedFilesCache.push(filesCache[index]);
            filesCache.splice(index, 1);
        } else {
            $.ajax({
                'type': 'DELETE',
                'url': Routing.generate('remove_tmp_image', {'filename': fileName})
            });

            filesCache.splice(index, 1);
        }

        listEl.remove();

        if (isMainImg) {
            Private.changeMainImage(index, null, $('li', Private.mapper.fileWrapper).eq(0));
        }

        Private.togglePlaceholder(filesCache.length === 0);
    };

    Public.setMainImage = function (self) {
        let newMain = $(self).parents('li'),
            oldMain = $('li.main-image', Private.mapper.fileWrapper),
            index = $('li', Private.mapper.fileWrapper).index(newMain);

        Private.arrayName = $(self).parents('.dropzone').data('files');

        Private.changeMainImage(index, oldMain, newMain);
    };

    Public.setColor = function(self) {
        Private.arrayName = $(self).parents('.dropzone').data('files');

        const listEl = $(self).parents('li');
        const index = $('li', Private.mapper.fileWrapper).index(listEl);
        const filesCache = Cache.get('adsFiles')[Private.arrayName];

        filesCache[index].color = $(self).val();
    }

    Public.getFilesArray = function ($name) {
       return $.merge(Cache.get('adsFiles')[$name], Cache.get('adsDeletedFiles')[$name]);
    };

    Public.getMainFile = name => {
        const mainFile = Cache.get('adsFiles')[name].filter(file => file.isMain);

        return mainFile.length > 0 ? mainFile[0] : null;
    }

    Public.setFiles = function (files, name) {
        Private.setFilesArray(name, true);
        Private.populateArrays(files);
        Private.generateHtml(files);
    };

    Private.changeMainImage = (index, oldMain, newMain) => {
        const filesCache = Cache.get('adsFiles')[Private.arrayName];
        $.each(filesCache, function (i, file) {
            file.isMain = false;
        });

        if (filesCache.length > 0 && index > -1) {
            filesCache[index].isMain = true;
        }

        if (oldMain) {
            oldMain.removeClass('main-image');
            $('.main-image-btn i', oldMain).addClass('fa-check').removeClass('fa-check-double');

        }
        newMain.addClass('main-image');
        $('.main-image-btn i', newMain).removeClass('fa-check').addClass('fa-check-double');
    }

    Private.addFiles = (dropZoneElm, files) => {
        let requests = [];

        const counter = files.length - 1;
        const maxFiles = dropZoneElm.data('max-files');

        Private.setFilesArray(dropZoneElm.data('files'));

        if (files.length === 0 || false === Private.checkMaxFiles(maxFiles, files)) {
            return false;
        }

        Loader.pageLoaderToggle();

        // Private.mapper.loader.removeClass('hide');

        // Loader.generateLoader(Private.mapper.loader, Translator.trans('dropzone_loader', null, 'messages', LOCALE), true);
        Private.disableDropzone();

        $.each(files, function (i, v) {
            requests.push(Private.processNewFile(v, counter, i));
        });

        Promise.all(requests)
            .then(function (response) {
                Private.populateArrays(response);
                Private.generateHtml(response);

                Private.enableDropzone();

                Loader.pageLoaderToggle();
                Private.mapper.loader.addClass('hide');
            })
            .catch(error => {
                Loader.removeLoader();
                Private.enableDropzone();
                Private.mapper.loader.addClass('hide');
            });

    }

    /**
     * Create html list with images
     */
    Private.generateHtml = function (files) {
        let hasFilesInDOM = $('li', Private.mapper.fileWrapper).length > 0;
        let mainImageIndex = files.findIndex(function (item) {
            return item.isMain === true;
        });
        let isMainImageSet = $('li.main-image', Private.mapper.fileWrapper).length > 0 || mainImageIndex > -1;
        let hasFiles = false;

        Private.togglePlaceholder();

        if ((hasFilesInDOM || files.length > 0) && !isMainImageSet) {
            files[0].isMain = true;
        }

        $.each(files, function (i, v) {
            if (!v.isDeleted) {
                hasFiles = true;
                Public.dom.generateHtml($(Private.mapper.fileWrapper), v, Private.options);
            }
        });

        if (hasFiles) {
            Private.togglePlaceholder(false);
        }
    };

    Private.togglePlaceholder = placeholderOn => {
        $(Private.mapper.placeholder).show();
        $(Private.mapper.fileWrapper).hide();

        if (false === placeholderOn) {
            $(Private.mapper.placeholder).hide();
            $(Private.mapper.fileWrapper).show();
        }
    }

    Private.placeHolder = function () {
        var li = $('<li>', {
            class: 'dropzone-file '
        });
        var remove = $('<span>', {
            class: 'dropzone-close col-xs-12 col-md-6'
        });
        var img = $('<img>', {
            src: v.file
        });

        $(Private.mapper.fileWrapper).append(
            li.append(remove)
                .append(img)
        );
    };

    Private.processNewFile = function (file, counter, index) {
        let result = $.Deferred();
        let data = new FormData();
        let fileNames = Cache.get('adsFileNames')[Private.arrayName];

        data.set('tmp_image', file);

        fileNames.push(file.name);

        return $.ajax({
            type: 'POST',
            url: Routing.generate('site_ads_image_resize_on_fly'),
            data: data,
            contentType: false,
            processData: false,
        });
    };

    Private.populateArrays = function (response) {
        $.merge(Cache.get('adsFiles')[Private.arrayName], response);

    };

    Private.setFilesArray = function (name, shouldReset = false) {
        const filesCache = Cache.get('adsFiles');
        const fileNames = Cache.get('adsFileNames');
        const deletedFiles = Cache.get('adsDeletedFiles');
        Private.arrayName = name;

        if (!filesCache.hasOwnProperty(Private.arrayName) || true === shouldReset) {
            filesCache[Private.arrayName] = [];
            fileNames[Private.arrayName] = [];
            deletedFiles[Private.arrayName] = [];
        }

        if (filesCache[Private.arrayName].length > 0 && !$('#dropzone-error').hasClass('hide')) {
            $('#dropzone-error').addClass('hide');
        }
    }

    Private.removeNotification = (counter, index) => {
        if (counter === index) {
            Private.isFilesBeingUpload = false;
            Private.toastr.remove();
        }
    }

    Private.attachDragOver = function (e) {
        e.preventDefault();
        $(this).addClass('dropzone-hover');
    };

    Private.attachDragLeave = function (e) {
        e.preventDefault();
        $(this).removeClass('dropzone-hover');
    };

    Private.attachDragEnter = function (e) {
        e.preventDefault();
    };

    Private.attachDrop = function (e) {
        e.stopPropagation();
        e.preventDefault();

        Private.addFiles($(this), e.originalEvent.dataTransfer.files);
        $(this).removeClass('dropzone-hover');
    };

    Private.disableDropzone = () => {
        Private.mapper.body.unbind('dragover', Private.attachDragOver);
        Private.mapper.body.unbind('dragenter', Private.attachDragEnter);
        Private.mapper.body.unbind('dragleave', Private.attachDragEnter);
        Private.mapper.body.unbind('drop', Private.attachDrop);
        Private.mapper.input.attr('disabled', true);
    }

    Private.enableDropzone = () => {
        Private.mapper.body.bind('dragover', Private.attachDragOver);
        Private.mapper.body.bind('dragenter', Private.attachDragEnter);
        Private.mapper.body.bind('dragleave', Private.attachDragEnter);
        Private.mapper.body.bind('drop', Private.attachDrop);
        Private.mapper.input.removeAttr('disabled');
    }

    Private.checkMaxFiles = (maxFiles, files) => {
        const filesCache = Cache.get('adsFiles')[Private.arrayName].filter(file => !file.isDeleted);
        const count = filesCache.length + files.length;

        if (!maxFiles) {
            return true;
        }

        if (count > maxFiles) {
            Private.toastr.error( Translator.trans('files.limit_reached', null, 'validators', LOCALE));

            return false;
        }

        return true;
    }

    return Public;
});
