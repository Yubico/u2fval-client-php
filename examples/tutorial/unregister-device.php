<?php

/*
 * This page unregisters a U2F device.
 */

require_once('users.inc.php');
require_once('template.inc.php');

require_once('../config.php');

//Ensure that the user is logged in.
$user = get_user();
if(empty($user)) {
  header('Location: index.php');
  die();
}

//Unregister the device with a call to the U2FVAL service.
try {
  $u2fval->unregister(get_id($user), $_GET['handle']);
  header('Location: devices.php');
  die();
} catch(U2fVal\U2fValException $exception) {
  print_header();
?>
        <div class="row">
          <div class="col-md-4">
            <h2>Error</h2>
            <p><?php echo $exception->getMessage(); ?></p>
            <p><a href="devices.php">Back</a>.</p>
          </div>
        </div>
<?php
  print_footer();
}
?>
