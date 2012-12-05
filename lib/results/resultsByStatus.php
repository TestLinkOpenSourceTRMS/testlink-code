<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * IMPORTANT NOTICE:
 * Only test cases that HAVE TESTER ASSIGNED will be considered.
 *
 * @filesource	resultsByStatus.php
 * @package 	TestLink
 * @copyright 	2007-2012, TestLink community 
 * @link 		http://www.teamst.org/index.php
 *
 *
 * @internal revisions
 * @since 1.9.5
 * 
 */
require('../../config.inc.php');
require_once('common.php');
require_once('displayMgr.php');
require_once('users.inc.php');
require_once('exttable.class.php');
require_once('exec.inc.php'); // used for bug string lookup

// Time tracking
//$tstart = microtime(true);
//$chronos[] = $tstart; $tnow = end($chronos);reset($chronos);
// Memory metrics	
//$mem['usage'][] = memory_get_usage(true); $mem['peak'][] = memory_get_peak_usage(true);


$templateCfg = templateConfiguration();
$resultsCfg = config_get('results');
$statusCode = $resultsCfg['status_code'];
$args = init_args($db,$statusCode);

$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);
$tcase_mgr = new testcase($db);
$metricsMgr = new tlTestPlanMetrics($db);

$gui = initializeGui($statusCode,$args,$tplan_mgr);

$tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
$tproject_info = $tproject_mgr->get_by_id($args->tproject_id);

// Memory metrics
//$mem['usage'][] = memory_get_usage(true); $mem['peak'][] = memory_get_peak_usage(true);
//echo '<br>' . __FUNCTION__ . ' Mem:' . end($mem['usage']) . ' Peak:' . end($mem['peak']) .'<br>';

// get issue tracker config and object to manage TestLink - BTS integration 
$its = null;
$tproject_mgr = new testproject($db);
$info = $tproject_mgr->get_by_id($args->tproject_id);
$gui->bugInterfaceOn = $info['issue_tracker_enabled'];
if($info['issue_tracker_enabled'])
{
	$it_mgr = new tlIssueTracker($db);
	$its = $it_mgr->getInterfaceObject($args->tproject_id);
	unset($it_mgr);
}	


$labels = init_labels(array('deleted_user' => null, 'design' => null, 'execution' => null,
                            'execution_history' => null,'nobody' => null,
                            'th_bugs_not_linked' => null,'info_notrun_tc_report' => null));

$gui->tplan_name = $tplan_info['name'];
$gui->tproject_name = $tproject_info['name'];
$testCaseCfg = config_get('testcase_cfg');
$mailCfg = buildMailCfg($gui);

// Time tracking
//$chronos[] = microtime(true);$tnow = end($chronos);$tprev = prev($chronos);
//$t_elapsed_abs = number_format( $tnow - $tstart, 4);
//$t_elapsed = number_format( $tnow - $tprev, 4);
//echo '<br>' . __FUNCTION__ . ' Elapsed relative (sec):' . $t_elapsed . ' Elapsed ABSOLUTE (sec):' . $t_elapsed_abs .'<br>';
//reset($chronos);	

$gui->info_msg = '';
$gui->bugs_msg = '';
if( $args->type == $statusCode['not_run'] )
{
	$gui->info_msg = $labels['info_notrun_tc_report'];
	$metrics = $metricsMgr->getNotRunWithTesterAssigned($args->tplan_id,null,array('output' => 'array'));
	$notesAccessKey = 'summary';
	$userAccessKey = 'user_id';
}
else
{
	$gui->info_msg = lang_get('info_' . $resultsCfg['code_status'][$args->type] .'_tc_report');
	$metrics = $metricsMgr->getExecutionsByStatus($args->tplan_id,$args->type,null,array('output' => 'array'));
	$notesAccessKey = 'execution_notes';
	$userAccessKey='tester_id';

	$gui->bugs_msg = $labels['th_bugs_not_linked'];
}

