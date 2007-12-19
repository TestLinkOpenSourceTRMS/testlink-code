<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * @filesource $RCSfile: web_editor.php,v $
 * @version $Revision: 1.3 $ $Author: franciscom $
 * @modified $Date: 2007/12/19 18:01:44 $
 *
 *
 **/ 

require_once(dirname(__FILE__)."/../../config.inc.php");
require_once("common.php");

$gui_cfg=config_get('gui');
$editor=$gui_cfg->webeditor;
switch($editor)
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

  $gui_cfg=config_get('gui');
  $editor=$gui_cfg->webeditor;
  
  switch($editor)
  {
    case 'fckeditor':
    $toolbar=config_get('fckeditor_toolbar');
    $of = new fckeditor($html_input_id) ;
    $of->BasePath = $base_path . 'third_party/fckeditor/';
    $of->ToolbarSet=$toolbar;
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