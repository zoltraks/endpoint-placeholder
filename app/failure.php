<?php

$code = 500;

if (isset($_GET['code'])) {
    $code = intval($_GET['code']);
    if ($code < 100 || $code > 599) {
        $code = 500;
    }
}

http_response_code($code);

if (isset($_GET['with'])) {
    echo $_GET['with'];
}
