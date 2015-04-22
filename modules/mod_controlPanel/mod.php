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


        get_logged_in();
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
				$message = deletePage($_POST['page']);
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

            case "addUser":
                $message = addUser(     $_POST['username'],
                                        $_POST['password'],
                                        $_POST['email'],
                                        isset($_POST['isAdmin'])?1:0
                                  );
                break;

			//special user permissions
			case "specialPermission":
				//set vars
				$type = $_POST['permissions'];
				$userId = $_POST['userId'];
				$pageId = $_POST['permissionPage'];
                $permissionType = $_POST['permissionType'];

                switch($type){
                    case "addPermission":
                        $message = addUserPermissions($userId, $pageId, $permissionType);
                        break;
                    case "editPermission":
                        $message = editUserPermissions($userId, $pageId, $permissionType);
                        break;
                    case "delPermission":
                        $message = removeUserPermissions($userId, $pageId);
                        break;
                    default:
                        $message = "Select an action";
                        break;
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
#permissionTable TD {padding:5px;width:25%;}</style><table id='permissionTable'><tr><th>User ID</th><th>Username</th><th>Page ID<th><th>Permission Type</th></tr>";
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

}
