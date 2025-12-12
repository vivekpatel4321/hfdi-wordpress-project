(function ($) {
    'use strict';

    // Add custom validation rule for phone numbers
    if ($.fn.validate) {
        $.validator.addMethod('phoneNumber', function(value, element) {
            // Allow empty values (rely on required rule)
            if (!value) {
                return true;
            }
            // Phone number pattern: exactly 10 digits
            return /^[0-9]{10}$/.test(value);
        }, 'Please enter a valid 10-digit phone number');

        // Add custom validation rule for names (letters, spaces, hyphens and apostrophes)
        $.validator.addMethod('lettersOnly', function(value, element) {
            if (!value) {
                return true;
            }
            // Accept basic Latin letters, spaces, hyphens and apostrophes
            return /^[A-Za-z\s\-']+$/.test(value);
        }, 'Please enter a valid first name (letters only)');

        // Add custom validation rule for email to ensure basic proper format
        $.validator.addMethod('validEmail', function(value, element) {
            if (!value) {
                return true;
            }
            // Basic RFC-like email pattern: user@domain.tld (simple, avoids exotic addresses)
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
        }, 'Please enter a valid email address');
    }

    window.houzezValidateElementor = function (form) {
        if ($.fn.validate && $.fn.ajaxSubmit) {
            var $form = $(form),
                submitButton = $form.find('.houzez-submit-button'),
                messageContainer = $form.find('.ele-form-messages'),
                errorContainer = $form.find('.error-container'),
                ajaxLoader = $form.find('.houzez-loader-js'),
                formOptions = {
                    beforeSubmit: function () {
                        ajaxLoader.addClass('loader-show');
                        submitButton.attr('disabled', 'disabled');
                        messageContainer.fadeOut('fast');
                        errorContainer.fadeOut('fast');
                    },
                    success: function (response, statusText, xhr, $form) {
                        response = $.parseJSON(response);
                        ajaxLoader.removeClass('loader-show');
                        submitButton.removeAttr('disabled');

                        if (response.success) {
                            $form.resetForm();
                            messageContainer
                                .html(
                                    '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                                        response.msg +
                                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>'
                                )
                                .fadeIn('fast');

                            if (houzez_vars.houzez_reCaptcha == 1) {
                                $form.find('.g-recaptcha-response').remove();
                                if (houzez_vars.g_recaptha_version == 'v3') {
                                    houzezReCaptchaLoad();
                                } else {
                                    houzezReCaptchaReset();
                                }
                            }

                            if (response.redirect_to != '') {
                                setTimeout(function () {
                                    window.location.replace(
                                        response.redirect_to
                                    );
                                }, 500);
                            }
                        } else {
                            messageContainer
                                .html(
                                    '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                                        response.msg +
                                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>'
                                )
                                .fadeIn('fast');
                        }
                    },
                };

            // Disable HTML5 native validation so jQuery Validate handles messages
            $form.attr('novalidate', 'novalidate');

            // Clear any previous error messages in the shared container
            errorContainer.empty();

            $form.validate({
                highlight: function (element, errorClass, validClass) {
                    $(element).addClass('is-invalid').removeClass('is-valid');
                },
                unhighlight: function (element, errorClass, validClass) {
                    $(element).removeClass('is-invalid');
                },
                // validate fields when they lose focus (on blur)
                onfocusout: function(element) {
                    // 'this' is the validator instance
                    this.element(element);
                },
                // disable validation while typing; validates on blur/submit instead
                onkeyup: false,
                errorPlacement: function(error, element) {
                    // Place error messages in the error container at the bottom
                    error.addClass('text-danger');
                    // ensure errors are appended (preserve previous behaviour)
                    errorContainer.append(error);
                },
                rules: {
                    // support both 'first_name' and generic 'name' fields
                    first_name: {
                        required: true,
                        lettersOnly: true
                    },
                    name: {
                        required: true,
                        lettersOnly: true
                    },
                    last_name: {
                        lettersOnly: true
                    },
                    email: {
                        required: true,
                        validEmail: true
                    },
                    mobile: {
                        required: true,
                        phoneNumber: true
                    },
                    home_phone: {
                        phoneNumber: true
                    },
                    work_phone: {
                        phoneNumber: true
                    }
                },
                messages: {
                    first_name: {
                        required: 'First name is required',
                        lettersOnly: 'Please enter a valid first name (letters only)'
                    },
                    name: {
                        required: 'First name is required',
                        lettersOnly: 'Please enter a valid first name (letters only)'
                    },
                    last_name: {
                        lettersOnly: 'Please enter a valid last name (letters only)'
                    },
                    email: {
                        required: 'Email address is required',
                        validEmail: 'Please enter a valid email address'
                    },
                    mobile: {
                        required: 'Mobile number is required',
                        phoneNumber: 'Please enter a valid 10-digit phone number'
                    }
                },
                submitHandler: function (form) {
                    $(form).ajaxSubmit(formOptions);
                },
            });
        } // end if jQuery.validate
    }; // end houzezValidateElementor

    // Initialize the validation when the document is ready
    $(document).ready(function () {
        $('.houzez-ele-form-js').each(function () {
            houzezValidateElementor(this);
        });

        // Initialize validation for property agent/contact forms (sidebar and bottom)
        $('.property-form-wrap form').each(function() {
            var $f = $(this);
            if ($.fn.validate && typeof $f.data('validator') === 'undefined') {
                $f.validate({
                    highlight: function (element, errorClass, validClass) {
                        $(element).addClass('is-invalid').removeClass('is-valid');
                    },
                    unhighlight: function (element, errorClass, validClass) {
                        $(element).removeClass('is-invalid');
                    },
                    // append error messages to the form's .form_messages container
                    errorPlacement: function(error, element) {
                        error.addClass('text-danger');
                        var $container = $f.find('.form_messages');
                        if ($container.length) {
                            $container.append(error);
                        } else {
                            element.after(error);
                        }
                    },
                    onfocusout: function(element) { this.element(element); },
                    onkeyup: false,
                    rules: {
                        name: { required: true, lettersOnly: true },
                        first_name: { required: true, lettersOnly: true },
                        last_name: { lettersOnly: true },
                        email: { required: true, validEmail: true },
                        mobile: { required: true, phoneNumber: true }
                    },
                    messages: {
                        name: { required: 'First name is required', lettersOnly: 'Please enter a valid first name (letters only)' },
                        first_name: { required: 'First name is required', lettersOnly: 'Please enter a valid first name (letters only)' },
                        last_name: { lettersOnly: 'Please enter a valid last name (letters only)' },
                        email: { required: 'Email address is required', validEmail: 'Please enter a valid email address' },
                        mobile: { required: 'Mobile number is required', phoneNumber: 'Please enter a valid 10-digit phone number' }
                    }
                });
            }
        });
    });

    // Re-initialize the validation when an Elementor popup is opened
    $(document).on('elementor/popup/show', function () {
        $('.houzez-ele-form-js').each(function () {
            houzezValidateElementor(this);
        });
    });
})(jQuery);

