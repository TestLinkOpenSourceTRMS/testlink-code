<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * Custom Fields definition import management
 *
 * @package 	  TestLink
 * @author 		  Francisco Mancardi (francisco.mancardi@gmail.com)
 * @copyright 	2005-2023, TestLink community 
 * @filesource  cfieldsImport.php
 * @uses 		    config.inc.php
 *
 */
require('../../config.inc.php');
require_once('common.php');
require_once('xml.inc.php');

testlinkInitPage($db,false,false,"checkRights");
$templateCfg = templateConfiguration();

$resultMap = null;
list($args,$gui,$cfield_mgr) = initEnv($db);

 
switch( $args->doAction ) {
  case 'doImport':
    $gui->file_check = doImport($db,$cfield_mgr);
  break;  
    
  default:
  break;  
}

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

	$iParams = [
    "doAction" => array(tlInputParameter::STRING_N,0,50),
    "tproject_id" => array(tlInputParameter::INT_N),
    "tplan_id" => array(tlInputParameter::INT_N),                   
	 	"export_filename" => array(tlInputParameter::STRING_N,0,100),
	 	"goback_url" => array(tlInputParameter::STRING_N,0,2048)
  ];

	R_PARAMS($iParams,$args);

  $context = new stdClass();
  $k2ctx = [
    'tproject_id' => 0,
    'tplan_id' => 0
  ];
  $env = '';
  foreach ($k2ctx as $prop => $defa) {
    $context->$prop = $args->$prop;
    if ($env != '') {
      $env .= "&";
    }
    $env .= "$prop=" . $context->$prop;
  }

  $args->userID = $_SESSION['userID'];
	return array($args,$context,$env);
}


/**
 * @param object dbHandler reference to db handler
 *
 */
function doImport(&$dbHandler,&$cfield_mgr)
{

  $import_msg = [
    'ok' => [], 
    'ko' => []
  ];
  $file_check = [
    'show_results' => 0,
    'status_ok' => 0, 
    'msg' => '',
    'filename' => '', 
    'import_msg' => $import_msg
  ];
  
  $key='targetFilename';
	$dest = TL_TEMP_PATH . session_id(). "-import_cfields.tmp";
	$source = isset($_FILES[$key]['tmp_name']) ? $_FILES[$key]['tmp_name'] : null;
	
	if (($source != 'none') && ($source != '')) { 
		$file_check['filename'] = $_FILES[$key]['name'];
		$file_check['status_ok'] = 1;
		if (move_uploaded_file($source, $dest)) {
      $file_check['status_ok']=!(($xml=@simplexml_load_file_wrapper($dest)) === FALSE);
		}
    if ($file_check['status_ok']) {
      $file_check['show_results']=1;
       foreach($xml as $cf) {
        if (is_null($cfield_mgr->get_by_name($cf->name))) {
          $cfield_mgr->create((array) $cf);
          $import_msg['ok'][]=sprintf(lang_get('custom_field_imported'),$cf->name);              
        } else {
          $import_msg['ko'][]=sprintf(lang_get('custom_field_already_exists'),$cf->name);              
        }
      }      
    } else {
      $file_check['msg']=lang_get('problems_loading_xml_content');  
    }
 	} else {
		$file_check = array('show_results'=>0, 'status_ok' => 0, 
		                    'msg' => lang_get('please_choose_file_to_import'));
	}
  
  $file_check['import_msg']=$import_msg;
  return $file_check;
}


/**
 *
 */
function initEnv(&$dbH) 
{

  list($args,$context,$env) = init_args();
  $cfield_mgr = new cfield_mgr($dbH);

  $gui = $cfield_mgr->initViewGUI($context,$env);
  $gui->activeMenu['system'] = 'active';  
  $gui->importTypes = array('XML' => 'XML');
  $gui->importLimitKB = (config_get('import_file_max_size_bytes') / 1024);
  $gui->page_title = lang_get('import_cfields');
  $gui->goback_url = !is_null($args->goback_url) ? $args->goback_url : ''; 
  $gui->file_check = array('show_results' => 0, 'status_ok' => 1, 
                           'msg' => 'ok', 'filename' => '');


  return array($args,$gui,$cfield_mgr);  
}

/**
 * 
 *
 */
function checkRights(&$db,&$user)
{
	return $user->hasRight($db,"cfield_management");
}