<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

/* module history support
*  version 0.1
*  developed by Matt Howard, Phil Lopreiato
*/


/* Function to set the modulePropes of a given module
 * This function will archive old edits into the history tables
 * ALL PROPERTY EDITS SHOULD USE THIS FUNCTION */
function setVariables($pageId, $modId, $variables){
	global $user,$mySQLLink;
    $pageId = mysql_real_escape_string($pageId);
    $modId = mysql_real_escape_string($modId);

    //put old edit data into database
	$propQuery = mysql_query("SELECT * FROM `moduleProps` WHERE `modId` = '$modId'",$mySQLLink) or die(mysql_error());
	while($propRow = mysql_fetch_array($propQuery)){
            // build array of old properties
			$oldProps[$propRow["propName"]]=$propRow["propValue"];
	}

    // create a new 'edit' event in editHistory table
	$string = "INSERT INTO editHistory (modId, time, ip, user) VALUES ('$modId','".time()."','".$_SERVER['REMOTE_ADDR']."','".$user->data["username_clean"]."')";
	mysql_query($string, $mySQLLink)or die(mysql_error());
    $editId = mysql_insert_id($mySQLLink);

    // insert the old properties into the modulePropsHistory table
	foreach($oldProps as $name => $value){
        // 'pageId' and 'modId' are immutable attributes, so don't update them
		if($name != "pageId" || $name != "modId"){
            $string = "INSERT INTO modulePropsHistory (editId, propName, propValue) VALUES ('$editId','$name','".stripslashes($value)."')";
			mysql_query($string, $mySQLLink)or die(mysql_error());
		}
	}

    // update the current properties to the newly specified values
	foreach ($variables as $key => $value){
		$exist = mysql_query("SELECT * FROM `moduleProps` WHERE `modId`= '$modId' AND `propName` = '$key'", $mySQLLink)or die(mysql_error());

        // if the property already exists then update it, else add it anew
		if(mysql_num_rows($exist) > 0){
			mysql_query("UPDATE `moduleProps` SET `propValue` = '$value' WHERE `modId` = '$modid' AND `propName` = '$key'", $mySQLLink)or die(mysql_error());
		}else{
			mysql_query("INSERT INTO `moduleProps` (modId, propName, propValue) VALUES ('$modId','$key','$value')", $mySQLLink)or die(mysql_error());
		}
	}

	return true;
}

/* Fetch the edit history for a module given a modId
 * Returns a html string showing the old properties/values
 */
function getEditHistory($modId){
    $modId = mysql_real_escape_string($modId);
	$output = "";
	$editQuery = mysql_query("SELECT * FROM `editHistory` WHERE `modId` = '$modId' ORDER BY editId ASC")or die(mysql_error());
	if(mysql_num_rows($editQuery)<1){
		$output .= "<p>$modId</p><p>This module has no edit history.</p>";
	}else{
		$output .= "<table style='width:100%' id='editHistory_$modId' name='editHistory_$modId'>";
		$output .= "<tr id='editHistory_".$modId."_headerRow' name='editHistory_".$modId."_headerRow' style='text-decoration:bold;'><td>Edit Time</td><td>User</td><td>IP</td><td>Show/Hide Edit Data</td></tr>";
		while($row = mysql_fetch_assoc($editQuery)){
			$output .= "<tr name='edit_".$row['editId']."' id='edit_".$row['editId']."'>";
			$output .= "<td name='edit_".$row['editId']."_time'>".(date('m\-d\-Y \a\t G\:i:s',$row['time']))."</td>";
			$output .= "<td name='edit_".$row['editId']."_user'>".$row['user']."</td>";
			$output .= "<td name='edit_".$row['editId']."_ip'>".$row['ip']."</td>";
			$output .= "<td name='edit_".$row['editId']."_select'><a href='javascript:void(0)' onclick='getEditData(".$pageId.",".$instanceId.",".$row['editId'].")'>View Edit Data</a></td>";
			$output .= "</tr>";
		}
		$output .= "</table><button name='return' id='return' onclick='showMod($modId)'>Return to Module</button>";
		$output .= "<div id='editData'></div>";
	}

	return $output;
}

