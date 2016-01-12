<?php

/*
 * Configuration file for all examples.
 */

// Using Composer
require_once(dirname(__FILE__).'/../vendor/autoload.php');
/* or by hand
require_once(dirname(__FILE__)."/../src/Client.php");
require_once(dirname(__FILE__)."/../src/Exceptions.php");
if(!function_exists("curl_init")) die("You require CURL for PHP");
*/

// U2FVAL Configuration - Using API token authentication:
$u2fval = U2fVal\Client::withApiToken(
    'https://u2fval.appspot.com/api',     // URL of the U2FVAL service
    ''                                    // API token for use with service, get it from u2fval.appspot.com and fill in here
);

// Alternate configurations available: See Client.php for more details
//$u2fval = U2fVal\Client::withNoAuth();
//$u2fval = U2fVal\Client::withHttpAuth('username', 'password');

?>
