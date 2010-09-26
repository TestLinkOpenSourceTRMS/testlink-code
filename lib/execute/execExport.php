<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * Allows export in XML format of set of test cases displayed on BULK execution
 * 	 
 * @package 	TestLink
 * @author 		Francisco Mancardi
 * @copyright 	2003-2009, TestLink community 
 * @version    	CVS: $Id: execExport.php,v 1.3 2010/09/26 14:46:24 franciscom Exp $
 * @link 		http://www.teamst.org/index.php
 * 
 * @internal Revisions:
 * 20100926 - franciscom - BUGID 3421: Test Case Execution feature - Add Export All test Case in TEST SUITE button
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
	$content = contentAsXML($db,$args,$tplan_mgr);
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

    $args->tcversionSet=isset($_REQUEST['tcversionSet']) ? $_REQUEST['tcversionSet'] : null;
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
	$guiObj->export_filename = 'export_execution_set.xml';
	$guiObj->exportTypes = array('XML' => 'XML');
	$guiObj->page_title = lang_get('export_execution_set');
	$guiObj->object_name = '';
	$guiObj->goback_url = !is_null($argsObj->goback_url) ? $argsObj->goback_url : ''; 

	$key2loop = array('tproject','tplan','platform','build','tsuite');
	foreach($key2loop as $item)
	{
		$argsKey = $item . '_id';
		// $inputKey = $item . 'ID';
		$guiObj->$argsKey = intval($argsObj->$argsKey);
	}
    $guiObj->tcversionSet  = $argsObj->tcversionSet;
	$guiObj->drawCancelButton = false;

	return $guiObj;
}



/**
 * 
 *
 */
function contentAsXML(&$dbHandler,$contextSet,&$tplanMgr)
{
	$dummy = array();
	$dummy['context'] = contextAsXML($dbHandler,$contextSet,$tplanMgr);
	$dummy['tcaseSet'] = tcaseSetAsXML($dbHandler,$contextSet);    

	$xmlString = TL_XMLEXPORT_HEADER . 
				"\n<executionSet>\n\t{$dummy['context']}\n\t{$dummy['tcaseSet']}\n\t</executionSet>";
    return $xmlString;

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

/**
 * 
 *
 */
function  tcaseSetAsXML(&$dbHandler,$contextSet)
{
	$tcaseMgr = new testcase($dbHandler);
	$tcversionSet = explode(',',$contextSet->tcversionSet);
	$xmlTC = "<testcases>\n\t";
	foreach($tcversionSet as $tcversion_id)
	{
		$xmlTC .= $tcaseMgr->exportTestCaseDataToXML(0,$tcversion_id,$contextSet->tproject_id,true);

    }
    $xmlTC .= "</testcases>\n\t";
	return $xmlTC;
}
?>
