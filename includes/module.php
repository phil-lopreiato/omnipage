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
	$query = mysql_query(mysql_real_escape_string("SELECT * FROM `modules` WHERE `pageId` = '$pageId' AND `deleted` = '0' ORDER BY `order` ASC"), $mySQLLink) or die(mysql_error());
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
	global $mySQLLink;

    // fetch the module from the db
	$query = mysql_query(mysql_real_escape_string("SELECT `modType` FROM `modules` WHERE `modUID` = '$modId'"), $mySQLLink) or die(mysql_error());
	$row = mysql_fetch_array($query);

    // get the module properties
	$properties = getProps($modId);

    // render the edit state
	return getModule($row["modType"])->renderEdit($properties);
}

// get properties for a given module
function getProps($modId){
	global $mySQLLink;
	$properties = array();

    // query the properties from the db and add them to our array
	$propQuery = mysql_query(mysql_real_escape_string("SELECT * FROM `moduleProps` WHERE `modId` = '$modid'"), $mySQLLink) or die(mysql_error());
	while($propRow = mysql_fetch_array($propQuery)){
		$properties[$propRow["propName"]]=$propRow["propValue"];
	}
	$properties["modId"]=$modId;
	return $properties;
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
			return new mod_bbcode();
			break;
		case 2:
			return new mod_like();
			break;
		case 3:
			return new mod_controlPanel();
			break;
		case 4:
			return new mod_sitemap();
			break;
		case 5:
			return new mod_calendar();
			break;
		case 6:
			return new mod_forumsActivity();
			break;
		case 7:
			return new mod_uploader();
			break;
		case 8:
			return new mod_gallery();
			break;
		case 9:
			return new mod_filetree();
			break;
		case 10:
			return new mod_news();
			break;
		case 11:
			return new mod_video();
			break;
		case 12:
			return new mod_simpleScript();
			break;
		case 13:
			return new mod_blog();
			break;
	}

	// if not valid modType
	return false;
}

// delete module (or all modules on a page)
function deleteMod($pageId, $modId=-1){
	mysql_query("UPDATE `modules` SET deleted = '1' WHERE `pageId` = '".mysql_real_escape_string($pageId)."' ".($modId==-1?"":("AND `modUID` = '".mysql_real_escape_string($modId)."'")))or die(mysql_error());
	mysql_query("UPDATE `moduleProps` SET deleted = '1' WHERE `pageId` = '".mysql_real_escape_string($pageId)."' ".($modId==-1?"":("AND `modUID` = '".mysql_real_escape_string($modId)."'")))or die(mysql_error());
}
?>
