<?PHP
/* File to handle logging in */

if($_SERVER["REQUEST_METHOD"] == "POST"){
    /* Somebody wants to log in */

    include "includes/common.php";
    include_once "includes/user.php";
    $salt = "salty";
    mySQLConnect();

    $user = $_POST['username'];
    $pass = $_POST['password'];
    $redirect = $_POST['redirect'];

    if(!empty($user) && !empty($pass)){
        global $mySQLLink;
        /* Validate the user */

        $str ="SELECT * FROM `users` WHERE `userName` = '". mysql_real_escape_string($user)."' LIMIT 1";
        $query = mysql_query($str, $mySQLLink) or die(mysql_error());
        if($row = mysql_fetch_assoc($query)){
            $testHash = hash("sha512", $user.$pass.$salt);
            if($testHash == $row['passHash']){
                $user = new User($row);
                $user->configure_session();

                if(empty($redirect)){
                    $redirect = "/o";
                }
                header("Location: ".$redirect);
                return;
            }
        }
    }
}

redirect_home();

function redirect_home(){
    header("Location: /o");
}
?>
