import {MESSAGE} from "../Constants/MessageConstants";
import RegistrationMapper from "../Mapper/RegistrationMapper";
import baseValidator from "../../../../../../app/Resources/public/js/Validators/BaseValidator";
import registrationMapper from "../Mapper/RegistrationMapper";

require ('../../../../../../app/Resources/public/js/Validators/ValidationRuleHelper');

class RegistrationValidator {
    #mapper;
    #baseValidator;

    constructor() {
        if (!RegistrationValidator.instance) {
            this.#mapper = registrationMapper;
            this.#baseValidator = baseValidator;

            RegistrationValidator.instance = this;
        }

        return RegistrationValidator.instance;
    }

    validate() {
        let rules = {
            firstname: 'required',
            lastname: 'required',
            email:{
                required: true,
                email: true,
                // remote: Routing.generate('site_check_email_exists')
            },
            password: {
                required: true,
                minlength: 5
            },
            repassword: {
                required: true,
                minlength: 5,
                equalTo: "#registration-password"
            },
            hiddenRecaptcha: {
                required: function () {
                    grecaptcha.execute(window.user_registration)
                        .then(response => {
                            if (grecaptcha.getResponse(window.user_registration) == '') {
                                return true;
                            }
                            return false;
                        });

                }
            },
        };

        return this.#baseValidator.validate($(this.#mapper.form), rules);
    }

    // Public.validation = () => {
    //     let options = {
    //         ignore: ".ignore",
    //         rules: {
    //             firstName: 'required',
    //             lastName: 'required',
    //             email:{
    //                 required: true,
    //                 email: true,
    //             },
    //             password: {
    //                 required: true,
    //                 minlength: 5
    //             },
    //             rePassword: {
    //                 required: true,
    //                 minlength: 5,
    //                 equalTo: "#password"
    //             },
    //             newPassword: {
    //                 minlength: 5
    //             },
    //             reNewPassword: {
    //                 minlength: 5,
    //                 equalTo: '#new-password'
    //             },
    //             hiddenRecaptcha: {
    //                 required: function () {
    //                     grecaptcha.execute(window.user_registration)
    //                         .then(response => {
    //                             if (grecaptcha.getResponse(window.user_registration) == '') {
    //                                 return true;
    //                             }
    //                             return false;
    //                         });
    //
    //                 }
    //             },
    //         },
    //     };
    //
    //     $.extend(options, window.helpBlock);
    //
    //     mapper.form.validate(options);
    // };
    //
    // return Public;
};

const registrationValidator = new RegistrationValidator();

Object.freeze(registrationValidator);

export default registrationValidator;
