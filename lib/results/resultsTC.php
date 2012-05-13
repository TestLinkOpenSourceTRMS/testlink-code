<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsTC.php,v 1.79 2010/11/01 17:14:48 franciscom Exp $ 
*
* @author	Martin Havlat <havlat@users.sourceforge.net>
* @author 	Chad Rosen
* 
* Show Test Report by individual test case.
*
* @internal revisios
* @since 1.9.4
* 20120513 - franciscom - TICKET 5016: Reports - Test result matrix - Refactoring
*
* @since 1.9.3
* 20110512 - Julian - BUGID 4451 - remove version tag from not run test cases as the shown version
*                                  is only taken from previous build and might not be right
* 20110329 - Julian - BUGID 4341 - added "Last Execution" column
*
*/
require('../../config.inc.php');
require_once('common.php');
require_once('displayMgr.php');
require_once('exttable.class.php');

$timerOn = microtime(true);   // will be used to compute elapsed time
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();

$metricsMgr = new tlTestPlanMetrics($db);
$tplan_mgr  = &$metricsMgr; // displayMemUsage('START' . __FILE__);

$args = init_args();
list($gui,$tproject_info,$labels,$cfg) = initializeGui($db,$args,$tplan_mgr);

$tprojectOpt = $_SESSION['testprojectOptions'];
$buildIDSet = null;
$testCaseCfg = config_get('testcase_cfg');
$testCasePrefix = $tproject_info['prefix'] . $testCaseCfg->glue_character;;
unset($testCaseCfg);

$mailCfg = buildMailCfg($gui); //displayMemUsage('Before getExecStatusMatrix()');

$execStatus = $metricsMgr->getExecStatusMatrix($args->tplan_id);
$metrics = $execStatus['metrics'];
$latestExecution = $execStatus['latestExec']; //displayMemUsage('Before UNSET');
unset($execStatus); // displayMemUsage('AFTER UNSET');

if ($gui->buildInfoSet)
{
	$buildIDSet = array_keys($gui->buildInfoSet);
}
$last_build = end($buildIDSet);

// Every Test suite a row on matrix to display will be created
// One matrix will be created for every platform that has testcases
if( ($show_platforms = !is_null($gui->platforms)) )
{
	$cols = array_flip(array('tsuite', 'link', 'platform', 'priority'));
}
else
{
	$cols = array_flip(array('tsuite', 'link', 'priority'));
}


