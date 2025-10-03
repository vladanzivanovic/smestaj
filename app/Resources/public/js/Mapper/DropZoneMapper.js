class DropZoneMapper {
    constructor(parentWrapper) {

        this.names = DropZoneMapper.NAMES;

        this.body = $(this.names.body);

        if (parentWrapper) {
            this.body = parentWrapper;
        }
        
        this.loader = $(this.names.loader, this.body);
        this.placeholder = $(this.names.placeholder, this.body);
        this.fileWrapper = $(this.names.fileWrapper, this.body);
        this.file = $(this.names.file, this.body);
        this.input = $(this.names.input, this.body);

    }

    static get NAMES() {
        return {
            body: '.dropzone',
            loader: '.dropzone__loader',
            placeholder: '.dropzone-placeholder',
            fileWrapper: '.dropzone-file-wrapper',
            file: '.dropzone-file',
            input: '.dropzone__input',
            fileRemove: '.dropzone-close',
            main: '.main-image-btn'
        }
    };
}

export default DropZoneMapper;