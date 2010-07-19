<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Manages test plan operations and related items like Custom fields, 
 * Builds, Custom fields, etc
 *
 * @package 	TestLink
 * @author	Martin Havlat <havlat@users.sourceforge.net>
 * @author Chad Rosen
 * @author 		kevyn levy
 *
 * @copyright 	2007-2010, TestLink community 
 * @version    	CVS: $Id: resultsByStatus.php,v 1.81 2010/07/19 18:54:19 erikeloff Exp $
 * @link 		http://www.teamst.org/index.php
 *
 *
 * @internal Revisions:
 *	20100719 - Eloff - Implement extTable for this report
 *	20100617 - eloff - BUGID 3255 - fix bug links if available
 *	201005 - Julian - BUGID 3492 - show only test case summary for not run test cases
 *	                  else show exec notes
 *	20100425 - franciscom - BUGID 3356
 *	20100124 - eloff - use buildExternalIdString()
 *	20091016 - franciscom - work still is needed to display LINK to BUG
 *	20091011 - franciscom - refactoring to do not use result.class
 *	20090517 - franciscom - fixed management of deleted testers
 *	20090414 - amikhullar - BUGID: 2374 - Show Assigned User in the Not Run Test Cases Report 
 *	20090325 - amkhullar  - BUGID 2249
 *	20090325 - amkhullar  - BUGID 2267
 *	20080602 - franciscom - changes due to BUGID 1504
 *	20070623 - franciscom - BUGID 911
*/

// Those defines are simply refering to the column number
define('TABLE_GROUP_BY_TESTSUITE', 0);
define('TABLE_GROUP_BY_PLATFORM', 3);

require('../../config.inc.php');
require_once('common.php');
require_once('displayMgr.php');
require_once('users.inc.php');
require_once('exttable.class.php');
require_once('exec.inc.php'); // used for bug string lookup
if (config_get('interface_bugs') != 'NO')
{
  require_once(TL_ABS_PATH. 'lib' . DIRECTORY_SEPARATOR . 'bugtracking' .
               DIRECTORY_SEPARATOR . 'int_bugtracking.php');
}
testlinkInitPage($db,true,false,"checkRights");

$templateCfg = templateConfiguration();
$resultsCfg = config_get('results');
$statusCode = $resultsCfg['status_code'];

$args = init_args($statusCode);
$gui = initializeGui($statusCode,$args);

$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);
$tcase_mgr = new testcase($db);
$tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
$tproject_info = $tproject_mgr->get_by_id($args->tproject_id);


$getOpt = array('outputFormat' => 'map');
$gui->platformSet = $tplan_mgr->getPlatforms($args->tplan_id,$getOpt);
$show_platforms = !is_null($gui->platformSet);
if( is_null($gui->platformSet) )
{
	$gui->platformSet = array('');
}
$gui->bugInterfaceOn = config_get('bugInterfaceOn');
$bugInterface = null;
if ($gui->bugInterfaceOn) {
	$bugInterface = config_get('bugInterface');
}
$deleted_user_label = lang_get('deleted_user');

$gui->tplan_name = $tplan_info['name'];
$gui->tproject_name = $tproject_info['name'];
$testCaseCfg = config_get('testcase_cfg');

$mailCfg = buildMailCfg($gui);


$arrOwners = getUsersForHtmlOptions($db);

$fl=$tproject_mgr->tree_manager->get_children($args->tproject_id,
                                        array( 'testcase', 'exclude_me',
                                               'testplan' => 'exclude_me',
                                               'requirement_spec' => 'exclude_me' ));

$loop2do = count($fl);
$topLevelSuites=null;
$myRBB = null;
for($idx=0 ; $idx < $loop2do; $idx++)
{
	$topLevelSuites[$fl[$idx]['id']]=array('name' => $fl[$idx]['name'], 'items' => null);
}

if( $args->type == $statusCode['not_run'] )
{
    $filters=null;
    $options=array('group_by_platform_tcversion' => true);
	$myRBB = $tplan_mgr->getNotExecutedLinkedTCVersionsDetailed($args->tplan_id,$filters,$options);
	$user_key='assigned_to';
}
else
{
	$filters = array('exec_status' => array($args->type));
	$options = array('output' => 'array' , 'last_execution' => true, 'only_executed' => true, 'details' => 'summary',
	                 'execution_details' => 'add_build');
	$myRBB = $tplan_mgr->get_linked_tcversions($args->tplan_id,$filters,$options);
	$user_key='tester_id';
}

