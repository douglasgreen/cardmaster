<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use CardMaster\Application;
use CardMaster\DeckManager;
use CardMaster\Page;

$app = new Application();
$app->checkAuthenticationAndRedirect();
$pdo = $app->getPdo();
$userId = $app->getUserId();

$deckManager = new DeckManager($pdo);
$deckNames = $deckManager->readNames($userId);

$page = new Page($app);
$page->addCss("common.css");
$page->addCss("header.css");
$page->addCss("deck/deck.css");
$page->addJs("https://code.jquery.com/jquery-3.7.0.min.js");
$page->addJs("error_handler.js");
$page->addJs("deck/deck.js");
$page->printHeader();
$page->printNavbar();

?>
    <div id="page-container">
        <h1>CardMaster: Flashcard Deck Manager</h1>

        <button id="addDeckBtn">Add new deck</button>
        <button id="importCardBtn">Import cards to existing deck</button>
        <button id="exportCardBtn">Export cards from existing deck</button>

        <button id="markAllInUseBtn">Mark all decks in use</button>
        <button id="markAllNotActiveBtn">Mark all decks not active</button>

        <div id="addDeckForm" style="display: none">
            <h2>Add a new deck</h2>
            <form id="newDeckForm">
                <label for="deckName">Deck Name:</label><br>
                <input type="text" id="deckName" name="deckName" required><br>
                <label for="deckNote">Deck Note:</label><br>
                <textarea id="deckNote" name="deckNote" rows="5" cols="51" maxlength="255"></textarea><br>
                <label for="questionLang">Question Language:</label><br>
                <select id="questionLang" name="cardQuestionLangId">
                    <option value="" selected>None</option>
                </select><br>
                <label for="answerLang">Answer Language:</label><br>
                <select id="answerLang" name="cardAnswerLangId">
                    <option value="" selected>None</option>
                </select><br>
                <label for="deckFile">Upload Deck:</label><br>
                <input type="file" id="deckFile" name="deckFile" accept=".csv" required><br>
                <input class="btn" type="submit" value="Submit">
            </form>
        </div>

        <div id="importCardForm" style="display: none">
            <h2>Import Cards to Existing Deck</h2>
            <form id="newCardForm">
                <label for="deckId">Select Deck:</label><br>
                <select id="deckId" name="deckId" required>
                    <option value=""></option>
                    <?php foreach ($deckNames as $deckName): ?>
                        <option value="<?php echo $deckName['deckId']; ?>">
                            <?php echo htmlspecialchars($deckName['deckName'], ENT_QUOTES); ?>
                        </option>
                    <?php endforeach; ?>
                </select><br>
                <label for="answerOverride">Answer Override Options:</label><br>
                <div class="radio-group">
                    <input type="radio" id="appendAnswer" name="answerOverride" value="append" checked required>
                    <label for="appendAnswer">Append</label>
                </div>
                <div class="radio-group">
                    <input type="radio" id="overwriteAnswer" name="answerOverride" value="overwrite">
                    <label for="overwriteAnswer">Overwrite</label>
                </div>
                <div class="radio-group">
                    <input type="radio" id="skipAnswer" name="answerOverride" value="skip">
                    <label for="skipAnswer">Skip</label>
                </div>
                <label for="deckFile">Upload Cards:</label><br>
                <input type="file" id="deckFile" name="deckFile" accept=".csv" required><br>
                <input class="btn" type="submit" value="Submit">
            </form>
        </div>

        <div id="exportCardForm" style="display: none">
            <h2>Export Cards from Existing Deck</h2>
            <form id="exportCardForm">
                <label for="exportDeckId">Select Deck:</label><br>
                <select id="exportDeckId" name="exportDeckId" required>
                    <option value=""></option>
                    <?php foreach ($deckNames as $deckName): ?>
                        <option value="<?php echo $deckName['deckId']; ?>">
                            <?php echo htmlspecialchars($deckName['deckName'], ENT_QUOTES); ?>
                        </option>
                    <?php endforeach; ?>
                </select><br>
                <input class="btn" type="submit" value="Export">
            </form>
        </div>

        <div id="deckContainer">
        </div>
    </div>
<?php

$page->printFooter();