// done here in order to get some config about images
$smarty = new TLSmarty();
if( !is_null($metrics) and count($metrics) > 0 )
{              

	$urlSafeString = array();  
	$urlSafeString['tprojectPrefix'] = urlencode($tproject_info['prefix']);
	$urlSafeString['basehref'] = str_replace(" ", "%20", $args->basehref);	
	  
	$out = array();
	$users = getUsersForHtmlOptions($db);
    $pathCache = $topCache = $levelCache = null;
	$nameCache = initNameCache($gui);

	$links = featureLinks($labels,$smarty->_tpl_vars['tlImages']);
	$odx = 0;
	foreach($metrics as &$exec)
	{	
		// --------------------------------------------------------------------------------------------
		// do some decode work, using caches
		if( !isset($pathCache[$exec['tcase_id']]) )
		{
			$dummy = $tcase_mgr->getPathLayered(array($exec['tcase_id']));	
			$pathCache[$exec['tcase_id']] = $dummy[$exec['tsuite_id']]['value'];
			$levelCache[$exec['tcase_id']] = $dummy[$exec['tsuite_id']]['level'];
	        $ky = current(array_keys($dummy)); 
	        $topCache[$exec['tcase_id']] = $ky;
		}
		
		// --------------------------------------------------------------------------
		// Bug processing. 
		// Remember that bugs are linked to executions NOT test case.
		// When using Platforms a Test Case can have multiple executions
		// (N on each platform).
		// --------------------------------------------------------------------------
		$bugString = '';
		if($gui->bugInterfaceOn && $exec['status'] != $statusCode['not_run']) 
		{
			$bugSet = get_bugs_for_exec($db, $its, $exec['executions_id']);
			if (count($bugSet) == 0) 
			{
				$gui->without_bugs_counter += 1;
			}
			foreach($bugSet as $bug) 
			{
				$bugString .= $bug['link_to_bts'] . '<br/>';
 			}
		}
	    // --------------------------------------------------------------------------
		
		
		// --------------------------------------------------------------------------------------------

		// IMPORTANT NOTICE:
		// When test case has been runned, version must be get from executions.tcversion_number 
		// Column ORDER IS CRITIC                       
		// suiteName
		// testTitle 	CCA-15708: RSRSR-150
		// testVersion 	1
		// buildName 	2.0
		// platformName XXXX
		// testerName 	yyyyyy
		// localizedTS 	2012-04-25 12:14:55   <<<< ONLY if executed
		// notes 	[empty string]
		// bugString 	[empty string]        <<<< ONLY if executed 
	
		$out[$odx]['suiteName'] =  $pathCache[$exec['tcase_id']];

		// --------------------------------------------------------------------------------------------
		if($args->format != FORMAT_HTML )
		{
			$out[$odx]['testTitle'] = '<a href="' . $urlSafeString['basehref'] . 
									  'linkto.php?tprojectPrefix=' . 
			          				  $urlSafeString['tprojectPrefix'] . '&item=testcase&id=' . 
			          				  urlencode($exec['full_external_id']) .'">' .
			          				  $exec['full_external_id'] . ':' . $exec['name'] . '</a>';
		}
		else
		{
		    $out[$odx]['testTitle'] = "<!-- " . sprintf("%010d", $exec['external_id']) . " -->";
			$out[$odx]['testTitle'] .= sprintf($links['full'],
											   $exec['tcase_id'],$exec['tcase_id'],$exec['tcversion_id'],
									           $exec['build_id'],$args->tplan_id,$exec['platform_id'],$exec['tcase_id']);
			$out[$odx]['testTitle'] .= $exec['full_external_id'] . ':' . $exec['name'] . '</a>';
		}
		// --------------------------------------------------------------------------------------------

		$out[$odx]['testVersion'] =  $exec['tcversion_number'];
		
		// Insert order on out is CRITIC, because order is used on buildMatrix
		if($gui->show_platforms)
		{
			$out[$odx]['platformName'] = $nameCache['platform'][$exec['platform_id']];
		}

		$out[$odx]['buildName'] = $nameCache['build'][$exec['build_id']];

		// --------------------------------------------------------------------------------------------
		// verbose user  
		if($exec[$userAccessKey] == 0 )
		{
			$out[$odx]['testerName'] = $labels['nobody'];
		}
		else
		{
			if(isset($users,$exec[$userAccessKey]))
			{
			   $out[$odx]['testerName'] = htmlspecialchars($users[$exec[$userAccessKey]]);
			}
			else
			{
			    // user id has been disable/deleted
			    $out[$odx]['testerName'] = sprintf($labels['deleted_user'],$exec[$userAccessKey]);
			}
		}
		$out[$odx]['testerName'] = htmlspecialchars($out[$odx]['testerName']);
		// --------------------------------------------------------------------------------------------

		if( $args->type != $statusCode['not_run'] )
		{
			$out[$odx]['localizedTS'] = $exec['execution_ts'];
		}
		$out[$odx]['notes'] = strip_tags($exec[$notesAccessKey]);

		if( $args->type != $statusCode['not_run'] )
		{
			$out[$odx]['bugString'] = $bugString;
		}
	
   	    $odx++;
	}
	$gui->dataSet = $out;
	unset($out);
}
else
{
    $gui->warning_msg = getWarning($args->type,$statusCode);
}	



