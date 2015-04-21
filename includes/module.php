<?PHP
/* module support
*  version 0.1
*  developed by Matt Howard,  Phil Lopreiato
*/


// When this file is included,  go through the modules directory and include all available
$dirHandle = opendir($root_path."/modules");
while($row = readdir($dirHandle)){
	if(file_exists($root_path."/modules/".$row."/mod.php")){
	    include $root_path."/modules/".$row."/mod.php";
    }
}

// render modules of given page ID
// returns html formatted string of rendered modules
function renderModules($pageId){
	global $mySQLLink;
    $pageId = mysql_real_escape_string($pageId);
	$query = mysql_query("SELECT * FROM `modules` WHERE `pageId` = $pageId AND `deleted` = 0 ORDER BY `order` ASC", $mySQLLink) or die(mysql_error());
	$output = "";
	while ($row = mysql_fetch_array($query)){
        // for each module on the page...

        // fetch this module's properties
		$properties = getProps($row["modUID"]);

        // render this module type with its properties
		$module = getModule($row["modType"])->render($properties);

        // insert the html into the basic module skin
		$output .= parseSkin(array("content"=>$module, "pageId"=>$pageId, "modId"=>$row["modUID"]), "basic_module");
		}
	return $output;
}

// render edit state of module, given its UID
function renderEdit($modId){
	global $mySQLLink, $editable;
    $modId = mysql_real_escape_string($modId);

    // fetch the module from the db
	$query = mysql_query("SELECT `modType`,`pageId` FROM `modules` WHERE `modUID` = '$modId'", $mySQLLink) or die(mysql_error());
	$row = mysql_fetch_array($query);

    // get the module properties
	$properties = getProps($modId);
    $editable = userPermissions(1, $row['pageId']);

    // render the edit state
	return getModule($row["modType"])->renderEdit($properties);
}

// get properties for a given module
function getProps($modId){
	global $mySQLLink;
    $modId = mysql_real_escape_string($modId);
	$properties = array();

    // query the properties from the db and add them to our array
	$propQuery = mysql_query("SELECT * FROM `moduleProps` WHERE `modId` = '$modId'", $mySQLLink) or die(mysql_error());
	while($propRow = mysql_fetch_array($propQuery)){
		$properties[$propRow["propName"]]=$propRow["propValue"];
	}

    $modQuery = mysql_query("SELECT `pageId` from `modules` WHERE `modUID` = '".$modId."'") or die(mysql_error());
    if($mod = mysql_fetch_assoc($modQuery)){
        $properties["pageId"]=$mod['pageId'];
    }

	$properties["modId"]=$modId;
	return $properties;
}

function getModuleById($modId){
    global $mySQLLink;
    $query = mysql_query("SELECT `modType` from `modules` WHERE `modUID` = '".mysql_real_escape_string($modId)."'") or die(mysql_error());
    if($row = mysql_fetch_assoc($query)){
        return getModule($row['modType']);
    }
    return false;
}

/* Get a new module object for a given mod id
 * Modules are imported at the very top dynamically,
 * so files just have to be placed in /omni/modules
 * New modules need to be assigned a new ID and added to this switch statement
 * TODO make this DB-backed, so modules can be dynamically added and enabled
 */
function getModule($modType){
	switch($modType){
		case 0:
			return new mod_HTML();
			break;
		case 1:
			return new mod_controlPanel();
			break;
		case 2:
			return new mod_sitemap();
			break;
		case 3:
			return new mod_calendar();
			break;
		case 4:
			return new mod_simpleScript();
			break;
	}

	// if not valid modType
	return false;
}

// delete module
function deleteMod($modId){
	mysql_query("UPDATE `modules` SET deleted = '1' WHERE `modUID` = '".mysql_real_escape_string($modId)."'")or die(mysql_error());
}
?>
