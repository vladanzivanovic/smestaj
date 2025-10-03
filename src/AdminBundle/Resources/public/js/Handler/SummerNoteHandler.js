class SummerNoteHandler {
    sendSummernoteImage(el, file, entity) {
        let data = new FormData();
        data.set('tmp_image', file);
        data.set('entity', entity);

        return $.ajax({
            type: 'POST',
            url: Routing.generate('admin.summernote_image_resize'),
            data: data,
            contentType: false,
            processData: false,
        });
    }

    removeSummernoteImage(filename) {
        return $.ajax({
            type: 'DELETE',
            url: Routing.generate('admin.remove_summernote_image', {filename}),
            dataType: 'json'
        })
    }

    sendSummernoteFile(el, file, entity) {
        let data = new FormData();
        data.set('file', file);

        return $.ajax({
            type: 'POST',
            url: Routing.generate('admin.summernote_document_upload'),
            data: data,
            contentType: false,
            processData: false,
        });
    }

    removeSummernoteFile(filename) {
        return $.ajax({
            type: 'DELETE',
            url: Routing.generate('admin.remove_summernote_image', {filename}),
            dataType: 'json'
        })
    }
}

export default SummerNoteHandler;