<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************

   ******************************************************************************************
   * Control Panel Module                                                                   *
   * Allows for administrative control of stuff on the site                                 *
   * version 0.1                                                                            *
   * Developed by Matt Howard, Phil Lopreiato                                               *
   ******************************************************************************************/


class mod_controlPanel {

	public $title = 'Control Panel';
	public $description = 'Allows administrators to change stuff on the site';
	public $path = 'mod_controlPanel';

	private $selectOutput = "";

	public function render($properties) {


        if(!userPermissions(1)){
            return "This is an unauthorized action. The control panel can only be viewed by an administrator and on the appropriate page.";
        }

		global $page, $root_path;
        include $root_path."/includes/admin.php";

        if(!isset($_POST['mode'])) $_POST['mode'] = "";

		//Perform administrative action
		switch($_POST['mode']){

			//add skin in SQL
			case "addSkin":
				$message = addSkin($_POST['skinName'], $_POST['skinPath'], $_POST['skinParent']);
                break;

			//add page in SQL
			case "addPage":
                $message = addPage( $_POST['parent'],
                                    $_POST['pageTitle'],
                                    $_POST['redirect'],
                                    $_POST['menuOrder'],
                                    isset($_POST['inherentBox'])?1:0,
                                    isset($_POST['privateBox'])?1:0,
                                    isset($_POST['hideBox'])?1:0
                                   );
                break;

			//delete a page in SQL
			case "delPage":
				//check that page has been selected
				if($_POST['page'] == '-1'){
					$message = "Please select a page to delete";
				}else{
					//check confirmation (not really needed, javascript does this already)
					if($_POST['confirmDel']=="on"){
						$name = mysql_query("SELECT * FROM `pages` WHERE `id` = '".mysql_real_escape_string($_POST['page'])."'",$GLOBALS["mySQLLink"]);
						$row = mysql_fetch_array($name);
						//delete pages in SQL
						$query = mysql_query("UPDATE `pages` SET `deleted` = '1' WHERE `id` = '".mysql_real_escape_string($_POST['page'])."'",$GLOBALS["mySQLLink"]);
						//delete modules assosciated with that page
						deleteMod($_POST['page']);
						//log the deletion
						logEntry("deleted page id ".$_POST['page']." titled ".$row["title"]);
						$message = $query?"Deleted sucessfully!":mysql_error();
						}
						else{
						$message="You need to confirm the deletion.";
						}
				}
			break;

			//modify a page in SQL
			case "editPage":
                $message = updatePage(  $_POST['pageEditSelect'],
                                        $_POST['newParent'],
                                        $_POST['newTitle'],
                                        $_POST['newRedirect'],
                                        $_POST['newMenuOrder'],
                                        isset($_POST['newInherent'])?1:0,
                                        isset($_POST['newPrivate'])?1:0,
                                        isset($_POST['newHide'])?1:0
                                     );
    			break;

			//special user permissions
			case "specialPermission":
				//set vars
				$type = $_POST['permissions'];
				$user = $_POST['userSelect'];
				$page = $_POST['permissionPage'];
				$message = "";

				//check if username is entered
				if($_POST['userSelect'] == ""){
					$message .= "Please enter a username.";
				}else{
					//check if user has entered a page
					if($_POST['permissionPage'] != -1){
						//check that permission type has been selected and type is not delete
						if($_POST['permissionType' == -1] && $_POST['permissionType'] != "delPermission"){
							$message .= "Please specify a permission type.";
						}else{

							//set more vars
							$user = strtolower($_POST['userSelect']);
							$user = mysql_real_escape_string($user);
							$pageId = mysql_real_escape_string($_POST['permissionPage']);
							$permissionType = mysql_real_escape_string($_POST['permissionType']);

									//this switch statement selects between the three types (add, modify, delete)
									switch($type){
										//default: nothing selected
										default:
											$message .= "Please specify add, edit, or delete permissions.";
											break;

										//add permissions
										case "addPermission":
											//check if permissions already exist
											$test = mysql_query("SELECT * FROM `pagePermissions` WHERE `username` = '".$user."' AND `pageId` = '".$pageId."'",$GLOBALS["mySQLLink"]);
											if(mysql_fetch_array($test)){
												$message .= "These user permissions already exist!";
											}else{
												global $db;
												//select user id from phpBB table
												$query = $db->sql_query("SELECT * FROM `phpbb_users` WHERE `username_clean` = '".$user."'",$GLOBALS["mySQLLink"]);

												$array = $db->sql_fetchrow($query);
												if(!$array)
													$message .= "The specified user does not exist. Please try again.";
												else{
												$userId = $array['user_id'];

												//insert permissions into omni database
												$insert = mysql_query("INSERT INTO  `uberbots_omni`.`pagePermissions` (`userId` ,`username` ,`pageId` ,`type`)VALUES ('".$userId."', '".$user."' , '".$pageId."', '".$permissionType."');",$GLOBALS["mySQLLink"]) or die(mysql_error());
												$message .= $insert?"Permission addition sucessful":mysql_error();
												//add log entry for adding permissions
												logEntry("Added Special permissions for User ".$user." on page with ID of ".$pageId." with permissions type ".$permissionType."");
											}}
											break;

										//edit permissions
										case "editPermission":
											//update permissions in SQL
											$update = mysql_query("UPDATE pagePermissions SET type = '".$permissionType."' WHERE username='".$user."' AND pageId='".$permissionType."'",$GLOBALS["mySQLLink"]);
											$message .= $update?"Permissions updated sucessfully.":mysql_error();
											//add log entry
											logEntry("Updated permissions for user ".$user." on page ID ".$pageId." to type ".$permissionType."");
											break;

										//delete permissions
										case "delPermission":
											//delete permissions from SQL
											$del = mysql_query("DELETE FROM `pagePermissions` WHERE `username` = '".$user."' AND `pageId` = '".$pageId."'",$GLOBALS["mySQLLink"]);
											$message .= $del?"Permissions deleted sucessfully.":mysql_error();
											//add log entry
											logEntry("Delted permissions for user ".$user." on page ID ".$pageId."");
											break;

									}


							}
						}else{
							$message .= "Please specify a page for special permissions to be applied.";
					}
				}
			break;
		}


		$log = str_replace("\n","<p>",file_get_contents("$root_path/logs/security.txt"));

		$this->selectOutput .= $this->listChildren(0,1);
		$permissionOutput = $this->showPermissions();
		$oldLogs = $this->oldLogs();
		$this->allPages .= $this->fullChildren(0,1);
		$skins = $this->getSkins();

	    $params = array('skins'=>$skins,"fullChildren"=>$this->allPages,"children"=>$this->selectOutput,"permissions"=>$permissionOutput, "message"=>isset($message)?$message:"","log"=>$log, "oldLogs"=>$oldLogs);
    	return parseSkin($params,'controlPanel',array("MESSAGE"=>isset($message)));

	}