if( !is_null($metrics) )
{
	// invariant pieces	=> avoid wasting time on loops
	$dlink = '<a href="' . str_replace(" ", "%20", $args->basehref) . 'linkto.php?tprojectPrefix=' . 
			 urlencode($tproject_info['prefix']) . '&item=testcase&id=';	

	$hist_img_tag = '<img title="' . $labels['history'] . '"' . ' src="' . $gui->img->history . '" /></a> ';
	$edit_img_tag = '<img title="' . $labels['design'] . '"' . ' src="' . $gui->img->edit . '" /></a> ';
	// ----------------------------------------------------------------------------------------------

	$tsuiteSet = array_keys($metrics);
	foreach($tsuiteSet as $tsuiteID)
	{
		$tcaseSet = array_keys($metrics[$tsuiteID]);
		foreach($tcaseSet as $tcaseID)
		{
			$platformSet = array_keys($metrics[$tsuiteID][$tcaseID]);
			foreach($platformSet as $platformID)
			{
				$rf = &$metrics[$tsuiteID][$tcaseID][$platformID];
				$rows = null;

				// some info does not change on different executions
				$build2loop = array_keys($rf);
				$top = current($build2loop);
				$external_id = $testCasePrefix . $rf[$top]['external_id'];
				$rows[$cols['tsuite']] = $rf[$top]['suiteName'];
			
				// -----------------------------------------------------------------------------------				
				// build HTML Links
			    $name = htmlspecialchars("{$external_id}:{$rf[$top]['name']}",ENT_QUOTES);
				if($args->format == FORMAT_HTML)
				{
					$rows[$cols['link']] = "<!-- " . sprintf("%010d", $rf[$top]['external_id']) . " -->" .  
										   "<a href=\"javascript:openExecHistoryWindow({$tcaseID});\">" .
				                		   $hist_img_tag .
										   "<a href=\"javascript:openTCEditWindow({$tcaseID});\">" .
				                		   $edit_img_tag . $name;
				}
				else
				{
					$rows[$cols['link']] = $dlink . urlencode($external_id) . '">' . "{$name}</a> ";
				}
				// -----------------------------------------------------------------------------------				

				if ($show_platforms)
				{
					$rows[$cols['platform']] = $gui->platforms[$platformID];
				}

				if($tprojectOpt->testPriorityEnabled) 
				{
					// is better to use code to do reorder instead of localized string ???
					$rows[$cols['priority']] = $rf[$top]['priority_level'];
				}

				// Now loop on result on each build, but following order
				$buildExecStatus = null;	
				$execOnLastBuild = null;
				foreach($buildIDSet as $buildID)
				{
					// build icon for execution link
					$r4build['text'] = "";
					if ($args->format == FORMAT_HTML) 
					{
						$r4build['text'] = "<a href=\"javascript:openExecutionWindow(" .
						             	   "{$tcaseID}, {$rf[$buildID]['tcversion_id']}, {$buildID}, " .
						             	   "{$args->tplan_id}, {$platformID});\">" .
						                   "<img title=\"{$labels['execution']}\" src=\"{$gui->img->exec}\" /></a> ";
					}
					$r4build['text'] .= $labels[$rf[$buildID]['status']] .
								  		sprintf($labels['versionTag'],$rf[$buildID]['version']);

					$r4build['value'] = $rf[$buildID]['status'];
					$r4build['cssClass'] = $gui->map_status_css[$rf[$buildID]['status']];
			
					$buildExecStatus[] = $r4build;

					if($gui->matrixCfg->buildColumns['showStatusLastExecuted'] && $last_build == $buildID)
					{
						$execOnLastBuild = $r4build;	
					}
					if( ($latestExecution[$platformID][$tcaseID]['build_id'] == $buildID) &&
					    ($latestExecution[$platformID][$tcaseID]['id'] == $rf[$buildID]['executions_id']) )
					{
						$lexec = $r4build;
					}    
				}
				
				unset($r4build);

				// Ok, now the specials
				// If configured, add column with Exec result on Latest Created Build
			    if ($gui->matrixCfg->buildColumns['showStatusLastExecuted'])
			    {
			    	$buildExecStatus[] = $execOnLastBuild;
                }
			    if ($gui->matrixCfg->buildColumns['latestBuildOnLeft']) 
			    {
			    	$buildExecStatus = array_reverse($buildExecStatus);
			    }
			    $rows = array_merge($rows, $buildExecStatus);
				
				// Always righmost column will display lastest execution result
				$rows[] = $lexec;
				
			    $gui->matrix[] = $rows;
			    unset($rows);
			    unset($buildExecStatus);
			} // $platformSet
			
		}  // $tcaseSet
	
	} // $tsuiteSet
	// new dBug($gui->matrix);
}	
unset($metrics);
unset($latestExecution);

// displayMemUsage('Before buildMatrix()');
$gui->tableSet[] =  buildMatrix($gui, $args, $last_build, $tprojectOpt);
// displayMemUsage('AFTER buildMatrix()');

$timerOff = microtime(true);
$gui->elapsed_time = round($timerOff - $timerOn,2);

$smarty = new TLSmarty;
$smarty->assign('gui',$gui);
displayReport($templateCfg->template_dir . $templateCfg->default_template, $smarty, $args->format, $mailCfg);

/**
 * 
 *
 */
function init_args()
{
	$iParams = array("format" => array(tlInputParameter::INT_N),
		             "tplan_id" => array(tlInputParameter::INT_N));

	$args = new stdClass();
	R_PARAMS($iParams,$args);
    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
	$args->basehref = $_SESSION['basehref'];
	
    return $args;
}

/**
 * 
 *
 */
function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_metrics');
}

/**
 * Builds ext-js rich table to display matrix results
 *
 *
 * return tlExtTable
 *
 */
