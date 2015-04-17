<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

/* OmniCore
*  Version 0.1
*  Developed by Matt Howard, Phil Lopreiato
*  OmniCore
*/

// show php errors
error_reporting(E_ALL);
ini_set('display_errors',1);

//include 'common'.'Common' contains all necessary includes.
include "includes/common.php";

//connect to mySQL
mySQLConnect();

//get current user session
get_logged_in();

//get page info
$page = new url();
$page->init();

//user permissions
$editable = userPermissions(1);

if($page->error404 || !userPermissions(0)){
    echo "404";
	header("HTTP/1.0 404 Not Found");
	echo file_get_contents($domain."/omni/error.php?errorCode=404");
	exit;
}

$menu = drawMenu();

//render final output
$skinVars = array();
$skinVars["additionalHead"] = "";
$skinVars["mainColumn"] = renderModules($page->pageId);
$skinVars["secondColumn"] = renderModules(0);
$skinVars["title"] = $page->title;
$skinVars["currentPage"] = $page->url;
$skinVars["sessionId"] = isset($user)?$user->data["session_id"]:"";
$skinVars["pageId"] = $page->pageId;
$skinVars["topMenu"] = $menu[0];
$skinVars["secondMenu"] = $menu[1];
$skinVars["breadcrumbs"] = $page->breadCrumbs();

$options = array("ISHOME"=>($page->pageId==1));

//html tidy
// Specify configuration
$config = array(
           'indent'         => true,
		   'doctype'		=> "user",
           'wrap'           => 200);

$html = parseSkin($skinVars,"main",$options);

// Tidy
/*$tidy = new tidy;
$tidy->parseString($html, $config, 'utf8');
$tidy->cleanRepair(); */

echo $html;
?>
