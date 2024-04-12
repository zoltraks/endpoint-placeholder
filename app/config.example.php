<?php

// Upload directory location
$GLOBALS['UPLOAD'] = '../upload';

// Upload directory permissions
$GLOBALS['PERMISSIONS'] = 0777;

// Output log file
// If not specified or empty then /var/log/php/error.log will be used
// unless error_log parameter is set in php.ini
$GLOBALS['LOG'] = '../log/php/error.log';

// Request size limit
$GLOBALS['LIMIT'] = 1048576;

// Enable phpinfo.php endpoint
$GLOBALS['PHPINFO'] = 0;
