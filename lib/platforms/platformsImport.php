<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * Platforms import management
 *
 * @package 	  TestLink
 * @author 		  Francisco Mancardi (francisco.mancardi@gmail.com)
 * @copyright   2005-2022, TestLink community 
 * @filesource  platformsImport.php
 * @link 		    http://www.testlink.org
 * @uses 		    config.inc.php
 *
 *
 */
require('../../config.inc.php');
require_once('common.php');
require_once('xml.inc.php');
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();

$args = init_args($db);
$gui = initializeGui($db,$args);

$resultMap = null;
switch($args->doAction) {
  case 'doImport':
    $gui->file_check = doImport($db,$args->tproject_id);
  break;  
    
  default:
  break;  
}


$smarty = new TLSmarty();
$smarty->assign('gui',$gui);  
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 *
 */
function init_args(&$dbH) {
	$args = new stdClass();
	$iParams = [
    "doAction" => [tlInputParameter::STRING_N,0,50],
    "tproject_id" => [tlInputParameter::INT],
    "tplan_id" => [tlInputParameter::INT]
  ];
		
	R_PARAMS($iParams,$args);
	$args->userID = $_SESSION['userID'];

  list($context,$env) = initContext();
  $args->tproject_id = $context->tproject_id;
  $args->tplan_id = $context->tplan_id;
  if( 0 == $args->tproject_id ) {
    throw new Exception("Unable to Get Test Project ID, Aborting", 1);
  }

  $args->tproject_name = '';
  $tables = tlDBObject::getDBTables(array('nodes_hierarchy'));
  $sql = "SELECT name FROM {$tables['nodes_hierarchy']}  
          WHERE id={$args->tproject_id}";
  $info = $dbH->get_recordset($sql);
  if( null != $info ) {
    $args->tproject_name = $info[0]['name'];
  }

	return $args;
}

/**
 *
 */

function initializeGui(&$dbH,&$argsObj) {

  list($add2args,$guiObj) = initUserEnv($dbH,$argsObj);

  $guiObj->activeMenu['projects'] = 'active'; 

  $guiObj->goback_url = $_SESSION['basehref'] . 
                        'lib/platforms/platformsView.php?tproject_id=' . $guiObj->tproject_id .
                        'tplan_id=' . $guiObj->tplan_id;

  $guiObj->page_title = lang_get('import_platforms');
  $guiObj->file_check = [
    'show_results' => 0, 
    'status_ok' => 1, 
    'msg' => 'ok', 
    'filename' => ''
  ];

  $guiObj->importTypes = array('XML' => 'XML');

  $guiObj->importLimitBytes = config_get('import_file_max_size_bytes');
  $guiObj->max_size_import_file_msg = 
    sprintf(lang_get('max_size_file_msg'), $guiObj->importLimitBytes/1024);

  return $guiObj;  
}


/**
 * @param object dbHandler reference to db handler
 *
 */
function doImport(&$dbHandler,$testproject_id)
{

  $import_msg = array('ok' => array(), 'ko' => array());
  $file_check = array('show_results' => 0, 'status_ok' => 0, 'msg' => '', 
                    	'filename' => '', 'import_msg' => $import_msg);
  
  $key = 'targetFilename';
	$dest = TL_TEMP_PATH . session_id(). "-import_platforms.tmp";
	$fInfo = $_FILES[$key];
	$source = isset($fInfo['tmp_name']) ? $fInfo['tmp_name'] : null;
	if (($source != 'none') && ($source != '')) { 
		$file_check['filename'] = $fInfo['name'];
		$xml = false;
		if (move_uploaded_file($source, $dest)) {
      // http://websec.io/2012/08/27/Preventing-XXE-in-PHP.html
      $xml = @simplexml_load_file_wrapper($dest);
    }
		if ($xml !== FALSE) {
     	$file_check['status_ok'] = 1;
      $file_check['show_results'] = 1;
      $platform_mgr = new tlPlatform($dbHandler,$testproject_id);

      $platformsOnSystem = $platform_mgr->getAllAsMap(['accessKey' => 'name', 
                                                       'output' => 'rows',
                                                       'enable_on_design' => null,
                                                       'enable_on_execution' => null,
                                                       'is_open' => null,
                                                      ]);
                                                      
      foreach($xml as $platform) {
        if (property_exists($platform, 'name')) {  
         	// Check if platform with this name already exists on test Project
         	// if answer is yes => update fields
         	$name = trim((string)$platform->name);
         	if(isset($platformsOnSystem[$name])) {
         		$import_msg['ok'][] = sprintf(lang_get('platform_updated'),$name);

            $platform_mgr->update($platformsOnSystem[$name]['id'],
                                  $name,
                                  (string)$platform->notes,
                                  intval($platform->enable_on_design),
                                  intval($platform->enable_on_execution),
                                  intval($platform->is_open)
                                 );

         	} else {
         		$import_msg['ok'][] = sprintf(lang_get('platform_imported'),$name);
            $item = new stdClass();
            $item->name = $name;
            $item->notes = (string)$platform->notes;
            $item->enable_on_design = intval($platform->enable_on_design);
            $item->enable_on_execution = intval($platform->enable_on_execution);
            $item->is_open = intval($platform->is_open);
            $platform_mgr->create($item);
         	}
        } else {
          $import_msg['ko'][] = lang_get('bad_line_skipped');
        }  
      }      
    }
    else
    {
      $file_check['msg'] = lang_get('problems_loading_xml_content');  
    }  
          
  }
	else
	{
		$msg = getFileUploadErrorMessage($fInfo);
		$file_check = array('show_results' => 0, 'status_ok' => 0,'msg' => $msg);
	}
  
  if( count($import_msg['ko']) == 0 )
  {
    $import_msg['ko'] = null;
  }  
  $file_check['import_msg'] = $import_msg;
  return $file_check;
}

function checkRights(&$db,&$user)
{
	return $user->hasRightOnProj($db,"platform_management");
}