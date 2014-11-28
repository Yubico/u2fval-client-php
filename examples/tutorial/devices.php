<?php

/*
 * This page shows the user his/her registered U2F devices.
 *
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

print_header($user);

?>
      <div class="row">
        <div class="col-md-4">
          <h2>U2F devices</h2>
<?php

//Get a list of the users U2F devices from the U2FVAL service.
$devices = $u2fval->list_devices(get_id($user));
if(empty($devices)) {
  echo "<p>You have no devices registered.</p>";
} else {
  foreach($devices as $device) {
    //Prints metadata about each device, and when it was registered and last used.
    $metadata = $device['metadata'];
    echo "<div class=\"well well-sm\">";
    echo "<h3>".$metadata['device']['displayName']."</h3>";
    if(!empty($metadata['device']['imageUrl'])) {
      echo "<img src=\"".$metadata['device']['imageUrl']."\" width=\"80\" height=\"80\" /><br/>";
    }
    echo "<strong>Registered: ".date("H:i D M j", strtotime($device['created']))."</strong><br/>";
    echo "<strong>Last used: ".date("H:i D M j", strtotime($device['lastUsed']))."</strong><br/>";
    echo "<a href=\"unregister-device.php?handle=".$device['handle']."\">Unregister</a>";
    echo "</div>";
  }
}

?>
          <p><a href="register-device.php">Register a device</a>.</p>
          <p><a href="welcome.php">Back</a>.</p>
        </div>
      </div>
<?php

print_footer();

?>
