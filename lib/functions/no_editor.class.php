<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: no_editor.class.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2007/12/02 17:09:28 $ by $Author: franciscom $
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
  
 	function Create($rows=8,$cols=80)
	{
		echo $this->CreateHtml($rows,$cols) ;
	}

	function CreateHtml($rows=8,$cols=80)
	{
		$HtmlValue = htmlspecialchars( $this->Value ) ;

    $my_rows=$rows;
    $my_cols=$cols;

    if( is_null($my_rows) || $my_rows <= 0 )
    {  
      $my_rows=8;
    }
    if( is_null($my_cols) || $my_cols <= 0 )
    {  
      $my_cols=80;
    }

		$Html = "<textarea name=\"{$this->InstanceName}\" " .
		        "id=\"{$this->InstanceName}\" rows=\"{$my_rows}\" cols=\"{$my_cols}\" \">".
		        "{$HtmlValue}</textarea>" ;
		return $Html ;
	}

} // class end
?>