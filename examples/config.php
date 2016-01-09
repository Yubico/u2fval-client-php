<?php

/*
 * Configuration file for all examples.
 */

require_once(dirname(__FILE__).'/../vendor/autoload.php');

// U2FVAL Configuration - Using API token authentication:
$u2fval = U2fVal\Client::withApiToken(
    'https://u2fval.appspot.com/api',               // URL of the U2FVAL service.
    ''                                              // API token for use with service, fill this in!
);

// Alternate configurations available: See Client.php for more details.
//$u2fval = U2fVal\Client::withNoAuth();
//$u2fval = U2fVal\Client::withHttpAuth('username', 'password');

?>
