<?php

require_once __DIR__ . '/../vendor/autoload.php';

use CardMaster\Application;
use CardMaster\Page;

$app = new Application();

$page = new Page($app);
$page->addCss("common.css");
$page->addCss("header.css");
$page->printHeader();
$page->printNavbar();

echo <<<HTML
    <div id="page-container">
        <h1>Welcome to CardMaster!</h1>
        <h2>Using Our Program</h2>
        <p>
            Our flashcard program is a helpful tool for your learning and revision. With its user-friendly
            interface, you can easily manage your study materials and focus on the areas you need the most.
        </p>
        
        <h2>Main Menu</h2>
        <p>The main menu has three options:</p>
        <ul>
            <li>
                <strong>Get Help:</strong>
                 This page you're reading right now, to provide you with the information you need to get the
                 most out of the program.
            </li>
            <li>
                <strong>Manage Decks:</strong>
                 Where you can create, modify, and delete your flashcard decks.
            </li>
            <li>
                <strong>Study Cards:</strong>
                 Where you can review, learn new, and study existing cards.
            </li>
        </ul>
        
        <h2>Manage Decks</h2>
        <p>In the Manage Decks section, you can manage all your flashcard decks.</p>
        <p>
            To add a new deck, click on the "Add new deck" button and fill out the form, which consists of
            Deck Name, Question Language, and Answer Language. The languages are optional. If you provide them,
            the card will be read aloud in that language when it is viewed.
        </p>
        
        <p>
            Below the "Add new deck" button, you can view all your existing decks. You can click on the deck
            name to change it. The number of cards in the deck is listed under the name.
        </p>
        
        <p>
            Each deck has a toggle button, allowing you to mark it as either "Not Active" or "In Use". This
            feature allows you to concentrate on one or more decks at a time.
        </p>
        
        <p>To delete a deck, simply click the delete button next to the relevant deck.</p>
        <h2>Study Cards</h2>
        <p>In the Study Cards section, you can review your flashcards. They are categorized into:</p>
        <ul>
            <li>
                <strong>Cards to review:</strong>
                 These are cards that you have studied before and are due for review.
            </li>
            <li>
                <strong>Cards to learn:</strong>
                 These are new cards that you haven't encountered yet.
            </li>
            <li>
                <strong>Cards to study:</strong>
                 These are cards that you have studied, but they're not yet due for review. However, you can
                 review them if you wish.
            </li>
        </ul>
        
        <p>
            Each card has a "Press to see the answer" button, which is enabled after the card's content has
            been read out loud. This button can also be activated using the spacebar. Below this button, there
            are options to Edit Flashcard and Delete Flashcard.
        </p>
        
        <p>
            Once the answer is revealed, you can mark whether you got the answer right or wrong using the
            'yes' or 'no' radio buttons. These buttons can also be activated using the Y or N keys. Once you've
            done that, the next card will automatically be presented.
        </p>
        
        <h2>Card Review Schedule</h2>
        <p>
            Cards are scheduled for review using a formula that considers your last attempt and how many times
            you've correctly answered the card. In simple terms, the more correct attempts you make, the longer
            the interval before the card is due for review. The initial interval after no correct attempts is
            5 minutes. After each correct attempt, the interval is multiplied by 5. This system is designed to
            improve your memory retention by gradually increasing the time intervals between reviews.
        </p>
    </div>
    HTML;

$page->printFooter();
