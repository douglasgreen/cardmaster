<?php

namespace CardMaster;

use Exception;
use PDO;

class Application
{
    private $basePath;
    private $baseUrl;
    private $config;
    private $pdo;

    public function __construct()
    {
        session_start();

        $this->basePath = dirname(dirname(__FILE__)) . '/';
        $this->baseUrl = $this->getBaseUrl();

        $configFilePath = $this->basePath . '/config.ini';
        $this->config = parse_ini_file($configFilePath, true);

        $dbConfig = $this->getConfigSection('db');
        $dbManager = new DatabaseManager($dbConfig);
        $this->pdo = $dbManager->getConnection();
    }

    public function getAbsoluteUrl($relativePath) {
        return $this->baseUrl . $relativePath;
    }

    public function getBaseUrl() {
        $serverName = $_SERVER['SERVER_NAME'];
        $requestUri = $_SERVER['REQUEST_URI'];

        $baseUrl = '';

        // Find the position of the program name in the URL.
        $progDir = basename($this->basePath);
        $prognamePosition = strpos($requestUri, $progDir);

        // If program name is found, get the base path.
        if ($prognamePosition !== false) {
            $baseUrl = substr($requestUri, 0, $prognamePosition) . $progDir . '/';
            return 'https://' . $serverName . $baseUrl;
        } else {
            throw new Exception("Base URL not found");
        }
    }

    public function getConfigSection(string $section): array
    {
        if (!isset($this->config[$section])) {
            throw new Exception("Section not found");
        }
        return $this->config[$section];
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function redirect(string $path): void
    {
        header('Location: ' . $this->baseUrl . $path);
        exit();
    }
}
