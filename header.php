<?php
if (!preg_match('~(?<page>\w+)/index.php~', $_SERVER['PHP_SELF'], $match)) {
    die("Not a front controller\n");
}
$current_page = $match['page'];

$links = [
    ['name' => 'help', 'href' => 'help/', 'id' => 'get-help-link', 'text' => 'Get Help'],
    ['name' => 'deck', 'href' => 'deck/', 'id' => 'manage-decks-link', 'text' => 'Manage Decks'],
    ['name' => 'card', 'href' => 'card/', 'id' => 'study-cards-link', 'text' => 'Study Cards']
];
?>

<header>
    <nav>
        <ul id="navbar">
            <?php
            foreach ($links as $link) {
                $class = ($current_page == $link['name']) ? 'class="active"' : '';
                echo <<<HTML
                    <li><a href="{$link['href']}" id="{$link['id']}" {$class}>{$link['text']}</a></li>
                    HTML;
            }
            ?>
        </ul>
    </nav>
</header>
