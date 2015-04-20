<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************

   ******************************************************************************************
   * addModule.php                                                                          *
   * This file  adds a module on a given page												*
   * Developed by Matt Howard, Phil Lopreiato                                               *
   * Version 0.1																			*
   ******************************************************************************************/

//include common
include "../includes/common.php";

mySQLConnect();

if(!userPermissions(1,$_GET["pageId"]))
exit;

//add modules
if($_GET["mode"]=="add"){
//get mod object
$mod =  getModule($_GET["modId"]);

//create `modules` table row
$query = mysql_query("INSERT INTO `modules` (`pageId`,`modType`,`order`,`deleted`) VALUES ('".mysql_real_escape_string($_GET["pageId"])."','".mysql_real_escape_string($_GET["modId"])."','10','0')", $mySQLLink)or die(mysql_error());
$modId = mysql_insert_id($mySQLLink);
//last, make properties

$mod->setup();
for($i=0;$i<sizeof($mod->sqlNames);$i++){
	mysql_query("INSERT INTO `moduleProps` (`modId`,`propName`,`propValue`) VALUES ('$modId','".mysql_real_escape_string($mod->sqlNames[$i])."','".mysql_real_escape_string($mod->sqlDefaults[$i])."')") or die(mysql_error());
	}

logEntry("Added mod '".$mod->title."' in pageId '".$_GET["pageId"]."'");
}

//list all modules
if($_GET["mode"]=="list"){
	$output = "";
	for($i=0;$mod=getModule($i);$i++){
		$skinVars = array();
		$skinVars["modId"] = $i;
		$skinVars["path"] = $mod->path;
		$skinVars["title"] = $mod->title;
		$skinVars["desc"] = $mod->description;

		$output.=parseSkin($skinVars,"module_list_line");
	}
	echo parseSkin(array("output"=>$output),"module_list");
}
?>
