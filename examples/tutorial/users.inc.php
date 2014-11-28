<?php

/*
 * User authentication fuctions for dealing with users and passwords. No U2F stuff here.
 */

session_start();

// Users with passwords
$users['alice'] = 'pass1';
$users['bob']   = 'pass2';
$users['cesar'] = 'pass3';

//Validate the users password
function validate_password($username, $password) {
  global $users;
  if(isset($users[$username]) && $users[$username] == $password) {
    return True;
  }
  return False;
}

//Log the user in
function log_in($username) {
  $_SESSION['user'] = $username;
}

//Log the user out
function log_out() {
  $_SESSION['user'] = null;
}

//Get the currently logged in user
function get_user() {
  return $_SESSION['user'];
}

//Get the ID of a user
function get_id($user) {
  //In a production system this could be a database ID. This value is used by
  //U2FVAL to identify the user, and must not change.
  //For the sake of privacy we might want to hash this value before sending it
  //to the U2FVAL server. For this demo we just use the username.
  return $user;
}

?>