if( !is_null($myRBB) and count($myRBB) > 0 )
{
    $pathCache=null;
    $topCache=null;
    $levelCache=null;
    $gdx=0;
	foreach($myRBB as $item)
	{
	    $suiteName='';
		$bugString='';
	    if( $item[$user_key] == 0 )
	    {
	    	$testerName = '';
	    }
	    else
	    {
	    	if (array_key_exists($item[$user_key], $arrOwners))
			{
			   $testerName = $arrOwners[$item[$user_key]];
			}
			else
			{
			    // user id has been deleted
			    $testerName = sprintf($deleted_user_label,$item[$user_key]);
			}
		}
		$tcaseName = buildExternalIdString($tproject_info['prefix'], $item['external_id']). ':' . $item['name'];
		if( !isset($pathCache[$item['tc_id']]) )
		{
			$dummy=$tcase_mgr->getPathLayered(array($item['tc_id']));	
			$pathCache[$item['tc_id']] = $dummy[$item['testsuite_id']]['value'];
			$levelCache[$item['tc_id']] = $dummy[$item['testsuite_id']]['level'];
            $ky=current(array_keys($dummy)); 
            $topCache[$item['tc_id']]=$ky;
		}
	    $verbosePath = $pathCache[$item['tc_id']];
	    $level = $levelCache[$item['tc_id']];
		if( $args->type == $statusCode['not_run'] )
		{
			// When not run, test case version, is the version currently linked to test plan
			$topLevelSuites[$topCache[$item['tc_id']]]['items'][$level][] = 
							array('suiteName' => $verbosePath, 'level' => $level,
							      'testTitle' => htmlspecialchars($tcaseName),
							      'testVersion' => $item['version'], 
							      'platformName' => htmlspecialchars($item['platform_name']),
							      'testerName' => htmlspecialchars($testerName),
							      'notes' => strip_tags($item['summary']),
							      'platformID' => $item['platform_id']);
		}			
		else
		{
			// BUGID 3492
			// BUGID 3356
			// When test case has been runned, version must be get from executions.tcversion_number 
			if ($gui->bugInterfaceOn) {
				$bugs = get_bugs_for_exec($db, $bugInterface, $item['exec_id']);
				foreach ($bugs as $bug) {
					$bugString .= $bug['link_to_bts'] . '<br/>';
				}
			}
			$topLevelSuites[$topCache[$item['tc_id']]]['items'][$level][] = 
							array('suiteName' => $verbosePath, 'testTitle' => htmlspecialchars($tcaseName),
			                      'testVersion' => $item['tcversion_number'], 
			                      'platformName' => htmlspecialchars($item['platform_name']),
			                      'buildName' => htmlspecialchars($item['build_name']),
			                      'testerName' => htmlspecialchars($testerName),
			                      'localizedTS' => $item['execution_ts'],
			                      'notes' => strip_tags($item['execution_notes']),
			                      'bugString' => $bugString,
			                      'platformID' => $item['platform_id']);
		}	      
	}

    // Rearrange for display
	$key2loop=array_keys($topLevelSuites);
	foreach($key2loop as $key)
	{
		if(	is_null($topLevelSuites[$key]['items']) )
		{
			unset($topLevelSuites[$key]);
		}
	}
	$key2loop=array_keys($topLevelSuites);
	$idx=0;
	foreach($key2loop as $key)
	{
		$elem=&$topLevelSuites[$key]['items'];
		$levelSet=array_keys($topLevelSuites[$key]['items']);
		foreach($levelSet as $level)
		{
			//$loop2do=count($topLevelSuites[$key]['items'][$level]);
			$loop2do=count($elem[$level]);
			for($jdx=0 ; $jdx < $loop2do ; $jdx++)
			{
				// $gui->dataSet[$idx]=$topLevelSuites[$key]['items'][$level][$jdx];
				$gui->dataSet[$idx]=$elem[$level][$jdx];
				unset($gui->dataSet[$idx]['level']);
				unset($gui->dataSet[$idx]['platformID']);
		        
		        $accessKey = $elem[$level][$jdx]['platformID'];
		        $gui->dataSetByPlatform[$accessKey][]=$gui->dataSet[$idx];
		        $idx++;
			}  		
		}
    }    	
}    	

new dBug($gui->dataSet);
new dBug($gui->dataSetByPlatform);

$gui->tableSet[] = buildMatrix($gui->dataSet, array(
		'status_not_run' => ($args->type == $statusCode['not_run']),
		'bugInterfaceOn' => $gui->bugInterfaceOn,
		'format' => $args->format,
		'show_platforms' => $show_platforms,
	));

