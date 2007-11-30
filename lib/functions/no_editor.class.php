<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: no_editor.class.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2007/11/30 07:54:24 $ by $Author: franciscom $
 * 
 * Rev :
 *      20071125 - franciscom - added dtree_render_req_node_open
 *
 **/

class no_editor
{
  
  var $InstanceName ;
  var $Value ;

  function __construct( $instanceName )
 	{
  	$this->InstanceName	= $instanceName ;
		$this->Value		= '' ;
  }
  
 	function Create()
	{
		echo $this->CreateHtml() ;
	}

	function CreateHtml()
	{
		$HtmlValue = htmlspecialchars( $this->Value ) ;

		$Html = "<textarea name=\"{$this->InstanceName}\" id=\"{$this->InstanceName}\" rows=\"4\" cols=\"40\" \">".
		        "{$HtmlValue}</textarea>" ;
		return $Html ;
	}

} // class end
?>