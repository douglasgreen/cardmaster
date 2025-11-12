<?php

namespace CardMaster;

use Exception;

class Page
{
    private const TITLE = 'CardMaster: Flashcard Review Program';

    private $app;

    private $baseUrl = [];

    private $cssUrls = [];

    private $jsUrls = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function addCss(string $url): void
    {
        if (!preg_match('/^http/', $url)) {
            $url = $this->app->getAbsoluteUrl($url);
        }
        if (in_array($url, $this->cssUrls, true)) {
            throw new Exception('Duplicate CSS URL');
        }
        $this->cssUrls[] = $url;
    }

    public function addJs(string $url): void
    {
        if (!preg_match('/^http/', $url)) {
            $url = $this->app->getAbsoluteUrl($url);
        }
        if (in_array($url, $this->jsUrls, true)) {
            throw new Exception('Duplicate JS URL');
        }
        $this->jsUrls[] = $url;
    }

    public function printHeader()
    {
        echo "<!DOCTYPE html>\n";
        echo "<html>\n";
        echo "<head>\n";
        echo '<title>' . self::TITLE . "</title>\n";
        foreach ($this->cssUrls as $url) {
            echo "<link rel='stylesheet' type='text/css' href='{$url}' />\n";
        }
        echo "</head>\n";
        echo "<body>\n";
    }

    public function printNavbar(): void
    {
        if (!preg_match('~(?<page>\w+)/index.php~', $_SERVER['PHP_SELF'], $match)) {
            die("Not a front controller\n");
        }
        $current_page = $match['page'];

        $baseUrl = $this->app->getBaseUrl();

        $links = [
            [
                'name' => 'help',
                'href' => 'help/',
                'id' => 'get-help-link',
                'text' => 'Get Help',
            ],
            [
                'name' => 'deck',
                'href' => 'deck/',
                'id' => 'manage-decks-link',
                'text' => 'Manage Decks',
            ],
            [
                'name' => 'card',
                'href' => 'card/',
                'id' => 'study-cards-link',
                'text' => 'Study Cards',
            ],
        ];

        echo "<header>\n";
        echo "<nav>\n";
        echo "<ul id='navbar'>\n";
        foreach ($links as $link) {
            $class = ($current_page === $link['name']) ? 'class="active"' : '';
            echo <<<HTML
                <li><a href="{$baseUrl}{$link['href']}" id="{$link['id']}" {$class}>{$link['text']}</a></li>
                HTML;
        }
        echo "</ul>\n";
        echo "</header>\n";
        echo "</nav>\n";
    }

    public function printFooter()
    {
        echo "<footer>\n";
        foreach ($this->jsUrls as $url) {
            echo "<script src='{$url}'></script>\n";
        }
        echo "</footer>\n";
        echo "</body>\n";
        echo "</html>\n";
    }
}
