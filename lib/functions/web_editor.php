<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * @filesource $RCSfile: web_editor.php,v $
 * @version $Revision: 1.10 $ $Author: franciscom $
 * @modified $Date: 2008/12/31 15:07:19 $
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
    $cfg = config_get('gui');
    $defaultCfg = $cfg->text_editor['all'];
	  $webEditorCfg = isset($cfg->text_editor[$feature]) ? $cfg->text_editor[$feature] : $defaultCfg;
  
	  foreach($defaultCfg as $key => $value)
  	{
    	  if(!isset($webEditorCfg[$key]))
    	  {
          	$webEditorCfg[$key] = $defaultCfg[$key];
        }  	
	  } 
    return $webEditorCfg;
}


/*
  function: require_web_editor

  args:

  returns:

*/
function require_web_editor($editor_type=null)
{
    $webEditorType = $editor_type;
    if(is_null($editor_type))
    {
        $cfg = getWebEditorCfg();
        $webEditorType = $cfg['type'];
	}
	  
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
function web_editor($html_input_id,$base_path,$editor_cfg=null)
{
    $webEditorCfg = is_null($editor_cfg) ? getWebEditorCfg() : $editor_cfg;

	switch($webEditorCfg['type'])
	{
		case 'fckeditor':
			$of = new fckeditor($html_input_id) ;
			$of->BasePath = $base_path . 'third_party/fckeditor/';
			$of->ToolbarSet = $webEditorCfg['toolbar'];
			$of->Config['CustomConfigurationsPath']  = $base_path . $webEditorCfg['configFile'];
			if (isset($webEditorCfg['height']))
				$of->Height = $webEditorCfg['height'];
			if (isset($webEditorCfg['width']))
				$of->Width = $webEditorCfg['width'];
		break;
		    
		case 'tinymce':
			$of = new tinymce($html_input_id) ;
			if (isset($webEditorCfg['rows']))
				$of->rows = $webEditorCfg['rows'];
			if (isset($webEditorCfg['cols']))
				$of->cols = $webEditorCfg['cols'];
			break;
		    
		case 'none':
		default:
			$of = new no_editor($html_input_id) ;
			if (isset($webEditorCfg['rows']))
				$of->rows = $webEditorCfg['rows'];
			if (isset($webEditorCfg['cols']))
				$of->cols = $webEditorCfg['cols'];
			break;
	}
    
    return $of;
}
?>