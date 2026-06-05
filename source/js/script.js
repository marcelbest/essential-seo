/* global jQuery */

jQuery(function() {

    // Highlight fields with validation errors
    var error_msg = jQuery(".esseo-message p.setting-error-message");

    if (error_msg.length !== 0) {

        jQuery(".esseo-message").each(function() {

            var error_setting = jQuery(this).find("p.setting-error-message").attr('data-title');

            jQuery("label[for='" + error_setting + "']").addClass('error');
            jQuery("input[id='" + error_setting + "']").addClass('esseo-field-error');

        });

    }

    // Live character counter for the SEO meta description (works in the block editor)
    jQuery(".esseo-meta-description").each(function() {

        var ta  = jQuery(this);
        var max = parseInt(ta.attr("maxlength"), 10) || 320;
        var out = ta.nextAll(".esseo-charcount").first();

        if (out.length === 0) {
            return;
        }

        var render = function() {
            out.text(ta.val().length + " / " + max);
        };

        ta.on("input", render);
        render();

    });

});
