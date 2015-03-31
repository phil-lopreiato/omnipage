<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

/* module history AJAX support
*  version 0.1
*  developed by Phil Lopreiato
*  receives GET variables and returns module in edit state
*/

//include common
include "../includes/common.php";

mySQLConnect();

$pageId = $_GET["pageId"];
$instanceId = $_GET["instanceId"];

if(!userPermissions(1,$pageId))
exit;

switch($_GET['mode']){
case "showMod":
		$modId = $_GET["modId"];
		$mod = getModule($modId);
		$properties = getProps($_GET['modId']);
		echo $mod->render($properties);
		break;

	case "getEdits":
		echo getEditHistory($modId);
		break;

	case "getEditData":
		echo getEditInfo($modId, $_GET['id']);
		break;

	case "restoreEdit":
		echo restoreEdit($modId,$_GET['id']);
		break;

	case "pageHistory":
		echo getPageHistory($_GET['page']);
		break;
}
?>
