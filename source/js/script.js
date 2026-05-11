/* global jQuery */

jQuery(function() {

    // Highlight fields with validation errors
    var error_msg = jQuery(".esseo-message p[class='setting-error-message']");

    if (error_msg.length !== 0) {

        jQuery(".esseo-message").each(function() {

            var error_setting = jQuery(this).find("p[class='setting-error-message']").attr('data-title');

            jQuery("label[for='" + error_setting + "']").addClass('error');
            jQuery("input[id='" + error_setting + "']").css('border-color', 'red');

        });

    }

});
