<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 *
 * @filesource	resultsByStatus.php
 * @package 	TestLink
 * @copyright 	2007-2012, TestLink community 
 * @link 		http://www.teamst.org/index.php
 *
 *
 * @internal revisions
 * @since 1.9.4
 *
 * @since 1.9.3
 *  20101013 - asimon - use linkto.php for emailed links
 *  20101012 - Julian - added html comment to properly sort by test case column
 *  20101007 - asimon - BUGID 3857: Replace linked icons in reports if reports get sent by e-mail
 *  20100927 - asimon - added mouseover information for the exec and edit icons
 *  20100923 - eloff - refactored to use improved table interface
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


// NEED TO BE REFACTORED
// Probably will not be used anymore
if (config_get('interface_bugs') != 'NO')
{
  require_once(TL_ABS_PATH. 'lib' . DIRECTORY_SEPARATOR . 'bugtracking' .
               DIRECTORY_SEPARATOR . 'int_bugtracking.php');
}
testlinkInitPage($db,true,false,"checkRights");

$templateCfg = templateConfiguration();
$resultsCfg = config_get('results');
$statusCode = $resultsCfg['status_code'];

$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);
$tcase_mgr = new testcase($db);
$metricsMgr = new tlTestPlanMetrics($db);

$args = init_args($statusCode);
$gui = initializeGui($statusCode,$args,$tplan_mgr);

$tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
$tproject_info = $tproject_mgr->get_by_id($args->tproject_id);

// Memory metrics
//$mem['usage'][] = memory_get_usage(true); $mem['peak'][] = memory_get_peak_usage(true);
//echo '<br>' . __FUNCTION__ . ' Mem:' . end($mem['usage']) . ' Peak:' . end($mem['peak']) .'<br>';

// TO BE REVIEWED!!!!
$gui->bugInterfaceOn = config_get('bugInterfaceOn');
$bugInterface = null;
if ($gui->bugInterfaceOn) 
{
	$bugInterface = config_get('bugInterface');
}

$labels = init_labels(array('deleted_user' => null, 'design' => null, 'execution' => null,
                            'execution_history' => null,'nobody' => null));

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


if( $args->type == $statusCode['not_run'] )
{
	$metrics = $metricsMgr->getNotRunWithTesterAssigned($args->tplan_id,null,array('output' => 'array'));
	$notesAccessKey = 'summary';
	$userAccessKey = 'user_id';
}
else
{
	$metrics = $metricsMgr->getExecutionsByStatus($args->tplan_id,$args->type,null,array('output' => 'array'));
	$notesAccessKey = 'execution_notes';
	$userAccessKey='tester_id';
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
    $pathCache=null;
    $topCache=null;
    $levelCache=null;
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
		$out[$odx]['buildName'] = $nameCache['build'][$exec['build_id']];
		if($gui->show_platforms)
		{
			$out[$odx]['platformName'] = $nameCache['platform'][$exec['platform_id']];
		}

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
			$out[$odx]['bugString'] = '';
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
function init_args($statusCode)
{
    $iParams = array("format" => array(tlInputParameter::INT_N),
		             "tplan_id" => array(tlInputParameter::INT_N),
    	             "type" => array(tlInputParameter::STRING_N,0,1));
	$args = new stdClass();
	R_PARAMS($iParams,$args);
	
	$args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
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
	if($targetStatus == $statusCfg['not_run']) 
	{
		$msg = lang_get('no_notrun');
	}
	if($targetStatus == $statusCfg['failed']) 
	{
		$msg = lang_get('no_failed');
	}
	if($targetStatus == $statusCfg['blocked']) 
	{
		$msg = lang_get('no_blocked');
	}
	return $msg;
}	

?>