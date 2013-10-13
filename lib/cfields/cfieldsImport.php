<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * Custom Fields definition import management
 *
 * @package 	  TestLink
 * @author 		  Francisco Mancardi (francisco.mancardi@gmail.com)
 * @copyright 	2005-2013, TestLink community 
 * @filesource  cfieldsImport.php,v 1.5 2010/03/15 20:22:42 franciscom Exp $
 * @link 		    http://www.teamst.org/index.php
 * @uses 		    config.inc.php
 *
 * @internal revisions
 * @since 1.9.9
 */
require('../../config.inc.php');
require_once('common.php');
require_once('xml.inc.php');

testlinkInitPage($db,false,false,"checkRights");
$templateCfg = templateConfiguration();

$resultMap = null;
$args = init_args();

$gui=new stdClass();
$gui->page_title=lang_get('import_cfields');
$gui->goback_url = !is_null($args->goback_url) ? $args->goback_url : ''; 
$gui->file_check = array('show_results' => 0, 'status_ok' => 1, 
                         'msg' => 'ok', 'filename' => '');

switch( $args->doAction )
{
    case 'doImport':
        $gui->file_check = doImport($db);
    	break;  
    
    default:
    	break;  
}

$obj_mgr = new cfield_mgr($db);
$gui->importTypes = array('XML' => 'XML');
$gui->importLimitKB = (config_get('import_file_max_size_bytes') / 1024);

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);  
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);



/*
  function: init_args()

  args:
  
  returns: 

*/
function init_args()
{
	$args = new stdClass();
	$_REQUEST = strings_stripSlashes($_REQUEST);

	$iParams = array("doAction" => array(tlInputParameter::STRING_N,0,50),
	 				 "export_filename" => array(tlInputParameter::STRING_N,0,100),
	 				 "goback_url" => array(tlInputParameter::STRING_N,0,2048));

	R_PARAMS($iParams,$args);

  	// $args->doAction = isset($_REQUEST['doAction']) ? $_REQUEST['doAction'] : null;
  	// $args->export_filename=isset($_REQUEST['export_filename']) ? $_REQUEST['export_filename'] : null;
  	// $args->goback_url = isset($_REQUEST['goback_url']) ? $_REQUEST['goback_url'] : null;

  	$args->userID = $_SESSION['userID'];

	return $args;
}


/**
 * @param object dbHandler reference to db handler
 *
 */
function doImport(&$dbHandler)
{

  	$import_msg=array('ok' => array(), 'ko' => array());
  	$file_check=array('show_results' => 0, 'status_ok' => 0, 'msg' => '', 
                    'filename' => '', 'import_msg' => $import_msg);
  
  	$key='targetFilename';
	$dest = TL_TEMP_PATH . session_id(). "-import_cfields.tmp";
	$source = isset($_FILES[$key]['tmp_name']) ? $_FILES[$key]['tmp_name'] : null;
	
	if (($source != 'none') && ($source != ''))
	{ 
		$file_check['filename'] = $_FILES[$key]['name'];
		$file_check['status_ok'] = 1;
		if (move_uploaded_file($source, $dest))
		{
      $file_check['status_ok']=!(($xml=@simplexml_load_file_wrapper($dest)) === FALSE);
		}
    if( $file_check['status_ok'] )
    {
      $file_check['show_results']=1;
      $cfield_mgr = new cfield_mgr($dbHandler);
      foreach($xml as $cf)
      {
        if( is_null($cfield_mgr->get_by_name($cf->name)) )
        {
          $cfield_mgr->create((array) $cf);
          $import_msg['ok'][]=sprintf(lang_get('custom_field_imported'),$cf->name);              
        }
        else
        {
          $import_msg['ko'][]=sprintf(lang_get('custom_field_already_exists'),$cf->name);              
        }
      }      
    }
    else
    {
      $file_check['msg']=lang_get('problems_loading_xml_content');  
    }
 	}
	else
	{
		$file_check = array('show_results'=>0, 'status_ok' => 0, 
		                    'msg' => lang_get('please_choose_file_to_import'));
	}
  
  $file_check['import_msg']=$import_msg;
  return $file_check;
}

/**
 * 
 *
 */
function checkRights(&$db,&$user)
{
	return $user->hasRight($db,"cfield_management");
}
?>