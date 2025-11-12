<?php


require_once __DIR__ . '/../vendor/autoload.php';

use CardMaster\Application;
use CardMaster\Page;

$app = new Application();

$page = new Page($app);
$page->addCss("common.css");
$page->addCss("header.css");
$page->addJs("https://code.jquery.com/jquery-3.7.0.min.js");
$page->addJs("index.js");
$page->printHeader();
$page->printNavbar();
$page->printFooter();
