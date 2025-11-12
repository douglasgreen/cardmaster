$(document).ready(function () {
    $("#addDeckBtn").on("click", function () {
        $("#addDeckForm").toggle();
        $("#exportCardForm").hide();
        $("#importCardForm").hide();
    });

    $("#exportCardBtn").on("click", function () {
        $("#addDeckForm").hide();
        $("#exportCardForm").toggle();
        $("#importCardForm").hide();
    });

    $("#importCardBtn").on("click", function () {
        $("#addDeckForm").hide();
        $("#importCardForm").toggle();
        $("#exportCardForm").hide();
    });

    $("#exportCardForm").on("submit", function (e) {
        e.preventDefault();

        const deckId = $("#exportDeckId").val(); // getting selected deckId

        // POST data to the server
        $.ajax({
            url: "download.php",
            type: "POST",
            data: { deckId: deckId },
            xhrFields: {
                responseType: 'text'  // to handle text data
            },
            success: function(data, status, xhr) {
                // Create a link to download file
                var a = document.createElement('a');
                var url = window.URL.createObjectURL(new Blob([data], {type: 'text/csv'}));
                a.href = url;
                a.download = 'deck_' + deckId + '.csv';
                a.click();
                window.URL.revokeObjectURL(url);
                alert("Export complete");
            },
            error: function (jqXHR, textStatus, errorThrown) {
                handleError(jqXHR, textStatus, errorThrown, "There was an error downloading the deck.");
            },
        });
    });

    $(document).on("click", "#markAllInUseBtn", function () {
        $.ajax({
            url: "../decks/set_active.php",
            type: "POST",
            data: {active: true},
            success: function (response) {
                alert("All decks marked as in use.");
                location.reload();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                handleError(jqXHR, textStatus, errorThrown, "There was an error marking all decks as in use.");
            },
        });
    });

    $(document).on("click", "#markAllNotActiveBtn", function () {
        $.ajax({
            url: "../decks/set_active.php",
            type: "POST",
            data: {active: false},
            success: function (response) {
                alert("All decks marked as inactive.");
                location.reload();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                handleError(jqXHR, textStatus, errorThrown, "There was an error marking all decks as inactive.");
            },
        });
    });

    $("#newCardForm").on("submit", function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        $.ajax({
            url: "../cards/import.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function () {
                alert("Cards imported successfully");
                location.reload();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                handleError(jqXHR, textStatus, errorThrown, "There was an error importing the cards.");
            },
        });

        $(this).trigger("reset");
        $("#importCardForm").hide();
    });

    $("#newDeckForm").on("submit", function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        $.ajax({
            url: "create.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function () {
                alert("Deck added successfully");
                location.reload();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                handleError(jqXHR, textStatus, errorThrown, "There was an error adding the deck.");
            },
        });

        $(this).trigger("reset");
        $("#addDeckForm").hide();
    });

    $(document).on("click", ".activeBtn", function () {
        const activeButton = $(this);
        const deckId = $(this).data("id");

        $.ajax({
            url: "toggle_active.php",
            type: "POST",
            data: { deckId: deckId },
            success: function (deck) {
                const btnText = deck.deckActive === "1" ? "In Use" : "Not Active";
                activeButton.text(btnText);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                handleError(jqXHR, textStatus, errorThrown, "There was an error toggling the active state.");
            },
        });
    });

    $(document).on("click", ".deck-name", function () {
        const activeDeck = $(this);
        const deckId = $(this).data("id");
        const newName = prompt("Enter the new deck name:");
        if (newName) {
            $.ajax({
                url: "rename.php",
                type: "POST",
                data: { deckId: deckId, newName: newName },
                success: function (deck) {
                    activeDeck.text(deck.deckName);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    handleError(jqXHR, textStatus, errorThrown, "There was an error renaming the deck.");
                },
            });
        }
    });

    $(document).on("click", ".deleteBtn", function () {
        const deckId = $(this).data("id");
        if (confirm("Are you sure you want to delete this deck?")) {
            $.ajax({
                url: "delete.php",
                type: "POST",
                data: { deckId: deckId },
                success: function () {
                    $(`#${deckId}`).remove();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    handleError(jqXHR, textStatus, errorThrown, "There was an error deleting the deck.");
                },
            });
        }
    });

    $(document).on("click", ".resetBtn", function () {
        const deckId = $(this).data("id");
        if (confirm("Are you sure you want to reset this deck?")) {
            $.ajax({
                url: "reset.php",
                type: "POST",
                data: { deckId: deckId },
                success: function () {
                    location.reload();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    handleError(jqXHR, textStatus, errorThrown, "There was an error resetting the deck.");
                },
            });
        }
    });

    $.ajax({
        url: "../decks/read.php",
        type: "GET",
        success: function (decks) {
            decks.forEach((deck) => {
                const activeDesc = deck.deckActive === "1" ? "In Use" : "Not Active";
                $("#deckContainer").append(
                    `<div id="${deck.deckId}">
                        <h2 class="deck-name" data-id="${deck.deckId}" style="cursor: pointer">${deck.deckName}</h2>
                        <p>Cards: ${deck.cardCount}</p>
                        <p>Percent correct: ${deck.percentCorrect}%</p>
                        <p>Percent mastered: ${deck.percentMastered}%</p>
                        <button class="activeBtn" data-id="${deck.deckId}">${activeDesc}</button>
                        <button class="resetBtn" data-id="${deck.deckId}">Reset</button>
                        <button class="deleteBtn" data-id="${deck.deckId}">Delete</button>
                    </div>`
                );
            });
        },
        error: function (jqXHR, textStatus, errorThrown) {
            handleError(jqXHR, textStatus, errorThrown, "There was an error retrieving the decks.");
        },
    });

    $.ajax({
        url: "../languages/read.php",
        type: "GET",
        success: function (languages) {
            languages.forEach((language) => {
                $("#questionLang, #answerLang").append(
                    `<option value="${language.langId}">${language.langName}</option>`
                );
            });
        },
        error: function (jqXHR, textStatus, errorThrown) {
            handleError(jqXHR, textStatus, errorThrown, "There was an error retrieving the languages.");
        },
    });
});
