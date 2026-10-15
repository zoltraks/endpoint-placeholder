<?php

// Upload directory location
// Relative paths are resolved against the app directory
// If not specified or empty then /home/upload is used when it exists,
// or the upload directory next to app otherwise
$GLOBALS['UPLOAD'] = '../upload';

// Upload directory permissions
$GLOBALS['PERMISSIONS'] = 0777;

// Output log file
// If not specified or empty then the error_log setting from php.ini is used,
// then /var/log/php/error.log when that directory exists,
// or log/php/error.log next to the app directory otherwise
$GLOBALS['LOG'] = '../log/php/error.log';

// Request size limit
$GLOBALS['LIMIT'] = 1048576;

// Enable phpinfo.php endpoint
$GLOBALS['PHPINFO'] = 0;
