<?php

file_exists('config.php') && require_once 'config.php';

$UPLOAD = @$GLOBALS['UPLOAD'] ?: '/home/upload';
$PERMISSIONS = @$GLOBALS['PERMISSIONS'] ?: 0777;
$LIMIT = @$GLOBALS['LIMIT'] ?: 32768;

$LOG = @$GLOBALS['LOG'] ?: ini_get("error_log") ?: '/var/log/php/error.log';

if ($_SERVER['CONTENT_LENGTH'] > $LIMIT) {
    http_response_code(500);
    exit("500 Internal Server Error: Request body size exceeds the limit");
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
	$user = posix_getpwuid(posix_geteuid())['name'];
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
	$ignore = array(
		'content-length',
		'host',
		'accept',
		'content-encoding',
		'transfer-encoding',
	);
	foreach (getallheaders() as $name => $value) {
		if (in_array(strtolower($name), $ignore)) {
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
	$remote = $_SERVER['REMOTE_ADDR'];
	$method = $_SERVER['REQUEST_METHOD'];
	$url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
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

$BODY = file_get_contents('php://input');

if (!is_dir($UPLOAD)) {
	message("Create directory $UPLOAD");
	if (!mkdir($UPLOAD)) {
		kill("500 Internal Server Error", "Failed to create upload directory");
	}
}

$YEAR_MONTH=date("Y-m");
$TODAY=date("Y-m-d");
$DIRECTORY="$UPLOAD/$YEAR_MONTH/$TODAY";

if (!is_dir($DIRECTORY)) {
	message("Create directory $DIRECTORY");
	if (!mkdir($DIRECTORY, $PERMISSIONS, true)) {
		kill("500 Internal Server Error", "Failed to create upload directory");
	}
}

$RAND=date("Ymd_His_").rand(10000000, 99999999);

$FILE_HEAD="$DIRECTORY/$RAND.head";
if (write_head_file($FILE_HEAD)) {
	message("File written: $FILE_HEAD");
}

$FILE_DATA="$DIRECTORY/$RAND.data";
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
