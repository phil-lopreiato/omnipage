<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

/* mySQL Database Utilities
*  version 0.1
*  developed by Matt Howard, Phil Lopreiato
*/

/* Connect to a local mysql database
 * Connection parameters are read from /omni/config.ini
 */
function mySQLConnect(){
    global $root_path;
	$config = parse_ini_file($root_path."/omni.ini");
    $username = $config['user'];
	$password = $config['pass'];
	$database = $config['db'];
	global $mySQLLink, $mySQLDatabase;
	$mySQLLink = mysql_connect("localhost",$username,$password) or die(mysql_error());
	$mySQLDatabase = mysql_select_db($database,$mySQLLink) or die(mysql_error());
	}

function mySQLClose(){
	mysql_close();
	}
?>
