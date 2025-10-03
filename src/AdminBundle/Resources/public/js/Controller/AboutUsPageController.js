import SummerNote from "../Services/SummerNote";
import aboutUsPageMapper from "../Mapper/AboutUsPageMapper";
import AboutUsPageService from "../Services/AboutUsPageService";
import AboutUsHandler from "../Handler/AboutUsHandler";
import aboutUsEditValidator from "../Validators/AboutUsEditValidator";

class AboutUsPageController {
    constructor() {
        this.mapper = aboutUsPageMapper;
        this.editService = new AboutUsPageService();
        this.handler = new AboutUsHandler();
        this.validator = aboutUsEditValidator;

        this.summernote = new SummerNote();

        for (let i = 0; i < LOCALES.length; i++) {
            let locale = LOCALES[i];
            this.summernote.initialize($(this.mapper[`desc_${locale.code}`]), this.createCallBacksSummernote(this.mapper[`desc_${locale.code}`]));
        }

        $('.dropdown-toggle').dropdown();

        this.validator.validate(this.mapper.form);

        this.registerEvents();
    }

    createCallBacksSummernote(el)
    {
        return {
            onImageUpload: files => {
                this.editService.sendSummernoteFile($(el), files[0])
                    .then(response => {
                        $(el).summernote('insertImage', response.file_url, function ($image) {
                            $image.attr('data-filename', response.file_name);
                        });
                    })
            },
            onMediaDelete: target => {
                this.editService.removeSummernoteImage(target[0].dataset.filename);
            }
        }
    }

    registerEvents()
    {
        $(this.mapper.submitBtn).on('click touchend', (e) => {
            e.preventDefault();
            e.stopPropagation();

            this.handler.save();
        });
    }
}

export default AboutUsPageController;