// Time tracking
//$chronos[] = microtime(true);$tnow = end($chronos);$tprev = prev($chronos);
//$t_elapsed_abs = number_format( $tnow - $tstart, 4);
//$t_elapsed = number_format( $tnow - $tprev, 4);
//echo '<br>' . __FUNCTION__ . ' Elapsed relative (sec):' . $t_elapsed . ' Elapsed ABSOLUTE (sec):' . $t_elapsed_abs .'<br>';
//reset($chronos);	
//$mem['usage'][] = memory_get_usage(true); $mem['peak'][] = memory_get_peak_usage(true);
//echo '<br>' . __FUNCTION__ . ' Mem:' . end($mem['usage']) . ' Peak:' . end($mem['peak']) .'<br>';


$tableOptions = array('status_not_run' => ($args->type == $statusCode['not_run']),
                      'bugInterfaceOn' => $gui->bugInterfaceOn,
                      'format' => $args->format,
                      'show_platforms' => $gui->show_platforms);

$gui->tableSet[] = buildMatrix($gui->dataSet, $args, $tableOptions ,$gui->platformSet);

// Time tracking
//$chronos[] = microtime(true);$tnow = end($chronos);$tprev = prev($chronos);
//$t_elapsed_abs = number_format( $tnow - $tstart, 4);
//$t_elapsed = number_format( $tnow - $tprev, 4);
//echo '<br>' . __FUNCTION__ . ' Elapsed relative (sec):' . $t_elapsed . ' Elapsed ABSOLUTE (sec):' . $t_elapsed_abs .'<br>';
//reset($chronos);	

$smarty = new TLSmarty();
$smarty->assign('gui', $gui );
displayReport($templateCfg->template_dir . $templateCfg->default_template, $smarty, $args->format,$mailCfg);


// Time tracking
//$chronos[] = microtime(true);$tnow = end($chronos);$tprev = prev($chronos);
//$t_elapsed_abs = number_format( $tnow - $tstart, 4);
//$t_elapsed = number_format( $tnow - $tprev, 4);
//echo '<br>' . __FUNCTION__ . ' Elapsed relative (sec):' . $t_elapsed . ' Elapsed ABSOLUTE (sec):' . $t_elapsed_abs .'<br>';
//reset($chronos);	
//$mem['usage'][] = memory_get_usage(true); $mem['peak'][] = memory_get_peak_usage(true);
//echo '<br>' . __FUNCTION__ . ' Mem:' . end($mem['usage']) . ' Peak:' . end($mem['peak']) .'<br>';


/**
 * 
 *
 */
function init_args(&$dbHandler,$statusCode)
{
    $iParams = array("apikey" => array(tlInputParameter::STRING_N,32,32),
                     "tproject_id" => array(tlInputParameter::INT_N), 
		                 "tplan_id" => array(tlInputParameter::INT_N),
                     "format" => array(tlInputParameter::INT_N),
    	               "type" => array(tlInputParameter::STRING_N,0,1));
	$args = new stdClass();
	R_PARAMS($iParams,$args);


  if( !is_null($args->apikey) )
  {
    $cerbero = new stdClass();
    $cerbero->args = new stdClass();
    $cerbero->args->tproject_id = $args->tproject_id;
    $cerbero->args->tplan_id = $args->tplan_id;
    $cerbero->args->getAccessAttr = true;
    $cerbero->method = 'checkRights';
    setUpEnvForRemoteAccess($dbHandler,$args->apikey,$cerbero);  
  }
  else
  {
    testlinkInitPage($dbHandler,true,false,"checkRights");  
	  $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
  }
	
	$args->user = $_SESSION['currentUser'];
	$args->basehref = $_SESSION['basehref'];
	return $args;
}

/**
 * initializeGui
 *
 */
function initializeGui($statusCode,&$argsObj,&$tplanMgr)
{
    $guiObj = new stdClass();
    
    // Count for the Failed Issues whose bugs have to be raised/not linked. 
    $guiObj->without_bugs_counter = 0; 
    $guiObj->dataSet = null;
    $guiObj->title = null;
    $guiObj->type = $argsObj->type;
    $guiObj->warning_msg = '';

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

		
	// needed to decode
	$getOpt = array('outputFormat' => 'map');
	$guiObj->platformSet = $tplanMgr->getPlatforms($argsObj->tplan_id,$getOpt);
	if( !($guiObj->show_platforms = !is_null($guiObj->platformSet)) )
	{
		$guiObj->platformSet = array('');
	}

	$guiObj->buildSet = $tplanMgr->get_builds_for_html_options($argsObj->tplan_id);
    
  return $guiObj;    
}



