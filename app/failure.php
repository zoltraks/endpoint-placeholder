<?php

$code = 500;

if (isset($_GET['code'])) {
    $code = intval($_GET['code']);
    if ($code < 100 || $code > 599) {
        $code = 500;
    }
}

header('X-Request-Method: ' . $_SERVER['REQUEST_METHOD']);

http_response_code($code);

if (isset($_GET['with'])) {
    echo $_GET['with'];
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
        $payload = $_POST;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES) && !empty($_FILES)) {
        $payload = $_FILES;
    } else {
        $payload = file_get_contents('php://input');
    }
    echo $payload;
}

