<?php

require_once('users.inc.php');
require_once('template.inc.php');

//Log out when this page is loaded.
log_out();

print_header();

//Validate username and password
?>
      <div class="row">
        <div class="col-md-4">
          <h2>Getting started</h2>
          <p>We start out with a basic non-U2F site. Try logging in using the form at the top. There are 3 available users in the system:</p>
          <dl>
            <dt>User 1</dt><dd>alice/pass1</dd>
            <dt>User 2</dt><dd>bob/pass2</dd>
            <dt>User 3</dt><dd>cesar/pass3</dd>
          </dl>
        </div>
      </div>
<?php

print_footer();

?>
