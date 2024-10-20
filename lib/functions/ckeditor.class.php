<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource ckeditor.class.php
 *
 **/

require_once("../../third_party/ckeditorWrapper/CKEditorPHPWrapper.php");

class ckeditorInterface {
	var $InstanceName ;
	var $Value ;
	var $Editor ;
	var $config ;

  /**
   *
   */
	function __construct($instanceName) {
		$this->InstanceName	= $instanceName;
		$this->Value		= '';
		$this->Editor		= new CKEditor();
		$this->Editor->returnOutput = true;
	}
  
  /**
   *
   */
 	function Create() {
		echo $this->CreateHtml($rows,$cols);
	}

  /**
   *
   */
	function CreateHtml($config=[]) {
		$Html = $this->Editor->editor($this->InstanceName, $this->Value, $config);
		return $Html ;
	}

} // class end