<?PHP

include_once "common.php";

/**
 * Add a skin to the SQL database
 * Precondition: mySQLConnect() has been called
 */
function addSkin($name, $path, $parent){
    get_logged_in();
    global $mySQLLink, $user;

    if(!$user->is_admin()) return "User is not admin";

    $name = mysql_real_escape_string($name);
    $path = mysql_real_escape_string($path);
    $parent = mysql_real_escape_string($parent);

    $str = "INSERT INTO `skins` (`name`, `path`, `parent`) VALUES ('$name', '$path', '$parent')";
    $query = mysql_query($str);
    if($query){
        logEntry("Added skin '$name'");
    }
    return $query?"Skin added successfully":mysql_error();
}

/**
 * Adds a page to the db
 * Precondition: mySQLConnect() has been called
 */
function addPage($parentId, $title, $redirect, $order, $inherentPermissions, $private, $hide){
    get_logged_in();
    global $mySQLLink, $user;
    if(!$user->is_admin()) return "User is not admin";

    $parentId = mysql_real_escape_string($parentId);
    $title = mysql_real_escape_string($title);
    $order = mysql_real_escape_string($order);
    $inherentPermissions = mysql_real_escape_string($inherentPermissions);
    $private = mysql_real_escape_string($private);
    $hide = mysql_real_escape_string($hide);
    $redirect = mysql_real_escape_string($redirect);

    if(!is_numeric($parentId) || $parentId < 0) return "Invalid parent id";
    if(!checkTitle($title)) return "Invalid title";
    if(!is_numeric($order)) return "Invalid menu order";

    $str = "INSERT INTO `pages` (`parentId`, `title`, `order`, `inheritPermissions`, `private`, `hide`, `redirect`)".
        "VALUES ('$parentId', '$title', '$order', '$inherentPermissions', '$private', '$hide', '$redirect')";
    $query = mysql_query($str);

    if($query){
        logEntry("Added page '$title' with parent id '$parentId'");
    }
    return $query?"Page added successfully":mysql_error();
}

/**
 * Updates a page in the db
 * Precondition: mySQLConnect() has been called
 */
function updatePage($pageId, $parentId, $title, $redirect, $order, $inherentPermissions, $private, $hide){
    get_logged_in();
    global $mySQLLink, $user;
    if(!$user->is_admin()) return "User is not admin";

    $pageId = mysql_real_escape_string($pageId);
    $parentId = mysql_real_escape_string($parentId);
    $title = mysql_real_escape_string($title);
    $order = mysql_real_escape_string($order);
    $inherentPermissions = mysql_real_escape_string($inherentPermissions);
    $private = mysql_real_escape_string($private);
    $hide = mysql_real_escape_string($hide);
    $redirect = mysql_real_escape_string($redirect);

    if(!is_numeric($pageId) || $pageId < 0) return "Invalid page";
    if(!is_numeric($parentId) || $parentId < 0) return "Invalid parent id";
    if(!checkTitle($title)) return "Invalid title";
    if(!is_numeric($order)) return "Invalid menu order";

    $str = "UPDATE `pages` SET `parentId` = '$parentId', `title` = '$title', `order` = '$order', ".
            "`inheritPermissions` = '$inherentPermissions', `private` = '$private', `hide` = '$hide', `redirect` = '$redirect' ".
            "WHERE `pageId`='$pageId'";
    $query = mysql_query($str);

    if($query){
        logEntry("Updated page '$pageId'");
    }
    return $query?"Page updated successfully":mysql_error();
}

function checkTitle($title){
	return preg_match("%\A[A-Za-z0-9\s]{1,20}\Z%",$title);
}


?>
