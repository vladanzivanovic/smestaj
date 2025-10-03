class DropZoneDom {
    generateHtml(fileWrapper, file, options)
    {
        let mainClass = file.isMain ? 'main-image' : '';
        let colorSelect = '';

        let li = $('<li>', {
            class: 'dropzone-file ' + mainClass,
            'data-name': file.fileName,
        });
        let img = $('<img>', {
            src: file.file,
            class: 'dropzone-file-img'
        });

        let mainBtn = $('<button>', {
            type: 'button',
            class: 'btn btn-icon main-image-btn',
        }).append($('<i>', {
            class: `fas ${file.isMain ? 'fa-check-double' : 'fa-check'}`,
        }));

        let removeBtn = $('<button>', {
            type: 'button',
            class: 'btn btn-icon dropzone-close',
        }).append($('<i>', {
            class: 'fas fa-trash'
        }));

        let buttonDiv = $('<div>', {
            class: 'dropzone-file__buttons'
        })
            .append(mainBtn)
            .append(removeBtn);


        fileWrapper.append(
            li.append(img)
                .append(buttonDiv)
        );
    }
}

export default DropZoneDom;