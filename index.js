/** Define events. */

$(document).ready(function () {
    $("#switch").click(function () {
        $("#login").toggle();
        $("#register").toggle();
        $(this).text(function (i, text) {
            return text === "Switch to Register" ? "Switch to Login" : "Switch to Register";
        });
    });
});