$smarty = new TLSmarty();
$smarty->assign('gui', $gui );
displayReport($templateCfg->template_dir . $templateCfg->default_template, $smarty, $args->format,$mailCfg);

/**
* Function returns number of Test Cases in the Test Plan
* @return string Link of Test ID + Title
*/
function buildTCLink($tcID,$tcversionID, $title, $buildID,$testCaseExternalId, $tplanId)
{
	$title = htmlspecialchars($title);
	$suffix = htmlspecialchars($testCaseExternalId) . ":&nbsp;<b>" . $title. "</b></a>";
	//Added tplan_id as a parameter - amitkhullar -BUGID 2267
	$testTitle = '<a href="lib/execute/execSetResults.php?level=testcase&build_id='
				 . $buildID . '&id=' . $tcID . '&version_id='. $tcversionID . '&tplan_id=' . $tplanId .'">';
	$testTitle .= $suffix;

	return $testTitle;
}


/**
 * 
 *
 */
function init_args($statusCode)
{
    $iParams = array("format" => array(tlInputParameter::INT_N),
		             "tplan_id" => array(tlInputParameter::INT_N),
    	             "type" => array(tlInputParameter::STRING_N,0,1));
	$args = new stdClass();
	R_PARAMS($iParams,$args);
	
	$args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
	$args->user = $_SESSION['currentUser'];

	return $args;
}

/**
 * initializeGui
 *
 */
function initializeGui($statusCode,&$argsObj)
{
    $guiObj = new stdClass();
    
    // Count for the Failed Issues whose bugs have to be raised/not linked. 
    $guiObj->without_bugs_counter = 0; 
    $guiObj->dataSet = null;
    $guiObj->dataSetByPlatform = null;
    $guiObj->title = null;
    $guiObj->type = $argsObj->type;

    // Humm this may be can be configured ???
    foreach(array('failed','blocked','not_run') as $verbose_status)
    {
        if($argsObj->type == $statusCode[$verbose_status])
        {
            $guiObj->title = lang_get('list_of_' . $verbose_status);
            break;
        }  
    }
    if(is_null($guiObj->title))
    {
    	tlog('wrong value of GET type');
    	exit();
    }
    
    return $guiObj;    
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_metrics');
}


/**
 * 
 *
 */
function buildMailCfg(&$guiObj)
{
	$labels = array('testplan' => lang_get('testplan'), 'testproject' => lang_get('testproject'));
	$cfg = new stdClass();
	$cfg->cc = ''; 
	$cfg->subject = $guiObj->title . ' : ' . $labels['testproject'] . ' : ' . $guiObj->tproject_name . 
	                ' : ' . $labels['testplan'] . ' : ' . $guiObj->tplan_name;
	                 
	return $cfg;
}

/**
 * Builds ext-js rich table to display matrix results
 *
 * @param map dataSet: data to be displayed on matrix
 *
 * return tlExtTable
 *
 */
function buildMatrix($dataSet, $options = array())
{
	$default_options = array(
		'bugInterfaceOn' => false,
		'show_platforms' => false,
		'status_not_run' => false,
		'format' => FORMAT_HTML,
	);
	$options = array_merge($default_options, $options);
	$columns = array();
	$columns[] = array('title' => lang_get('title_test_suite_name'), 'width' => 100);
	$columns[] = array('title' => lang_get('title_test_case_title'), 'width' => 250);
	$columns[] = array('title' => lang_get('version'), 'width' => 50);
	if ($options['show_platforms'])
	{
		$columns[] = array('title' => lang_get('platform'));
	}
	if( $options['status_not_run'] )
	{
		$columns[] = array('title' => lang_get('assigned_to'));
		$columns[] = array('title' => lang_get('summary'));
	}
	else
	{
		$columns[] = array('title' => lang_get('th_build'));
		$columns[] = array('title' => lang_get('th_run_by'));
		$columns[] = array('title' => lang_get('th_date'));
		$columns[] = array('title' => lang_get('title_execution_notes'));
		if ($options['bugInterfaceOn'])
		{
			$columns[] = array('title' => lang_get('th_bugs'));
		}
	}

	if ($format == FORMAT_HTML)
	{
		$matrix = new tlExtTable($columns, $dataSet, 'tl_table_results_by_status');
		if ($options['show_platforms'])
		{
			$matrix->groupByColumn = TABLE_GROUP_BY_PLATFORM;
		}
		else
		{
			$matrix->groupByColumn = TABLE_GROUP_BY_TESTSUITE;
		}
	}
	else
	{
		$matrix = new tlHTMLTable($columns, $dataSet, 'tl_table_results_by_status');
	}
	return $matrix;
}
