<?php

// Get the current timestamp with microseconds
list($microseconds, $seconds) = explode(' ', microtime());
$microseconds = str_pad(round($microseconds * 1000), 3, '0', STR_PAD_RIGHT);

// Format the current date and time
$currentDateTime = date("Y-m-d H:i:s", $seconds) . '.' . $microseconds;

die($currentDateTime);
