document.addEventListener('DOMContentLoaded', function() {

    // Highlight fields with validation errors
    document.querySelectorAll(".esseo-message p.setting-error-message").forEach(function(error_msg) {

        var error_setting = error_msg.getAttribute('data-title');

        document.querySelectorAll("label[for='" + error_setting + "']").forEach(function(label) {
            label.classList.add('error');
        });

        document.querySelectorAll("input[id='" + error_setting + "']").forEach(function(input) {
            input.classList.add('esseo-field-error');
        });

    });

    // Live character counter for the SEO meta description (works in the block editor)
    document.querySelectorAll(".esseo-meta-description").forEach(function(ta) {

        var max = parseInt(ta.getAttribute("maxlength"), 10) || 320;
        var out = ta.nextElementSibling;

        while (out && !out.classList.contains("esseo-charcount")) {
            out = out.nextElementSibling;
        }

        if (!out) {
            return;
        }

        var render = function() {
            out.textContent = ta.value.length + " / " + max;
        };

        ta.addEventListener("input", render);
        render();

    });

});
