/** Define global variables */

var globalAnswerIetfTag;
var globalCorrectAttempts = 0;
var globalCurrentCard = {};
var globalNextCardId = null;
var globalQuestionIetfTag;

var globalButtonShowTime;
var globalButtonAutoclickTimer;
var globalClickCount = 0;
var globalTimeTaken;
var globalTotalTime = 0;


$(document).ready(function () {

    $("#delete").click(function () {
        var confirmDelete = confirm("Are you sure you want to delete this flashcard?");
        if (confirmDelete) {
            $.ajax({
                url: "delete.php",
                type: "POST",
                data: { cardId: globalNextCardId },
                success: function () {
                    alert("Flashcard deleted successfully.");
                    location.reload();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    handleError(jqXHR, textStatus, errorThrown, "There was an error deleting the flashcard.");
                },
            });
        }
    });

    $("#edit").click(function () {
        $("#cardAnswer").val(globalCurrentCard.cardAnswer);
        $("#cardQuestion").val(globalCurrentCard.cardQuestion);
        $("#cardNote").val(globalCurrentCard.cardNote);
        $("#editForm").show();
    });

    $("#editForm").submit(function (e) {
        e.preventDefault();
        var cardAnswer = $("#cardAnswer").val();
        var cardQuestion = $("#cardQuestion").val();
        var cardNote = $("#cardNote").val();

        if (cardAnswer !== null && cardQuestion !== null) {
            $.ajax({
                url: "edit.php",
                type: "POST",
                data: {
                    cardId: globalCurrentCard.cardId,
                    cardAnswer: cardAnswer,
                    cardQuestion: cardQuestion,
                    cardNote: cardNote,
                },
                success: function () {
                    alert("Flashcard updated successfully.");
                    $("#editForm").hide();
                    location.reload();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    handleError(jqXHR, textStatus, errorThrown, "There was an error editing the flashcard.");
                },
            });
        }
    });

    $("#next").click(function () {
        $("#next").hide();
        $("#answer").show();
        const clickTime = new Date().getTime();  // Record the time the button was clicked
        globalTimeTaken = (clickTime - globalButtonShowTime)/1000;  // Calculate the time difference in seconds
        clearTimeout(globalButtonAutoclickTimer);
        let timeDesc = "very slow";
        if (globalTimeTaken < 1) {
            timeDesc = "very fast";
        } else if (globalTimeTaken < 2) {
            timeDesc = "fast";
        } else if (globalTimeTaken < 4) {
            timeDesc = "medium";
        } else if (globalTimeTaken < 8) {
            timeDesc = "slow";
        }
         
        // Update click count and total time
        globalClickCount++;
        globalTotalTime += globalTimeTaken;

        $("#timeTaken").text("Time: " + globalTimeTaken.toFixed(1) + " seconds (" + timeDesc + ")").show();
        if (globalAnswerIetfTag) {
            speak("answer", globalAnswerIetfTag, function () {
                $("#response").show();
            });
        } else {
            var delay = Math.round(($("#answer").text().length / 25) * 1000) + 500;
            setTimeout(function () {
                $("#response").show();
            }, delay);
        }
    });

    $("#response").change(function () {
        var response = $('input[name="response"]:checked').val();

        var radio = $('input[name="response"]');
        radio.prop("disabled", true); // Disable the radio button

        var score = 0;
        if (response === "y") {
            globalCorrectAttempts++;
            // Bonus for fast response from 3 @ 1 s., 2 @ 2 s., 1 @ 4 s., 0 @ 8 s.
            score = 3 - Math.log2(globalTimeTaken);
            if (score > 3) {
                score = 3;
            } else if (score < 0) {
                score = 0;
            }
        } else if (response === "n") {
            // Wipe out score on wrong answer.
            score = -10;
        }
        $.ajax({
            url: "update_attempts.php",
            type: "POST",
            data: {
                cardId: globalNextCardId,
                correctAttempts: globalCorrectAttempts,
                score: score,
                timeTaken: globalTimeTaken
            },
            success: function () {
                setTimeout(function () {
                    $('input[name="response"]').prop("checked", false);
                    radio.prop("disabled", false);
                    getNextCard();
                }, 1000);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                handleError(jqXHR, textStatus, errorThrown, "There was an error updating the flashcard.");
            },
        });
    });

    $(document).keypress(function (event) {
        // Let each textarea handle its own key presses.
        if ($(event.target).is("textarea")) {
            return;
        }

        var keycode = event.keyCode ? event.keyCode : event.which;

        if (keycode == "32" && $("#next").is(":visible")) {
            // Enter key press
            $("#next").click();
        }

        if (keycode == "121" && $("#yes").is(":visible") && $("#yes").is(":enabled")) {
            // 'y' key press
            $("#yes").prop("checked", true).trigger("change");
        }

        if (keycode == "110" && $("#no").is(":visible") && $("#no").is(":enabled")) {
            // 'n' key press
            $("#no").prop("checked", true).trigger("change");
        }
    });

    $("#next").hide();
    getNextCard();

    function getNextCard() {
        $.ajax({
            url: "get_next.php",
            type: "GET",
            success: function (data) {
                if (data.card === null) {
                    $("#deckName").text("No flashcards available.");
                    $("#reviewCount").hide();
                    $("#answer").hide();
                    $("#timeTaken").hide();
                    $("#next").hide();
                    $("#question").hide();
                    $("#response").hide();
                    $("#card-buttons").hide();
                    $("#editForm").trigger("reset").hide();
                } else {
                    var bgColor = textToColorCode(data.card.cardQuestion);
                    $(".flashcard").css("background-color", bgColor);
                    globalCurrentCard = data.card;
                    globalNextCardId = data.card.cardId;
                    globalCorrectAttempts = data.card.correctAttempts || 0;
                    $("#answer")
                        .hide()
                        .text("Answer: " + data.card.cardAnswer);
                    $("#timeTaken").hide();
                    $("#response").hide();
                    $("#editForm").trigger("reset").hide();
                    $("#deckName").text("Deck: " + data.deck.deckName);

                    // Regular expression to match URLs ending with .gif, .jpg, or .png
                    var urlRegex = new RegExp(
                        "^(http://|https://)?[a-z0-9]+([-.]{1}[a-z0-9]+)*[.][a-z]{2,5}(:[0-9]{1,5})?(/.*)?[.](gif|jpg|png)$"
                    );
                    var isQuestionImage;
                    if (urlRegex.test(data.card.cardQuestion)) {
                    // If it's an image URL, use html() to insert an img tag
                        $("#question").html("Question: <img src='" + data.card.cardQuestion + "' alt='Question Image'>");
                        isQuestionImage = true;
                    } else {
                    // If not, use text() to insert the string as plain text
                        $("#question").text("Question: " + data.card.cardQuestion);
                        isQuestionImage = false;
                    }

                    var cardString;
                    if (data.reviewCount) {
                        cardString = data.reviewCount + (data.reviewCount === 1 ? " card" : " cards") + " to review";
                        $("#reviewCount").show().text(cardString);
                    } else if (data.newCount) {
                        cardString = data.newCount + " new" + (data.newCount === 1 ? " card" : " cards") + " to learn";
                        $("#reviewCount").show().text(cardString);
                    } else if (data.oldCount) {
                        cardString = data.oldCount + " old" + (data.oldCount === 1 ? " card" : " cards") + " to study";
                        $("#reviewCount").show().text(cardString);
                    } else {
                        $("#reviewCount").hide();
                    }
                    if (data.questionLanguage && !isQuestionImage) {
                        globalQuestionIetfTag = data.questionLanguage.ietfTag;
                        speak("question", globalQuestionIetfTag, function () {
                            $("#next").show();
                            // Record the time the button was shown
                            globalButtonShowTime = new Date().getTime();

                            // 15 seconds timer
                            globalButtonAutoclickTimer = setTimeout(function () {
                                $("#next").click();
                            }, 15000);
                        });
                    } else {
                        globalQuestionIetfTag = null;
                        var delay = Math.round(($("#question").text().length / 25) * 1000) + 500;
                        setTimeout(function () {
                            $("#next").show();

                            // Record the time the button was shown
                            globalButtonShowTime = new Date().getTime();

                            // 15 seconds timer
                            globalButtonAutoclickTimer = setTimeout(function () {
                                $("#next").click();
                            }, 15000);
                        }, delay);
                    }
                    if (data.answerLanguage) {
                        globalAnswerIetfTag = data.answerLanguage.ietfTag;
                    } else {
                        globalAnswerIetfTag = null;
                    }
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                handleError(jqXHR, textStatus, errorThrown, "There was an error getting the next flashcard.");
            },
        });
    }

});
