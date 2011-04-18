<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * Platforms import management
 *
 * @filesource	platformsImport.php
 * @package 	TestLink
 * @author 		Francisco Mancardi (francisco.mancardi@gmail.com)
 * @copyright 	2005-2011, TestLink community 
 * @link 		http://www.teamst.org/index.php
 * @uses 		config.inc.php
 *
 * @internal Revisions:
 *
 */
require('../../config.inc.php');
require_once('common.php');
require_once('xml.inc.php');
testlinkInitPage($db,!TL_UPDATE_ENVIRONMENT,false,"checkRights");

$templateCfg = templateConfiguration();
$resultMap = null;


$args = init_args($db);

$gui = new stdClass();

$gui->tproject_id = $args->tproject_id; 
$gui->page_title = lang_get('import_platforms');
$gui->goback_url = is_null($args->goback_url) ? '' : $args->goback_url; 
$gui->file_check = array('show_results' => 0, 'status_ok' => 1, 'msg' => 'ok', 'filename' => '');
$gui->importTypes = array('XML' => 'XML');
$gui->importLimitBytes = config_get('import_file_max_size_bytes');
$gui->max_size_import_file_msg = sprintf(lang_get('max_size_file_msg'), $gui->importLimitBytes/1024);

switch($args->doAction)
{
    case 'doImport':
        $gui->file_check = doImport($db,$args->tproject_id);
    	break;  
    
    default:
    	break;  
}


$smarty = new TLSmarty();
$smarty->assign('gui',$gui);  
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


function init_args(&$dbHandler)
{
	$args = new stdClass();
	$_REQUEST=strings_stripSlashes($_REQUEST);

	$iParams = array("doAction" => array(tlInputParameter::STRING_N,0,50),
	 				 "goback_url" => array(tlInputParameter::STRING_N,0,2048),
 					 "tproject_id" => array(tlInputParameter::INT));
		
	R_PARAMS($iParams,$args);
	$args->userID = $_SESSION['userID'];

	$target = $args->goback_url;
	if( strlen(trim($target)) > 0)
	{
		$target .= (strpos($target,"?") === false) ? "?" : "&"; 
		$target .= "tproject_id={$args->tproject_id}";
	}
	$args->goback_url = $target;

	new dBug($args);
	return $args;
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
	if (($source != 'none') && ($source != ''))
	{ 
		$file_check['filename'] = $fInfo['name'];
		$xml = false;
		if (move_uploaded_file($source, $dest))
		{
			$xml = @simplexml_load_file($dest);
        }
         
		if($xml !== FALSE)
        {
        	$file_check['status_ok'] = 1;
            $file_check['show_results'] = 1;
            $platform_mgr = new tlPlatform($dbHandler,$testproject_id);
            $platformsOnSystem = $platform_mgr->getAllAsMap('name','rows');
            
            foreach($xml as $platform)
            {
            	// Check if platform with this name already exists on test Project
            	// if answer is yes => update fields
            	$name = trim($platform->name);
            	if(isset($platformsOnSystem[$name]))
            	{
            		$import_msg['ok'][] = sprintf(lang_get('platform_updated'),$platform->name);
                    $platform_mgr->update($platformsOnSystem[$name]['id'],$name,$platform->notes);
            	}
            	else
            	{
            		$import_msg['ok'][] = sprintf(lang_get('platform_imported'),$platform->name);
                    $platform_mgr->create($name,$platform->notes);
            	}
            }      
        }
        else
            $file_check['msg'] = lang_get('problems_loading_xml_content');  
  	}
	else
	{
		$msg = getFileUploadErrorMessage($fInfo);
		$file_check = array('show_results' => 0, 'status_ok' => 0, 
		                    'msg' => $msg);
	}
  
  	$file_check['import_msg'] = $import_msg;
  	return $file_check;
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,"platform_management");
}
?>