class BlogEditMapper {
    constructor() {
        this.form = $('#edit_form');
        this.title_rs = $('#blog_title_rs', this.form);
        this.shortDesc_rs = $('#blog_short_description_rs', this.form);
        this.desc_rs = $('#blog_description_rs', this.form);
        this.title_en = $('#blog_title_en', this.form);
        this.shortDesc_en = $('#blog_short_description_en', this.form);
        this.desc_en = $('#blog_description_en', this.form);
        this.blog_tags = $('#tags', this.form);
        this.submitBtn = $('#blog_submit');
    }
}

export default BlogEditMapper;