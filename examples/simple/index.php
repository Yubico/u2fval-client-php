<?php
require_once(dirname(__FILE__)."/../config.php");

if(empty($_GET["action"])) // Let the User choose an option and ID for testing purposes
{
    echo '
	<form action="index.php" method="get">
        <input type="text" name="username" placeholder="Username"><br>
        <select name="action">
            <option value="register">Register</option>
            <option value="login">Login</option>
        </select><br>
        <input type="submit">
    </form>';
    exit;
}
else // Register or Login
{
    $user = $_GET["username"];
    $isregister = ($_GET["action"] == "register");
    if(empty($_GET["data"])) // Ask user to press yubikey to register or login
    {
        $data = $isregister ? $u2fval->register_begin($user) : $u2fval->auth_begin($user);
        echo '
        <script src="u2f-api.js"></script>
        <script>
            var data = '.$data.';
            setTimeout(function() {
                console.log("init",data);
                ';
                
        if($isregister) echo 'u2f.register(data.registerRequests, data.authenticateRequests, function(resp) {';
        else echo 'u2f.sign(data.authenticateRequests, function(resp) {';
        
        echo '
                    console.log("callback",resp);
                    if(resp.errorCode) {
                        alert("registration failed with error: " + resp.errorCode);
                        return;
                    }
                    window.location = window.location.href + "&data=" + encodeURIComponent(JSON.stringify(resp));
                });
                alert("Touch Yubikey now (and create a gui for this app)");
              }, 1000);
        </script>
        Please wait...
        <br><br><a href="index.php">Back to Main</a>';
        exit;
    }
    else // Process yubikey data
    {
        try {
            $data = $isregister ? $u2fval->register_complete($user, $_GET['data']) : $u2fval->auth_complete($user, $_GET["data"]);
            echo '<h1>Success</h1>'.json_encode($data);
        } catch(U2fVal\U2fValException $exception) {
            echo '<h1>Error</h1>'.$exception->getMessage();
        }
        echo '<br><br><a href="index.php">Back to Main</a>';
        exit;
    }
}
?>