	public function renderEdit($properties) {
		return "This module has no editable properties.";
	}

	public function edit($properties) {
	}

	var $sqlNames, $sqlDefaults;

	public function setup() {
		$this->sqlNames = array();
		$this->sqlDefaults = array();
	}

	private function getSkins(){
		$out = "";
		$query = mysql_query("SELECT * FROM `skins`",$GLOBALS["mySQLLink"]);
		while($row = mysql_fetch_array($query)){
			$out .= "<option value=".$row['skinId'].(($row['skinId']==0)?" SELECTED":"").">".$row['name']."</option>";
		}
		return $out;
	}

	private function oldLogs(){
		$output = "<div id=\"logList\" name=\"logList\">";
			$output .= "Old Logs:\n";
			$dir = "/home1/uberbots/public_html/omni/logs/";
			if (is_dir($dir)) {
				if($handle = opendir($dir)){
				$output .= "<br><ul>";
				while (false != ($file = readdir($handle))) {
					if ($file != "." && $file != "..") {
						if($file != "security.txt" && $file != "error_log" && $file != "index.php"){
							$output .= "<li><a href=\"http://uberbots.org/omni/logs/".$file."\" target=\"_blank\">".$file."</a></li>";
						}
					}
				}
				$output .= "</div>";
				closedir($handle);
				}
			}else{
				$output .= "not directory";
			}
		return $output;
	}

	private function listChildren($parent, $level){
		$query = mysql_query("SELECT * FROM `pages` WHERE `parentId` = '$parent' AND `deleted` = '0' ORDER BY `order` ASC, `title` ASC",$GLOBALS["mySQLLink"]);
		while($row = mysql_fetch_array($query)){
			if($row["pageId"]!=0){
			$this->selectOutput .= "<option value='".$row["pageId"]."'>".str_repeat("-",$level).$row["title"]."</option>\n";
			$this->listChildren($row["pageId"],$level+1);
			}
		}

	}

	private function fullChildren($parent, $level){
		$query = mysql_query("SELECT * FROM `pages` WHERE `parentId` = '$parent' ORDER BY `order` ASC, `title` ASC",$GLOBALS["mySQLLink"]);
		while($row = mysql_fetch_array($query)){
			if($row["pageId"]!=0){
				$del = "";
				if($row['deleted'] == 1){
					$del = " - Deleted";
				}
				$this->allPages .= "<option value='".$row["pageId"]."'>".str_repeat("-",$level).$row["title"].$del."</option>\n";
				$this->fullChildren($row["pageId"],$level+1);
			}
		}

	}

	private function showPermissions(){
		$query = mysql_query("SELECT * FROM `pagePermissions` NATUAL JOIN `users`");
        $permissionOutput = "";
		$permissionOutput .= "<h3>Special Permissions List</h3><style type='text/css'>#permissionTable {width:100%;}
#permissionTable TD {padding:5px;width:25%;}</style><table id='permissionTable'><tr><td><b>User ID</b></td><td><b>Username</b></td><td><b>Page ID</b></td><td><b>Permission Type</b></td></tr>";
		while ($row = mysql_fetch_array($query)){

			if ($row['type'] == 0)
				$type = "Read";
			elseif ($row['type'] == 1)
				$type = "Write";
			$permissionOutput .= "<tr><td>".$row['userId']."</td><td>".$row['userName']."</td><td>".$row['pageId']."</td><td>".$type."</td></tr>";
		}
		$permissionOutput .= "</table>";
		return $permissionOutput;
	}

	//check for valid page title
	private function checkTitle($title){
		return preg_match("%\A[A-Za-z0-9\s]{1,20}\Z%",$title);
	}



}
