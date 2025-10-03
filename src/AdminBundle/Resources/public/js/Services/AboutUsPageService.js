class AboutUsPageService {
    sendSummernoteFile(el, file) {
        let data = new FormData();
        data.set('tmp_image', file);
        data.set('entity', 'about-us');

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
}

export default AboutUsPageService;