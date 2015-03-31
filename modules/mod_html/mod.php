<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

/* HTML Module
 * version 0.1
 * Developed by Matt Howard, Phil Lopreiato
 */

class mod_HTML {

	public $title = 'HTML';
	public $description = 'uses basic HTML markup';
	public $path = 'mod_html';

	public function render($properties) {
		return $properties["code"];
	}

	public function renderEdit($properties) {
		global $currentSkin;
	    return parseSkin($properties, "mod_html_edit.html");
    }

	public function edit($properties) {
		setVariables(mysql_real_escape_string($properties['pageId']),mysql_real_escape_string($properties['modId']),array('code'=>$properties['code']));
	}

	var $sqlNames, $sqlDefaults;

	public function setup() {
		$this->sqlNames = array("code");
		$this->sqlDefaults = array("HTML markup");
	}
}
