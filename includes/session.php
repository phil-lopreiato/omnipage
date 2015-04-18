<?PHP

include_once "user.php";

function get_logged_in(){
    session_start();
    if(isset($_SESSION['login_user']) && isset($_SESSION['login_session'])){
        global $mySQLLink;
        $str = "SELECT * FROM `users` WHERE `userName` = '".mysql_real_escape_string($_SESSION['login_user'])."' LIMIT 1";
        $query = mysql_query($str, $mySQLLink) or die(mysql_error());
        if($row = mysql_fetch_assoc($query)){
            /* Validate session hash */
            $testHash = make_session_hash($row['userName'], $row['passHash']);
            if($testHash == $_SESSION['login_session']){
                global $user;
                $user = new User($row);
                return $user;
            }
        }
    }
}

?>
