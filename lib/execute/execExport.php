<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * Allows export in XML format of:
 *
 * . complete plan contents: 
 *   linked platforms
 *	 linked test cases (minimal information)
 *
 * 	 
 * @package 	TestLink
 * @author 		Francisco Mancardi
 * @copyright 	2003-2009, TestLink community 
 * @version    	CVS: $Id: execExport.php,v 1.1 2010/09/26 09:33:17 franciscom Exp $
 * @link 		http://www.teamst.org/index.php
 * 
 * @internal Revisions:
 **/
require_once("../../config.inc.php");
require_once("../functions/common.php");
require_once("../functions/xml.inc.php");
testlinkInitPage($db);
$templateCfg = templateConfiguration();

$tplan_mgr = new testplan($db);

$args = init_args();
$gui = initializeGui($args,$tplan_mgr);

if ($args->doExport)
{
	// Generate Context
	$execContext = contextAsXML($db,$args,$tplan_mgr);
    
	
	$content = $tplan_mgr->exportLinkedItemsToXML($args->tplan_id);
	downloadContentsToFile($content,$gui->export_filename);
	exit();
}

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/*
  function: init_args

  args:
  
  returns: 

*/
function init_args()
{
    $_REQUEST = strings_stripSlashes($_REQUEST);
    
    	
	new dBug($_REQUEST);

    $args = new stdClass();
    $args->doExport = isset($_REQUEST['export']) ? $_REQUEST['export'] : null;
    $args->exportType = isset($_REQUEST['exportType']) ? $_REQUEST['exportType'] : null;


	$key2loop = array('tproject','tplan','platform','build','tsuite');
	foreach($key2loop as $item)
	{
		$argsKey = $item . '_id';
		$inputKey = $item . 'ID';
		$args->$argsKey = isset($_REQUEST[$inputKey]) ? intval($_REQUEST[$inputKey]) : 0;
	}

    $args->export_filename=isset($_REQUEST['export_filename']) ? $_REQUEST['export_filename'] : null;
    $args->goback_url=isset($_REQUEST['goback_url']) ? $_REQUEST['goback_url'] : null;

	new dBug($args);
    return $args;
}


/**
 * 
 *
 */
function initializeGui(&$argsObj,&$tplanMgr)
{
	$info = $tplanMgr->get_by_id($argsObj->tplan_id);

	$guiObj = new stdClass();
	$guiObj->do_it = 1;
	$guiObj->nothing_todo_msg = '';
	$guiObj->export_filename = 'export_' . $info['name'] . '.xml';
	$guiObj->exportTypes = array('XML' => 'XML');
	$guiObj->page_title = lang_get('export_test_plan');
	$guiObj->object_name = $info['name'];
	$guiObj->goback_url = !is_null($argsObj->goback_url) ? $argsObj->goback_url : ''; 

	$key2loop = array('tproject','tplan','platform','build','tsuite');
	foreach($key2loop as $item)
	{
		$argsKey = $item . '_id';
		// $inputKey = $item . 'ID';
		$guiObj->$argsKey = intval($argsObj->$argsKey);
	}

	new dBug($guiObj);
	return $guiObj;
}


/**
 * 
 *
 */
function contextAsXML(&$dbHandler,$contextSet,&$tplanMgr)
{
	$info = array();
	$tprojectMgr = new testproject($dbHandler);
	$info['tproject'] = $tprojectMgr->get_by_id($contextSet->tproject_id);
	unset($tprojectMgr);
	
	$info['tplan'] = $tplanMgr->get_by_id($contextSet->tplan_id);

	$buildMgr = new build_mgr($dbHandler);
	$info['build'] = $buildMgr->get_by_id($contextSet->build_id);
	unset($buildMgr);
	
	$info['platform'] = null;
	$plaftorm_template = '';
	if( $contextSet->platform_id > 0 )
	{
		$platformMgr = new tlPlatform($dbHandler, $contextSet->tproject_id);
		$info['platform'] = $platformMgr->getByID($contextSet->platform_id);
		unset($platformMgr);

		$platform_template = "\n\t" .
							 "<platform>" . 
    					 	 "\t\t" . "<name><![CDATA[||PLATFORMNAME||]]></name>" .
    					 	 "\t\t" . "<internal_id><![CDATA[||PLATFORMID||]]></internal_id>" .
    					 	 "\n\t" . "</platform>";
	}
	
	$key2loop = array_keys($info);
	foreach($key2loop as $item_key)
	{
		if(!is_null($info[$item_key]))
		{
			$contextInfo[$item_key . '_id'] = $info[$item_key]['id'];
			$contextInfo[$item_key . '_name'] = $info[$item_key]['name'];
		}
	} 
	$contextInfo['prefix'] = $info['tproject']['prefix'];
	
	$xml_root = "<context>{{XMLCODE}}\n</context>";
	$xml_template = "\n\t" . 
					"<testproject>" . 
    				"\t\t" . "<name><![CDATA[||TPROJECTNAME||]]></name>" .
    				"\t\t" . "<internal_id><![CDATA[||TPROJECTID||]]></internal_id>" .
    				"\t\t" . "<prefix><![CDATA[||TPROJECTPREFIX||]]></prefix>" .
    				"\n\t" . "</testproject>" .
    				"\n\t" .
					"<testplan>" . 
    				"\t\t" . "<name><![CDATA[||TPLANNAME||]]></name>" .
    				"\t\t" . "<internal_id><![CDATA[||TPLANID||]]></internal_id>" .
    				"\n\t" . "</testplan>" . $platform_template .
    				"\n\t" .
					"<build>" . 
    				"\t\t" . "<name><![CDATA[||BUILDNAME||]]></name>" .
    				"\t\t" . "<internal_id><![CDATA[||BUILDID||]]></internal_id>" .
    				"\n\t" . "</build>";

	$xml_mapping = null;
	$xml_mapping = array("||TPROJECTNAME||" => "tproject_name", "||TPROJECTID||" => 'tproject_id',
						 "||TPROJECTPREFIX||" => "prefix",
						 "||TPLANNAME||" => "tplan_name", "||TPLANID||" => 'tplan_id',
						 "||BUILDNAME||" => "build_name", "||BUILDID||" => 'build_id',
						 "||PLATFORMNAME||" => "platform_name", "||PLATFORMID||" => 'platform_id');


	$mm = array($contextInfo);
	$contextXML = exportDataToXML($mm,$xml_root,$xml_template,$xml_mapping,('noXMLHeader'=='noXMLHeader'));
	// echo '<pre><xmp>';
	// echo $contextXML;
	// echo '</xmp></pre>';
    
    return $contextXML;
}
?>
