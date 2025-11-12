<?php


require_once __DIR__ . '/../vendor/autoload.php';

use CardMaster\Application;
use CardMaster\Page;

$app = new Application();

if (isset($_SESSION['userId'])) {
    header("Location: card/index.php");
}

$page = new Page($app);
$page->addCss("common.css");
$page->addCss("header.css");
$page->addJs("https://code.jquery.com/jquery-3.7.0.min.js");
$page->addJs("index.js");
$page->printHeader();
$page->printNavbar();

echo <<<HTML
    <div id="page-container">
        <form id="login" method="POST" action="login/index.php">
            <h2>Login</h2>
            <label for="username">Username:</label><br>
            <input type="text" id="username" name="username"><br>
            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password"><br>
            <input class="btn" type="submit" value="Login">
        </form>

        <form id="register" method="POST" action="register/index.php" style="display:none;">
            <h2>Register</h2>
            <label for="rusername">Username:</label><br>
            <input type="text" id="rusername" name="username"><br>
            <label for="rpassword">Password:</label><br>
            <input type="password" id="rpassword" name="password"><br>
            <input class="btn" type="submit" value="Register">
        </form>

        <button class="btn" id="switch">Switch to Register</button>
    </div>
    HTML;

$page->printFooter();
