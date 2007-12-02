<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * @filesource $RCSfile: web_editor.php,v $
 * @version $Revision: 1.2 $ $Author: franciscom $
 * @modified $Date: 2007/12/02 17:09:26 $
 *
 * @author 	Martin Havlat
 * @author 	Chad Rosen
 *
 * Common functions: database connection, session and data initialization,
 * maintain $_SESSION data, redirect page, log, etc. 
 *
 * @var array $_SESSION
 * - user related data are adjusted via doAuthorize.php and here (product & test plan)  
 * - has next values: valid (yes/no), user (login name), role (e.g. admin),
 * email, userID, productID, productName, testplan (use rather testPlanID),
 * testPlanID, testPlanName
 *
 * 20071027 - franciscom - added ini_get_bool() from mantis code, needed to user
 *                         string_api.php, also from Mantis.
 * 
 * 20071002 - jbarchibald - BUGID 1051
 * 20070707 - franciscom - BUGID 921 - changes to gen_spec_view()
 * 20070705 - franciscom - init_labels()
 *                         gen_spec_view(), changes on process of inactive versions
 * 20070623 - franciscom - improved info in header of localize_dateOrTimeStamp()
 * 20070104 - franciscom - gen_spec_view() warning message removed
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