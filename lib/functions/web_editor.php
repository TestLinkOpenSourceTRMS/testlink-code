<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * @filesource $RCSfile: web_editor.php,v $
 * @version $Revision: 1.5 $ $Author: havlat $
 * @modified $Date: 2008/06/03 10:54:18 $
 *
 *
 **/

require_once(dirname(__FILE__)."/../../config.inc.php");
require_once("common.php");

switch($tlCfg->gui_text_editor)
{
	case 'fckeditor':
		require_once("../../third_party/fckeditor/fckeditor.php");
		break;

	case 'none':
		require_once("no_editor.class.php");
		break;

	case 'tinymce':
		require_once("tinymce.class.php");
		break;
}


/*
  function:

  args:

  returns:

*/
function web_editor($html_input_id,$base_path)
{
	global $tlCfg;

	switch($tlCfg->gui_text_editor)
	{
		case 'fckeditor':
			$of = new fckeditor($html_input_id) ;
			$of->BasePath = $base_path . 'third_party/fckeditor/';
			$of->ToolbarSet = $tlCfg->fckeditor_default_toolbar;
			break;

		case 'none':
			$of = new no_editor($html_input_id) ;
			break;

		case 'tinymce':
			$of = new tinymce($html_input_id) ;
			break;
  }

  return $of;
}
?>