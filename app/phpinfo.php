<?php

file_exists('config.php') && require_once 'config.php';

if (!@$GLOBALS['PHPINFO']) {
    http_response_code(404);
    exit("404 Not Found");
}

phpinfo();
