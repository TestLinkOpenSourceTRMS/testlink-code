<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * TestLink support for TINYMCE web editor
 * 
 * @package 	TestLink
 * @copyright 	2007-2009, TestLink community 
 * @version    	CVS: $Id: tinymce.class.php,v 1.4 2009/06/25 19:37:53 havlat Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * @internal Revisions:
 * 
 *	20071201 - francisco.mancardi@gruppotesi.com
 *      code created using as starting point:
 *      fckeditor_php5.php from
 *      FCKeditor - The text editor for Internet - http://www.fckeditor.net
 *      Copyright (C) 2003-2007 Frederico Caldeira Knabben
 * 
 *
 **/

/**
 * @package 	TestLink
 */
class tinymce
{
	var $InstanceName;
	var $Value;
	var $rows = 12;
	var $cols = 80;

	function __construct($instanceName)
 	{
  		$this->InstanceName	= $instanceName;
		$this->Value		= '';
	}
  
 	function Create($rows = null,$cols = null)
	{
		echo $this->CreateHtml($rows,$cols);
	}

	function CreateHtml($rows = null,$cols = null)
	{
		$HtmlValue = htmlspecialchars($this->Value);

    	$my_rows = $rows;
    	$my_cols = $cols;

	    if(is_null($my_rows) || $my_rows <= 0)
			$my_rows = $this->rows;
	    if(is_null($my_cols) || $my_cols <= 0)
	    	$my_cols = $this->cols;
	    
	    // rows must count place for toolbar !! 
		$Html = "<textarea name=\"{$this->InstanceName}\"" .
		        "id=\"{$this->InstanceName}\" rows=\"{$my_rows}\" cols=\"{$my_cols}\">".
		        "{$HtmlValue}</textarea>" ;
		return $Html ;
	}

} // class end


?>