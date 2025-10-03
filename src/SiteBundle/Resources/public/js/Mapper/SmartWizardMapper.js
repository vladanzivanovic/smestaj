class SmartWizardMapper {
    constructor() {
        this.document = $(document);
        this.navigationBtnWrapper = $('.sw-btn-group', this.document);
        this.extraBtnWrapper = '.sw-btn-group-extra';
        this.stepTab = '.step-tab';
    }
}

export default SmartWizardMapper;