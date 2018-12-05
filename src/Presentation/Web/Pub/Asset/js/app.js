window.Popper = require("popper.js").default;

try {
    window.$ = window.jQuery = require("jquery");

    require("bootstrap");
} catch (e) {}

// Focus the first element that has an error.
$(".is-invalid:first input").focus();

// register Focusable jQuery extension
jQuery.extend(jQuery.expr[":"], {
    focusable: function(el, index, selector) {
        return $(el).is("a, button, :input, [tabindex]");
    }
});

// Disable form submit on pressing Enter key
$(".data-form").on("keypress", "input,select", function(e) {
    if (e.which == 13) {
        e.preventDefault();
        // Get all focusable elements on the page
        var $canfocus = $(":focusable");
        var index = $canfocus.index(document.activeElement) + 1;
        if (index >= $canfocus.length) index = 0;
        $canfocus.eq(index).focus();
    }
});

// Automatically dismiss alerts after several seconds
$("#divAlertStatus")
    .delay(4000)
    .fadeOut(600);
