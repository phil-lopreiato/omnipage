<?PHP

// Returns json-encoded info for given page id

if($_SERVER["REQUEST_METHOD"] != "POST"){
    die("No permission");
}

include "../includes/common.php";
mySQLConnect();
get_logged_in();

$pageId = mysql_real_escape_string($_POST['pageId']);

if(!is_numeric($pageId)){
    die("No permission");
}

if(!userPermissions(1, $pageId)){
    die("No permission");
}

$query = mysql_query("SELECT * FROM `pages` WHERE `pageId` = '$pageId LIMIT 1'")or die(mysql_error());
$row = mysql_fetch_assoc($query);

if($row){
    echo json_encode($row);
}else{
    echo "{}";
}
?>
