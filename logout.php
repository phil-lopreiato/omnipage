<?PHP

if($_SERVER['REQUEST_METHOD'] == "POST"){
    include "includes/common.php";
    mySQLConnect();
    get_logged_in();

    global $user;

    if(isset($user)){
        $user->end_session();

        $redirect = $_POST['redirect'];
        if(empty($redirect)){
            $redirect = "/o";
        }
        header("Location: ".$redirect);
        return;
    }
}

header('Location: /o');

?>