function checkRights(&$db,&$user,$context = null)
{
  if(is_null($context))
  {
    $context = new stdClass();
    $context->tproject_id = $context->tplan_id = null;
    $context->getAccessAttr = false; 
  }
  $check = $user->hasRight($db,'testplan_metrics',$context->tproject_id,$context->tplan_id,$context->getAccessAttr);
	return $check;
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
function buildMatrix($dataSet, &$args, $options = array(), $platforms)
{
	$default_options = array('bugInterfaceOn' => false,'show_platforms' => false,
							 'status_not_run' => false,'format' => FORMAT_HTML);
	$options = array_merge($default_options, $options);

	$l18n = init_labels(array('assigned_to' => null,'platform' => null, 'th_date' => null,
						      'th_build' => null));



	$columns = array();
	$columns[] = array('title_key' => 'title_test_suite_name', 'width' => 80, 'type' => 'text');
	$columns[] = array('title_key' => 'title_test_case_title', 'width' => 80, 'type' => 'text');
	$columns[] = array('title_key' => 'version', 'width' => 30);
	if ($options['show_platforms'])
	{
		$columns[] = array('title_key' => 'platform', 'width' => 60, 'filter' => 'list', 'filterOptions' => $platforms);
	}
	if( $options['status_not_run'] )
	{
		$columns[] = array('title_key' => 'th_build', 'width' => 35);
		$columns[] = array('title_key' => 'assigned_to', 'width' => 60);
		$columns[] = array('title_key' => 'summary', 'width' => 150, 'type' => 'text');
	}
	else
	{
		$columns[] = array('title_key' => 'th_build', 'width' => 35);
		$columns[] = array('title_key' => 'th_run_by', 'width' => 60);
		$columns[] = array('title_key' => 'th_date', 'width' => 60);
		$columns[] = array('title_key' => 'title_execution_notes', 'width' => 150, 'type' => 'text');
		if ($options['bugInterfaceOn'])
		{
			$columns[] = array('title_key' => 'th_bugs', 'type' => 'text');
		}
	}


	if ($options['format'] == FORMAT_HTML)
	{

		// IMPORTANT DEVELOPMENT NOTICE
		// columns and dataSet are deeply related this means that inside
		// dataSet order has to be identical that on columns or table will be a disaster
		//
		$matrix = new tlExtTable($columns, $dataSet, 'tl_table_results_by_status');
		
		//if not run report: sort by test suite
		//blocked, failed report: sort by platform (if enabled) else sort by date
		$sort_name = 0;
		if ($options['status_not_run']) 
		{
			$sort_name = $l18n['assigned_to'];
		} 
		else 
		{
			$sort_name = $options['show_platforms'] ? $l18n['platform'] : $l18n['th_date'];
		}
		
		$matrix->setSortByColumnName($sort_name);
		$matrix->setGroupByColumnName($l18n['th_build']);

		$matrix->addCustomBehaviour('text', array('render' => 'columnWrap'));
		
		//define table toolbar
		$matrix->showToolbar = true;
		$matrix->toolbarExpandCollapseGroupsButton = true;
		$matrix->toolbarShowAllColumnsButton = true;
	}
	else
	{
		$matrix = new tlHTMLTable($columns, $dataSet, 'tl_table_results_by_status');
	}
	return $matrix;
}



function featureLinks($lbl,$img)
{
	$links = array();

	// %s => test case id
	$links['exec_history'] = '<a href="javascript:openExecHistoryWindow(%s);" >' .
			                 '<img title="' . $lbl['execution_history'] . '" ' .
			                 'src="' . $img['history_small'] . '" /></a> ';

	// tcase_id,tcversion_id,build_id,tplan_id,platform_id
	$links['exec'] = '<a href="javascript:openExecutionWindow(%s,%s,%s,%s,%s);" >' .
				     '<img title="' . $lbl['execution'] .'" ' .
				     'src="' . $img['exec_icon'] . '" /></a> ';

	// %s => test case id
	$links['edit'] = '<a href="javascript:openTCEditWindow(%s);" >' .
					'<img title="' . $lbl['design'] . '" '. 
				    'src="' . $img['edit_icon'] . '" /></a> ';


	$links['full'] = $links['exec_history'] . $links['exec'] . $links['edit'];

	return $links;
}



function initNameCache($guiObj)
{
	$safeItems = array('build' => null, 'platform' => null);

	foreach($guiObj->buildSet as $id => $name)
	{
		$safeItems['build'][$id] = htmlspecialchars($name);	
	}

	if($guiObj->show_platforms)
	{
		foreach($guiObj->platformSet as $id => $name)
		{
			$safeItems['platform'][$id] = htmlspecialchars($name);	
		}
	}	
	
	return $safeItems;
}


function getWarning($targetStatus,$statusCfg)
{
	$msg = ''; 
	$key2check = array('not_run','failed','blocked');
	foreach($key2check as $statusVerbose)
	{
		if( $targetStatus == $statusCfg[$statusVerbose] )
		{         
			$msg = lang_get('no_' . $statusVerbose . '_with_tester');
			break;
		}
	}
	return $msg;
}	
?>