/* Show info for a given edit id */
function getEditInfo($modId, $editId){
	$modId = mysql_real_escape_string($modId);
    $editId = mysql_real_escape_string($editId);
    $output = "";
	$q = mysql_query("SELECT * FROM `modulePropsHistory` WHERE editId = '$editId'")or die(mysql_error());
	$output .= "<table id='editData_".$editId."' name='editData_".$editId."' style='width:100%;'><tr style='text-decoration:bold;'><td>Property Name</td><td>Property Value</td></tr>";
	while($row = mysql_fetch_assoc($q)){
		$output .= "<tr><td style='vertical-align:text-top;'>".$row['propName']."</td><td><div style='overflow:auto;width:100%'>".htmlentities($row['propValue'])."</div></td></tr>";
	}
	$output .= "</table>";
	$mod = mysql_fetch_array(mysql_query("SELECT * FROM modules WHERE `modUID` = '$modId'"))or die(mysql_error());
	$output .= "<button name='revertButton' id='revertButton' onclick='revertEdit($modId, $editId)'>".($mod['deleted']==0?"Restore Module to this State":"Undelete Module to this State")."</button>";
	return $output;
}

/* Restore a module to a given edit state */
function restoreEdit($modId, $editId){
	global $mySQLLink;
    $modId = mysql_real_escape_string($modId);
    $editid = mysql_real_escape_string($editId);
    $out = "";

	$q = mysql_fetch_array(mysql_query("SELECT * FROM modules WHERE `modUID` = '$modId'", $mySQLLink))or die(mysql_error());
	if($q['deleted']==1){
		$res = mysql_query("UPDATE modules SET deleted = '0' WHERE `modUID` = '$modId'", $mySQLLink)or die(mysql_error());
	}

	$s = "SELECT * FROM `modulePropsHistory` WHERE `editId` = '".$editId."'";
	$q = mysql_query(mysql_real_escape_string($s),$mySQLLink)or die(mysql_error());
	$props = array();
	while($propRow = mysql_fetch_array($q,MYSQL_ASSOC)){
		$props[$propRow["propName"]]=$propRow["propValue"];
	}
	$update = setVariables($modId, $props);
	if($update){
	    logEntry("Reverted mod id $modId to edit state $editId");
	    $out .= "Sucessfully restored module";
	}else{
	    $out .= $update;
	}
	return $out;
}

/* Restore a page and all its modules */
function restorePage($page){
	global $mySQLLink;
    $page = mysql_real_escape_string($page);
	$out = "";
	//undelete page
	$page = mysql_query("UPDATE pages SET deleted = '0' WHERE pageId = '$pageId'", $mySQLLink)or die(mysql_error());

    //undelete page's modules
	$mod = mysql_query("UPDATE modules SET deleted = '0' WHERE pageId = '".$pageId."'", $mySQLLink)or die(mysql_error());

	if($page && $mod && $modProp){
		$out = "Sucessfully restored page with ID ".$pageId;
		logEntry("Restored page ID ".$pageId);
	}else{
		$out = "error restoring page";
	}
	return $out;
}

/* Show page history */
function pageHistory($page){
    global $mySQLLink;
    $page = mysql_real_escape_string($page);
	$q = mysql_query("SELECT * FROM modules WHERE pageId = '$page'", $mySQLLink);
	$out = "<ul>";
	while($row = mysql_fetch_array($q)){
		$out .= "<li><a href='javascript:void(0);' class='modHistoryLink' id='history_".$page."_".$row['modUID']."'>Module Id: ".$row['modUID']." ".($row['deleted']==1?"- deleted":"")."</a></li>";
	}
	$out .= "</ul>";

	$page = mysql_fetch_array(mysql_query("SELECT * FROM pages WHERE id = '$page'", $mySQLLink));
	if($page['deleted'] == 1){
		$out .= "<br/><p><b>Page Has Been Deleted";
		$out .= "<br/><button id='restorePage_".$page."_Button' class='restorePage'>Restore this Page</button></p>";
	}

	return $out;
}
?>
