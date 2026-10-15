<?php

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

chdir(__DIR__);

file_exists(__DIR__ . '/config.php') && require_once __DIR__ . '/config.php';

$ROOT = dirname(__DIR__);

$UPLOAD = @$GLOBALS['UPLOAD'] ?: (is_dir('/home/upload') ? '/home/upload' : "$ROOT/upload");
$PERMISSIONS = @$GLOBALS['PERMISSIONS'] ?: 0777;
$LIMIT = @$GLOBALS['LIMIT'] ?: 32768;

$LOG = @$GLOBALS['LOG'] ?: ini_get("error_log") ?: (is_dir('/var/log/php') ? '/var/log/php/error.log' : "$ROOT/log/php/error.log");

if (!is_dir(dirname($LOG))) {
    @mkdir(dirname($LOG), 0777, true);
}

function message($message, $severity = "") {
    $now = DateTime::createFromFormat("U.u", number_format(microtime(true), 6, ".", ""));
    $text = "";
    $text .= $now->format("Y-m-d H:i:s.v");
    $text .= " " . $message . " <br>\n";
    global $LOG;
    @error_log($text, 3, $LOG);
}

function error_handler($errno, $errstr) {
    if (!(error_reporting() & $errno)) {
        return;
    }
    $user = 'unknown';
    if (function_exists('posix_geteuid') && function_exists('posix_getpwuid')) {
        $info = @posix_getpwuid(posix_geteuid());
        if (is_array($info) && isset($info['name'])) {
            $user = $info['name'];
        }
    } elseif (getenv('USERNAME')) {
        $user = getenv('USERNAME');
    } else {
        $user = get_current_user() ?: 'unknown';
    }
    message("Error $errno: $errstr");
    message("User: $user");
}

if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

function clone_headers() {
    $skip = ['content-length', 'transfer-encoding', 'connection', 'host'];
    foreach (getallheaders() as $name => $value) {
	if (!$value || in_array(strtolower($name), $skip)) {
            continue;
        }
        header("$name: $value");
    }
}

function write_head_file($file) {
    $content = "";
    foreach (getallheaders() as $name => $value) {
        $content .= "$name: $value\n";
    }
    $content .= "\n";
    file_put_contents($file, $content);
    return true;
}

function write_data_file($file, $body) {
    if (empty($body)) {
        return false;
    }
    file_put_contents($file, $body);
    return true;
}

function write_peer_file($file) {
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'] ?? '';
    $scheme = $_SERVER['REQUEST_SCHEME'] ?? 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $request = $_SERVER['REQUEST_URI'] ?? '/';
    $url = "$scheme://$host$request";
    $content = "";
    $content .= "REMOTE: $remote\n";
    $content .= "METHOD: $method\n";
    $content .= "URL: $url\n";
    file_put_contents($file, $content);
    return true;
}

function kill($code, $text) {
    header("HTTP/1.1 $code");
    exit($text);
}

set_error_handler("error_handler");

set_exception_handler(function ($e) {
    message("Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    if (!headers_sent()) {
        header("HTTP/1.1 500 Internal Server Error");
    }
    exit("500 Internal Server Error");
});

register_shutdown_function(function () {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE], true)) {
        if (!headers_sent()) {
            header("HTTP/1.1 500 Internal Server Error");
        }
        echo "500 Internal Server Error";
    }
});

if (PHP_SAPI === 'cli-server') {
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $file = realpath(__DIR__ . $uri);
    if ($file !== false && $file !== realpath(__FILE__) && is_file($file)
        && strpos($file, __DIR__ . DIRECTORY_SEPARATOR) === 0
        && substr($file, -4) === '.php') {
        require $file;
        return;
    }
}

if (isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > $LIMIT) {
    http_response_code(500);
    exit("500 Internal Server Error: Request body size exceeds the limit");
}

$BODY = file_get_contents('php://input');

if (!is_dir($UPLOAD)) {
    message("Create directory $UPLOAD");
    if (!mkdir($UPLOAD)) {
        kill("500 Internal Server Error", "Failed to create upload directory");
    }
}

$YEAR_MONTH = date("Y-m");
$TODAY = date("Y-m-d");
$DIRECTORY = "$UPLOAD/$YEAR_MONTH/$TODAY";

if (!is_dir($DIRECTORY)) {
    message("Create directory $DIRECTORY");
    if (!mkdir($DIRECTORY, $PERMISSIONS, true)) {
        kill("500 Internal Server Error", "Failed to create upload directory");
    }
}

$RAND = date("Ymd_His_").rand(10000000, 99999999);

$FILE_HEAD = "$DIRECTORY/$RAND.head";
if (write_head_file($FILE_HEAD)) {
    message("File written: $FILE_HEAD");
}

$FILE_DATA = "$DIRECTORY/$RAND.data";
if (write_data_file($FILE_DATA, $BODY)) {
    message("File written: $FILE_DATA");
}

$FILE_PEER="$DIRECTORY/$RAND.peer";
if (write_peer_file($FILE_PEER)) {
    message("File written: $FILE_PEER");
}

restore_error_handler();

clone_headers();

echo $BODY;
