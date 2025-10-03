class DropZoneEvents {
    constructor(dropzone, mapper) {
        mapper.fileWrapper.on('click touchend', mapper.names.fileRemove, e => {
            dropzone.deleteFile(e.currentTarget);
        });
        mapper.fileWrapper.on('click touchend', mapper.names.main, e => {
            dropzone.setMainImage(e.currentTarget);
        });
        mapper.fileWrapper.on('change', '.dropdown-colors', e => {
            dropzone.setColor(e.currentTarget);
        })
    }
}

export default DropZoneEvents;