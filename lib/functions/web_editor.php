<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * @filesource $RCSfile: web_editor.php,v $
 * @version $Revision: 1.7 $ $Author: franciscom $
 * @modified $Date: 2008/08/27 06:22:14 $
 *
 *
 * rev: 20080826 - franciscom - BUGID 1692
 *      refactoring to allow use of different editor type in different TL features/areas
 **/

require_once(dirname(__FILE__)."/../../config.inc.php");
require_once("common.php");


/*
  function: getWebEditorCfg

  args:-

  returns:

*/
function getWebEditorCfg($feature='all')
{
    $cfg=config_get('gui');
	  $webEditorType=isset($cfg->text_editor[$feature])?$cfg->text_editor[$feature]:$cfg->text_editor['all'];
    return $webEditorType;
}


/*
  function: require_web_editor

  args:

  returns:

*/
function require_web_editor($editor_type=null)
{
    $webEditorType=is_null($editor_type) ? getWebEditorCfg() : $editor_type;
	  switch($webEditorType)
    {
    	case 'fckeditor':
    		return "../../third_party/fckeditor/fckeditor.php";
    		break;
   
    	case 'tinymce':
    		return "tinymce.class.php";
    		break;

    	case 'none':
    	default:
    		return "no_editor.class.php";
    		break;
    }
}

/*
  function: web_editor

  args:

  returns:

*/
function web_editor($html_input_id,$base_path,$editor_type=null)
{
    $webEditorType=is_null($editor_type) ? getWebEditorCfg() : $editor_type;
	  switch($webEditorType)
	  {
	  	case 'fckeditor':
	  		$of = new fckeditor($html_input_id) ;
	  		$of->BasePath = $base_path . 'third_party/fckeditor/';
	  		$of->ToolbarSet = config_get('fckeditor_default_toolbar');
	  		$of->Config['CustomConfigurationsPath']  = $base_path . 'cfg/tl_fckeditor_config.js';
	  		break;
    
	  	case 'tinymce':
	  		$of = new tinymce($html_input_id) ;
	  		break;
    
	  	case 'none':
	  	default:
	  		$of = new no_editor($html_input_id) ;
	  		break;
    }
    
    return $of;
}
?>