function buildMatrix(&$guiObj,&$argsObj,$latestBuildID,$options)
{
	$columns = array(array('title_key' => 'title_test_suite_name', 'width' => 100),
	                 array('title_key' => 'title_test_case_title', 'width' => 150));

	$lbl = init_labels(array('title_test_suite_name' => null,'platform' => null,'priority' => null,
					         'result_on_last_build' => null, 'title_test_case_title' => null));
	
	$group_name = $lbl['title_test_suite_name'];

	if(!is_null($guiObj->platforms))
	{
		$columns[] = array('title_key' => 'platform', 'width' => 60, 'filter' => 'list', 
						   'filterOptions' => $guiObj->platforms);
		$group_name = $lbl['platform'];
	}
	if($options->testPriorityEnabled) 
	{
		$columns[] = array('title_key' => 'priority', 'type' => 'priority', 'width' => 40);
	}
	
	// --------------------------------------------------------------------
	$buildSet = $guiObj->buildInfoSet;
	if( $guiObj->matrixCfg->buildColumns['showStatusLastExecuted'] )
	{
		$buildSet[] = array('name' => $lbl['result_on_last_build'] . ' ' . $buildSet[$latestBuildID]['name']);
	}
	
	foreach($buildSet as $build) 
	{
		$columns[] = array('title' => $build['name'], 'type' => 'status', 'width' => 100);
	}
	// --------------------------------------------------------------------
	
	
	$columns[] = array('title_key' => 'last_execution', 'type' => 'status', 'width' => 100);
	if ($argsObj->format == FORMAT_HTML) 
	{
		$matrix = new tlExtTable($columns, $guiObj->matrix, 'tl_table_results_tc');
		
		//if platforms feature is enabled group by platform otherwise group by test suite
		$matrix->setGroupByColumnName($group_name);
		$matrix->sortDirection = 'DESC';

		if($options->testPriorityEnabled) 
		{
			$matrix->addCustomBehaviour('priority', array('render' => 'priorityRenderer', 'filter' => 'Priority'));
			$matrix->setSortByColumnName($lbl['priority']);
		} 
		else 
		{
			$matrix->setSortByColumnName($lbl['title_test_case_title']);
		}
		
		// define table toolbar
		$matrix->showToolbar = true;
		$matrix->toolbarExpandCollapseGroupsButton = true;
		$matrix->toolbarShowAllColumnsButton = true;

	} 
	else 
	{
		$matrix = new tlHTMLTable($columns, $guiObj->matrix, 'tl_table_results_tc');
	}
	unset($columns);
	
	return $matrix;
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
 * 
 *
 */
function initializeGui(&$dbHandler,&$argsObj,&$tplanMgr)
{
	
	$cfg = array('results' => config_get('results'), 'urgency' => config_get('urgency'));
	
	$guiObj = new stdClass();
	$guiObj->map_status_css = null;
	$guiObj->title = lang_get('title_test_report_all_builds');
	$guiObj->printDate = '';
	$guiObj->matrix = array();

	$guiObj->platforms = $tplanMgr->getPlatforms($argsObj->tplan_id,array('outputFormat' => 'map'));

	$guiObj->img = new stdClass();
	$guiObj->img->exec = TL_THEME_IMG_DIR . "exec_icon.png";
	$guiObj->img->edit = TL_THEME_IMG_DIR . "edit_icon.png";
	$guiObj->img->history = TL_THEME_IMG_DIR . "history_small.png";


	$tproject_mgr = new testproject($dbHandler);
	$tproject_info = $tproject_mgr->get_by_id($argsObj->tproject_id);
	unset($tproject_mgr); 

	$tplan_info = $tplanMgr->get_by_id($argsObj->tplan_id);
	$guiObj->tplan_name = $tplan_info['name'];
	$guiObj->tproject_name = $tproject_info['name'];


	$l18n = init_labels(array('design' => null, 'execution' => null, 'history' => 'execution_history',
							  'result_on_last_build' => null, 'versionTag' => 'tcversion_indicator') );

	$l18n['not_run']=lang_get($cfg['results']['status_label']['not_run']);


	$guiObj->buildInfoSet = $tplanMgr->get_builds($argsObj->tplan_id, testplan::ACTIVE_BUILDS); 
	$guiObj->matrixCfg  = config_get('resultMatrixReport');

	// hmm need to understand if this can be removed
	if ($guiObj->matrixCfg->buildColumns['latestBuildOnLeft'])
	{
		$guiObj->buildInfoSet = array_reverse($guiObj->buildInfoSet);
	}
	// -------------------------------------------------------------------------------


	foreach($cfg['results']['code_status'] as $code => $verbose)
	{
	  if( isset($cfg['results']['status_label'][$verbose]))
	  {
	    $l18n[$code] = lang_get($cfg['results']['status_label'][$verbose]);
	    $guiObj->map_status_css[$code] = $cfg['results']['code_status'][$code] . '_text';
	  }
	}
	
	return array($guiObj,$tproject_info,$l18n,$cfg);
}
?>