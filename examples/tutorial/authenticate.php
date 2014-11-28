<?php

require_once('users.inc.php');
require_once('template.inc.php');

require_once('../config.php');

//When this page is initially shown, this varable is empty and we begin the U2F authentication.
if(empty($_POST['u2f-data'])) {

  $username = $_POST['username'];
  $password = $_POST['password'];

  //Validate username and password
  if(validate_password($username, $password)) {  //Username/password are correct
    /*
     * Start U2F authentication:
     */
    try {
      $u2f_data = $u2fval->auth_begin(get_id($username));

      //Store password authenticated username in session:
      $_SESSION['username'] = $username;

      print_header($username);
?>
      <div class="row">
        <div class="col-md-4">
          <h2>U2F authentication</h2>
          <p>Please insert your U2F device now. Once it starts blinking, touch the button.</p>
          <form method="post" id="u2f-form">
            <input type="hidden" name="u2f-data" id="u2f-data" />
          </form>
        </div>
      </div>

      <script>
      //Set $u2f_data to a JavaScript variable:
      var u2f_data = <?php echo $u2f_data; ?>;
      //Call the U2F browser API:
      u2f.sign(u2f_data.authenticateRequests, function(resp) {
        $('#u2f-data').val(JSON.stringify(resp));
        $('#u2f-form').submit();
      });
      </script>

<?php
      print_footer();

    } catch(U2fVal\NoEligableDevicesException $exception) {
      if($exception->hasDevices()) {  //All the users devices have been compromised, the user needs to contact an administator.
        print_header();
?>
        <div class="row">
          <div class="col-md-4">
            <h2>Error</h2>
            <p><?php echo $exception->getMessage(); ?></p>
            <p><a href="index.php">Back</a>.</p>
          </div>
        </div>
<?php
        print_footer();
      } else {  //The user has no U2F devices registered, skip U2F
        log_in($username);
        header('Location: welcome.php');
      }
      die();
    }

  } else {  //Username/password are NOT correct!
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

} else {  //When the above data is posted, we complete the authentication.
  /*
   * Complete U2F authentication
   */

  //Grab password authenticated user from session:
  $username = $_SESSION['username'];

  //Send the POSTed data to the U2FVAL service, and check the response.
  try {
    $u2fval->auth_complete(get_id($username), $_POST['u2f-data']);
    
    //Success! Mark user as logged in and redirect to welcome.php!
    log_in($username);
    header('Location: welcome.php');
    die();
  } catch(U2fVal\U2fValException $exception) {
    print_header();
?>
        <div class="row">
          <div class="col-md-4">
            <h2>Error</h2>
            <p><?php echo $exception->getMessage(); ?></p>
            <p><a href="index.php">Back</a>.</p>
          </div>
        </div>
<?php
    print_footer();
  }
}
?>
