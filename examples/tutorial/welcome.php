<?php

require_once('users.inc.php');
require_once('template.inc.php');

$user = get_user();
if(empty($user)) {
  header('Location: index.php');
  die();
}

print_header($user);

?>
      <div class="row">
        <div class="col-md-4">
          <h2>Logged in</h2>
          <p>Congratulations! You are logged in as <?php echo ($user); ?>.</p>
          <p><a href="index.php">Log out.</a></p>
        </div>
      </div>
<?php

print_footer();

?>
