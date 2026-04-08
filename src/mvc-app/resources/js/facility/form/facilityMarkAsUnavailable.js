// Enable/disable time dropdowns when the row's checkbox is toggled.
(function () {
    function updateRow(day, enabled) {
        var from = document.getElementById(
            "facility_mark_as_unavailable_" + day + "_from_frm",
        );
        var to = document.getElementById(
            "facility_mark_as_unavailable_" + day + "_to_frm",
        );
        if (from) from.disabled = !enabled;
        if (to) to.disabled = !enabled;

        if (window.jQuery) {
            var $ = window.jQuery;
            if (from) $(from).trigger("chosen:updated");
            if (to) $(to).trigger("chosen:updated");
        }
    }

    function dayFromCheckboxId(id) {
        var m =
            id && id.match(/^facility_mark_as_unavailable_(.+)_active_frm$/);
        return m ? m[1] : null;
    }

    function bind() {
        var container = document.getElementById(
            "facility-mark-as-unavailable-modal",
        );
        if (!container) return;

        var checks = container.querySelectorAll(
            ".facility-mark-as-unavailable-active-chk",
        );
        checks.forEach(function (chk) {
            var day = dayFromCheckboxId(chk.id);
            if (!day) return;

            // Initial state
            updateRow(day, chk.checked);

            chk.addEventListener("change", function () {
                updateRow(day, chk.checked);
            });
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", bind);
    } else {
        bind();
    }
})();
