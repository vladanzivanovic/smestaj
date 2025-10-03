require ('../../../../../../app/Resources/public/js/Validators/ValidationRuleHelper');

export default (() => {
    var Public = {};

    Public.validate = function (form, errors) {
        form.validate().resetForm();
        $.each(errors, function (k,v) {
            $('input[name="'+k+'"]', form).rules('add', {
                backEndValidation: true,
                messages: {
                    backEndValidation: v
                }
            });
        });

       form.valid();
    };

    return Public;
});