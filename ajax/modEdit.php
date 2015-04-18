<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

/* module editing AJAX support
*  version 0.1
*  developed by Matt Howard, Phil Lopreiato
*  receives POST variables and returns module in edit state
*  !** NEEDS SECURITY FEATURES **!
*/

//include common
include "../includes/common.php";

mySQLConnect();

$modId = $_GET["modId"];

// quit here if the user doesn't have edit permission on this page
if(!userPermissions(1,$pageId)){
    echo "No permission";
    exit;
}

switch($_GET['mode']){
	case "renderEdit":
		echo renderEdit($modId);
		break;

	case "saveMod":
		$mod = getModuleById($modId);
		$properties = array();
		foreach($_GET as $k => $v){
			if($k != 'mode')
			    $properties[$k] = $v;
		}
		$mod->edit($properties);
		logEntry("Edited mod id $modId on page $pageId");
		$properties = getProps($modId);
		echo $mod->render($properties);
		break;

	case "delete":
		deleteMod($pageId, $modId);
		logEntry("Deleted mod id $modId from pageId $pageId");
		break;

	case "showMod":
		$mod = getModuleById($modId);
		$properties = getProps($modId);
		echo $mod->render($properties);
		break;
}
?>
