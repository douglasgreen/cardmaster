function handleError(jqXHR, textStatus, errorThrown, userMessage) {
    if (jqXHR.status == 400) {
        var error = jqXHR.responseJSON;
        console.error("Error: " + error.message);
        alert(userMessage);
    }
    else if (jqXHR.status == 401) {
        // Redirect to login page
        window.location.href = "../login/";
    }
    else {
        console.log(jqXHR, textStatus, errorThrown);
        alert(userMessage);
    }
}
