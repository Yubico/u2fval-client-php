<?php

/*
 * This page registers a U2F device for the logged in user.
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

//When this page is initially shown, this varable is empty and we begin the registration process.
if(empty($_POST['u2f-data'])) {

  /*
  * Start U2F registration
  */

  print_header($user);

  ?>
        <div class="row">
          <div class="col-md-4">
            <h2>Register U2F device</h2>
            <p>Insert a U2F device and touch the button, when it starts flashing.</p>
  <?php

  //Get U2F device registration data from the U2FVAL service:
  $u2f_data = $u2fval->register_begin(get_id($user));

  ?>
            <form id="u2f-form" method="post">
              <input id="u2f-data" type="hidden" name="u2f-data" />
            </form>

            <script>
            //Store the U2F request data as a JavaScript variable.
            var u2f_data = <?php echo $u2f_data; ?>;

            //Call the browsers U2F API.
            u2f.register(u2f_data.registerRequests, u2f_data.authenticateRequests, function(resp) {
              if(resp.errorCode) {
                alert("registration failed with error: " + resp.errorCode);
                return;
              }

              //Submit the U2F registration data to the server.
              $('#u2f-data').val(JSON.stringify(resp));
              $('#u2f-form').submit();
            });
            </script>
            <p><a href="devices.php">Back</a>.</p>
          </div>
        </div>
  <?php

  print_footer();

} else {  //When the above data is posted, we complete the registration.
  /*
   * Complete U2F registration
   */

  //Send the POSTed data to the U2FVAL service, and check the response.
  try {
    $u2fval->register_complete(get_id($user), $_POST['u2f-data']);
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
}

?>
