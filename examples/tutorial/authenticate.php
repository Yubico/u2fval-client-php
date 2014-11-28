<?php

require_once('users.inc.php');
require_once('template.inc.php');

$username = $_POST['username'];
$password = $_POST['password'];

//Validate username and password
if(validate_password($username, $password)) {
  log_in($username);
  header('Location: welcome.php');
  die();
} else {
  print_header();
?>
      <div class="row">
        <div class="col-md-4">
          <h2>Invalid username or password!</h2>
          <p>There are 3 available users in the system:</p>
          <dl>
            <dt>User 1</dt><dd>alice/pass1</dd>
            <dt>User 2</dt><dd>bob/pass2</dd>
            <dt>User 3</dt><dd>cesar/pass3</dd>
          </dl>
        </div>
      </div>

<?php
  print_footer();
}
?>
