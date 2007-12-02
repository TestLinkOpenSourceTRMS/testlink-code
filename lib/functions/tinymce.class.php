<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: tinymce.class.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2007/12/02 17:07:05 $ by $Author: franciscom $
 * 
 * Rev :
 *      20071201 - francisco.mancardi@gruppotesi.com
 *      code created using as starting point:
 *      fckeditor_php5.php from
 *      FCKeditor - The text editor for Internet - http://www.fckeditor.net
 *      Copyright (C) 2003-2007 Frederico Caldeira Knabben
 * 
 *
 **/

class tinymce
{
  
  var $InstanceName ;
  var $Value ;

  function __construct( $instanceName )
 	{
  	$this->InstanceName	= $instanceName ;
		$this->Value		= '' ;
  }
  
 	function Create($rows=12,$cols=80)
	{
		echo $this->CreateHtml($rows,$cols) ;
	}

	function CreateHtml($rows=12,$cols=80)
	{
		$HtmlValue = htmlspecialchars( $this->Value ) ;

    $my_rows=$rows;
    $my_cols=$cols;

    if( is_null($my_rows) || $my_rows <= 0 )
    {  
      $my_rows=12;
    }
    if( is_null($my_cols) || $my_cols <= 0 )
    {  
      $my_cols=80;
    }
    
    
    // rows must count place for toolbar !! 
		$Html = "<textarea name=\"{$this->InstanceName}\" " .
		        "id=\"{$this->InstanceName}\" rows=\"{$my_rows}\" cols=\"{$my_cols}\" \">".
		        "{$HtmlValue}</textarea>" ;
		return $Html ;
	}

} // class end
?>