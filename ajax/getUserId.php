<?PHP

if($_SERVER["REQUEST_METHOD"] != "POST"){
    die("No permission");
}

include "../includes/common.php";
mySQLConnect();
get_logged_in();

global $user;
if(!isset($user) || !$user->is_admin()){
    die("No permission");
}

$username = mysql_real_escape_string($_POST['username']);
$query = mysql_query("SELECT `userId` FROM `users` WHERE `userName` = '$username' LIMIT 1") or die(mysql_error());
$row = mysql_fetch_assoc($query);

if($row){
    echo $row['userId'];
}else{
    echo -1;
}

?>
