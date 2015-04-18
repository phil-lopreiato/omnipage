<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

/* Security features
*  version 0.1
*  developed by Matt Howard, Phil Lopreiato
*/

function logEntry($entry){
    global $user,$root_path;
    $file = fopen("$root_path/logs/security.txt",'a');
    $entry = str_replace("\n","\\n",$entry);
    fwrite($file,$user->data["user_email"].":".$user->data["username_clean"].":".$user->data["user_ip"].":".date("D M j G:i:s T Y").":".$entry."\n");
    fclose($file);
}

function userPermissions($type,$pageId=""){
	global $user,$page, $mySQLLink;

    // if we need to, query for the currently logged in user
    if(!isset($user)){
        $user = get_logged_in();
    }

	//if pageId is not specified, use current one
	if($pageId == "")
		$pageId = $page->pageId;

	//admins start with full permission, everybody else does not
	$result = isset($user)?$user->is_admin():false;

	//Admin group has full permissions
	/* take out group for now
    if(($user->data["group_id"]==5))
	    $result = true;
    */

	$query = mysql_query("SELECT * FROM `pages` WHERE `pageId` = '".mysql_real_escape_string($pageId)."'", $mySQLLink) or die(mysql_error());
	$row = mysql_fetch_array($query);
	//if not private, user has read access
	if($row["private"] == "0" && $type == 0)
	    $result = true;

	//inherit
	if($row["inheritPermissions"] == "1" && userPermissions($type,$row["parentId"]))
	    $result = true;

	//check database for individual user permissions
    if(!isset($user))
        return $result;
	$query = mysql_query("SELECT * FROM `pagePermissions` WHERE `pageId` = '".mysql_real_escape_string($pageId)."' AND `userId` = '".mysql_real_escape_string($user->data['user_id'])."'", $mySQLLink);
	$row = mysql_fetch_array($query);

	if($row){
		if($row["access"]==0 && $type==0)
		    $result = true;
		if($row["access"]==1)
		    $result = true;
	}
	return $result;
}

function logEmail($subject,$contents){
	global $user,$root_path;
	$date = date("d-m-Y G:i:s");
	$entry = "<h1>Mass Email Sent on $date</h1><p><strong>Subject: </strong>$subject</p><br/><div>$contents</div>";
	$file = fopen("$root_path/logs/mass emails/".$date.".html",'a+');
	fwrite($file,$entry);
	fclose($file);
}
?>
