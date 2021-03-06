define(
    [
        'jquery',
        'mage/validation',
        'mage/translate'
    ],
    function ($) {
        'use strict';

        return {

            /**
             * Validate checkout agreements
             *
             * @returns {Boolean}
             */
            validate: function () {
                var telephoneValidationResult = false;
                var num = $("#shipping-new-address-form input[name=telephone]").val();
                var codenum = $(".country.active").attr('data-dial-code') === undefined ?  $(".selected-flag").attr('title').split(": ")[1] : '+'+$(".country.active").attr('data-dial-code');
                var numLength = codenum.length;
                var num_after_replace = num; 
                
                if(num.indexOf(' ') >= 0)
                {
                    num = num.substr(num.indexOf(' ')+1);
                    num_after_replace = num;
                } else {
                    num = num.substr(num.indexOf(' ')+1);
                    num_after_replace = num.slice(numLength); 
                }
                num_after_replace = num_after_replace.replace(/\s/g, '');
                if(num_after_replace.length != window.phone_no_digits_for_checkout || (num_after_replace.charAt(0) == 0)) {
                    var htmlText = '<div for="telephone" generated="true" class="mage-error" id="telephone-error-shipping">'+$.mage.__('Please enter valid mobile number for the selected country.')+'</div>';
                    if(num_after_replace.length == 0)
                    {   
                        if($("#telephone-error-shipping").length) {
                            $("#telephone-error-shipping").remove();
                        }
                        $('#telephone-error-shipping').text('');
                        $('#shipping-new-address-form div.intl-tel-input').after(htmlText);
                    } 
                    telephoneValidationResult = false;
                } else {
                    telephoneValidationResult = true;
                }
                return telephoneValidationResult;
            }
        };
    }
);