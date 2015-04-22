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

/**
 * Deletes a page
 * Precondition: mySQLConnect() has been called
*/
function deletePage($pageId){
    global $mySQLLink;
    $pageId = mysql_real_escape_string($pageId);

    if(!is_numeric($pageId) || $pageId < 0) return "Invalid page";

    $str = "UPDATE `pages` SET `deleted` = '1' WHERE `pageId` = '$pageId'";
    $query = mysql_query($str, $mySQLLink);
    if($query){
        deleteModsInPage($pageId);
        logEntry("Deleted page id '$pageId'");
    }
    return $query?"Page deleted":mysql_error();
}

/**
 * Creates a new user
 * Precondition: mySQLConnect() has been called
 */
function addUser($username, $pass, $email, $isAdmin){
    global $mySQLLink;
    $username = mysql_real_escape_string($username);
    $pass = mysql_real_escape_string($pass);
    $email = mysql_real_escape_string($email);
    $isAdmin = mysql_real_escape_string($isAdmin);

    $passHash = make_main_hash($username, $pass);

    $str =  "INSERT INTO `users` (`userName`, `passHash`, `email`, `admin`)".
            "VALUES ('$username', '$passHash', '$email', '$isAdmin')";
    $query = mysql_query($str, $mySQLLink);

    if($query){
        logEntry("Created user '$username'");
    }
    return $query?"User created":mysql_error();
}

function addUserPermissions($userId, $pageId, $permissionType){
    global $mySQLLink;
    $userId = mysql_real_escape_string($userId);
    $pageId = mysql_real_escape_string($pageId);
    $type = mysql_real_escape_string($permissionType);

    $str = "INSERT INTO `pagePermissions` (`userId`, `pageId`, `type`) ".
            "VALUES ('$userId', '$pageId', '$type')";
    $query = mysql_query($str, $mySQLLink);

    if($query){
        logEntry("Added type $type permissions for $userId on $pageId");
    }
    return $query?"Permissions added":mysql_error();
}

function editUserPermissions($userId, $pageId, $permissionType){
    global $mySQLLink;
    $userId = mysql_real_escape_string($userId);
    $pageId = mysql_real_escape_string($pageId);
    $type = mysql_real_escape_string($permissionType);

    $str = "UPDATE `pagePermissions` SET `type` = '$type' ".
            "WHERE `userId` = '$userId' AND `pageId` = '$pageId'";
    $query = mysql_query($str, $mySQLLink);

    if($query && mysql_affected_rows($mySQLLink) > 0){
        logEntry("Updated type $type permissions for $userId on $pageId");
        return "Permissions updated";
    }else if($query){
        return "Permission doesn't exist: ";
    }else{
        return mysql_error();
    }
}

function removeUserPermissions($userId, $pageId){
    global $mySQLLink;
    $userId = mysql_real_escape_string($userId);
    $pageId = mysql_real_escape_string($pageId);

    $str = "DELETE FROM `pagePermissions` WHERE `userId` = '$userId' AND `pageId` = '$pageId'";
    $query = mysql_query($str, $mySQLLink);

    if($query){
        logEntry("Removed permissions for $userId on $pageId");
    }
    return $query?"Permissions removed":mysql_error();
}
function checkTitle($title){
	return preg_match("%\A[A-Za-z0-9\s]{1,20}\Z%",$title);
}

?>
