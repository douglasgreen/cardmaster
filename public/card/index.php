<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use CardMaster\Application;
use CardMaster\Page;

$app = new Application();
$page = new Page($app);
$page->addCss("common.css");
$page->addCss("header.css");
$page->addCss("card/card.css");
$page->addJs("https://code.jquery.com/jquery-3.7.0.min.js");
$page->addJs("error_handler.js");
$page->addJs("card/card.js");
$page->addJs("card/color_code.js");
$page->addJs("card/text_to_speech.js");
$page->printHeader();
$page->printNavbar();

echo <<<HTML
    <div id="page-container">
        <div class="flashcard">
            <div id="deckName"></div>
            <div id="reviewCount"></div>
            <div id="question"></div>
            <div id="answer"></div>
            <div id="timeTaken"></div>
            <button id="next" class="btn">Press to see the answer</button>
            <div id="response">
                Was your answer correct?
            <div class="radio-container">
                <div>
                    <input type="radio" name="response" id="yes" value="y">
                    <label for="yes" class="yes-answer">Yes</label>
                </div>
    
                <div>
                    <input type="radio" name="response" id="no" value="n">
                    <label for="no" class="no-answer">No</label>
                </div>
            </div>
        </div>
        <div id="card-buttons">
            <button id="edit" class="btn">Edit Flashcard</button>
            <button id="delete" class="btn">Delete Flashcard</button>
        </div>
        <form id="editForm" style="display: none;">
            <label for="cardAnswer">Answer:</label>
            <textarea id="cardAnswer" rows="5" cols="51" maxlength="255"></textarea>
            <label for="cardQuestion">Question:</label>
            <textarea id="cardQuestion" rows="5" cols="51" maxlength="255"></textarea>
            <label for="cardNote">Note:</label>
            <textarea id="cardNote" rows="5" cols="51" maxlength="255"></textarea>
            <input class="btn" type="submit" value="Submit">
        </form>
    </div>
    HTML;

$page->printFooter();
