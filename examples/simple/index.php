<?php

/* Copyright (c) 2014 Yubico AB
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are
 * met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above
 *     copyright notice, this list of conditions and the following
 *     disclaimer in the documentation and/or other materials provided
 *     with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

require_once('../config.php');

?>

<html>
<head>
<title>PHP U2F example</title>

<script src="chrome-extension://pfboblefjcgdjicmnffhdgionmgcdmne/u2f-api.js"></script>

<script>
<?php

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  if(!$_POST['username']) {
    echo "alert('no username provided!');";
  } else if(!isset($_POST['action']) && !isset($_POST['register2']) && !isset($_POST['authenticate2'])) {
    echo "alert('no action provided!');";
  } else {
    $user = $_POST['username'];
    echo "var username = '" . $user . "';";

    if(isset($_POST['action'])) {
      if($_POST['action'] === 'register') {
        $data = $u2fval->register_begin($user);
        if(U2fVal\is_error($data)) {
          echo "alert('error: " . $data['errorMessage'] . "');";
        } else {
          echo "var data = " . $data . ";";
?>
          setTimeout(function() {
            console.log("Data: ", data);
            console.log("Register Requests: ", data.registerRequests);
            console.log("Authenticate Requests: ", data.authenticateRequests);
            u2f.register(data.registerRequests, data.authenticateRequests, function(resp) {
              var form = document.getElementById('form');
              var reg = document.getElementById('register2');
              var user = document.getElementById('username');
              console.log("Register callback", resp);
              if(resp.errorCode) {
                  alert("registration failed with error: " + resp.errorCode);
                  return;
              }
              reg.value = JSON.stringify(resp);
              user.value = username;
              form.submit();
            });
        }, 1000);
<?php
        }
      } else if($_POST['action'] === 'authenticate') {
        $data = $u2fval->auth_begin($user);
        if(U2fVal\is_error($data)) {
          echo "alert('error: " . $data['errorMessage'] . "');";
        } else {
          echo "var data = " . $data . ";";
?>
          setTimeout(function() {
            console.log("Data: ", data);
            console.log("Authenticate Requests: ", data.authenticateRequests);
            u2f.sign(data.authenticateRequests, function(resp) {
              var form = document.getElementById('form');
              var auth = document.getElementById('authenticate2');
              var user = document.getElementById('username');
              console.log("Authenticate callback", resp);
              auth.value = JSON.stringify(resp);
              user.value = username;
              form.submit();
            });
          }, 1000);
<?php
        }
      }
    } else if($_POST['register2']) {
      $data = $u2fval->register_complete($user, $_POST['register2']);
      if(U2fVal\is_error($data)) {
        echo "alert('error: " . $data['errorMessage'] . "');";
      } else {
        echo "alert('success: " . json_encode($data) . "');";
      }
    } else if($_POST['authenticate2']) {
      $data = $u2fval->auth_complete($user, $_POST['authenticate2']);
      if(U2fVal\is_error($data)) {
        echo "alert('error: " . $data['errorMessage'] . "');";
      } else {
        echo "alert('success: " . json_encode($data) . "');";
      }
    }
  }
}
?>
</script>
</head>
<body>

<form method="POST" id="form">
username: <input name="username" id="username" value="<?php echo $_POST['username']; ?>" /><br/>
register: <input value="register" name="action" type="radio"/><br/>
authenticate: <input value="authenticate" name="action" type="radio"/><br/>
<input type="hidden" name="register2" id="register2"/>
<input type="hidden" name="authenticate2" id="authenticate2"/>
<button type="submit">Submit!</button>
</form>

</body>
</html>
