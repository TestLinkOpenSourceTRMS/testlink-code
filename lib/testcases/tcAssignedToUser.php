<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource  tcAssignedToUser.php
 * @author Francisco Mancardi - francisco.mancardi@gmail.com
 * 
 * @internal revisions
 * @since 1.9.15
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once("exttable.class.php");

testlinkInitPage($db);
$templateCfg = templateConfiguration();

$smarty = new TLSmarty();
$imgSet = $smarty->getImages();

$args = init_args($db);
$gui = initializeGui($db,$args);
$statusGui = getStatusGuiCfg();


// Get all test cases assigned to user without filtering by execution status
$opt = array('mode' => 'full_path');
$filters = initFilters($args);
$tplan_param = ($args->tplan_id) ? array($args->tplan_id) : testcase::ALL_TESTPLANS;

$tcase_mgr = new testcase($db);
$gui->resultSet = $tcase_mgr->get_assigned_to_user($args->user_id, $args->tproject_id,
                                                   $tplan_param, $opt, $filters);

$doIt = !is_null($gui->resultSet);

// will work only on standard exec status
$exec = getQuickExecCfg($gui,$imgSet,$statusGui->status_code);

$tables = tlObjectWithDB::getDBTables(array('nodes_hierarchy','executions','tcversions'));

if($args->result != '' && $args->tcvx > 0)
{

  // get version number
  $sql =  " SELECT TCV.version FROM  {$tables['tcversions']} TCV WHERE TCV.id = " . $args->tcvx;
  $xx = $db->get_recordset($sql);
  $version_number = $xx[0]['version'];

  $sql = " INSERT INTO {$tables['executions']} ".
         " (status,tester_id,execution_ts,tcversion_id,tcversion_number,testplan_id,platform_id,build_id)".
         " VALUES ('{$args->result}', {$args->executedBy}, " . $db->db_now() . "," .
         "         {$args->tcvx}, {$version_number}, {$args->tpx}, {$args->pxi},{$args->bxi})";

  $db->exec_query($sql);
}  


if( $doIt )
{   
  $execCfg = config_get('exec_cfg');

  // has logged user right to execute test cases on this test plan?
  $hasExecRight = 
    $_SESSION['currentUser']->hasRight($db,'testplan_execute',null,$args->tplan_id);

  $tables = tlObjectWithDB::getDBTables(array('nodes_hierarchy'));
  $tplanSet=array_keys($gui->resultSet);
  $sql="SELECT name,id FROM {$tables['nodes_hierarchy']} " .
       "WHERE id IN (" . implode(',',$tplanSet) . ")";
  $gui->tplanNames=$db->fetchRowsIntoMap($sql,'id');
  $optColumns = array('user' => $args->show_user_column, 'priority' => $args->priority_enabled);

  $whoiam = $args->show_all_users ? 'tcAssignedToUser': 'tcAssignedToMe';

  foreach ($gui->resultSet as $tplan_id => $tcase_set) 
  {
    list($columns,$sortByColumn,$show_platforms) = getColumnsDefinition($db,$tplan_id,$optColumns);
    
    $rows = array();

    foreach ($tcase_set as $tcase_platform) 
    {

      foreach ($tcase_platform as $tcase) 
      {
      	$current_row = array();
      	$tcase_id = $tcase['testcase_id'];
      	$tcversion_id = $tcase['tcversion_id'];
      	
      	if ($args->show_user_column) 
      	{
          if($tcase['user_id'] > 0 &&  isset($args->userSet[$tcase['user_id']]))
          {
            $current_row[] = htmlspecialchars($args->userSet[$tcase['user_id']]['login']);
          }
          else
          {
            $current_row[] = '';
          }  
      	}
      
      	$current_row[] = htmlspecialchars($tcase['build_name']);
      	$current_row[] = htmlspecialchars($tcase['tcase_full_path']);

        // create linked icons
        $ekk = $elk = $exec_link = '';
        $canExec = $hasExecRight == 'yes';
        if($execCfg->exec_mode->tester == 'assigned_to_me')
        {
          $canExec = $canExec && ($tcase['user_id'] == $_SESSION['userID']);
        }  

        if($canExec)
        {  
          $ekk = sprintf($exec['common'],$tplan_id,$tcase['platform_id'],$tplan_id,$tcase['build_id'],
                         $tplan_id,$tcversion_id,$tplan_id);
          
          $elk = sprintf($exec['passed'],$tplan_id) . $ekk . '&nbsp;' . sprintf($exec['failed'],$tplan_id ) . $ekk . '&nbsp;' . 
                 sprintf($exec['blocked'],$tplan_id) . $ekk;

          $exec_link = "<a href=\"javascript:openExecutionWindow(" .
                       "{$tcase_id},{$tcversion_id},{$tcase['build_id']}," .
                       "{$tcase['testplan_id']},{$tcase['platform_id']},'{$whoiam}');\">" .
                       "<img title=\"{$gui->l18n['execution']}\" src=\"{$imgSet['exec_icon']}\" /></a> ";
        }
       
        $exec_history_link = "<a href=\"javascript:openExecHistoryWindow({$tcase_id});\">" .
                             "<img title=\"{$gui->l18n['execution_history']}\" src=\"{$imgSet['history_small']}\" /></a> ";
        
        
        $edit_link = "<a href=\"javascript:openTCEditWindow({$tcase_id});\">" .
                     "<img title=\"{$gui->l18n['design']}\" src=\"{$imgSet['edit_icon']}\" /></a> ";
        
        $current_row[] = "<!-- " . sprintf("%010d", $tcase['tc_external_id']) . " -->" . $elk . $exec_history_link .
                         $exec_link . $edit_link . htmlspecialchars($tcase['prefix']) . $gui->glueChar . 
                         $tcase['tc_external_id'] . " : " . htmlspecialchars($tcase['name']) .
                         sprintf($gui->l18n['tcversion_indicator'],$tcase['version']);

        if ($show_platforms)
        {
          $current_row[] = htmlspecialchars($tcase['platform_name']);
        }
        
        if ($args->priority_enabled) 
        {
          $current_row[] = "<!-- " . $tcase['priority'] . " -->" . $gui->priority[priority_to_level($tcase['priority'])];
        }
        
        $leOptions = array('getSteps' => 0);
        $lexec = $tcase_mgr->get_last_execution($tcase_id, $tcversion_id, $tplan_id, 
                                                $tcase['build_id'],$tcase['platform_id'],
                                                $leOptions);
        $status = $lexec[$tcversion_id]['status'];
        if (!$status) 
        {
          $status = $statusGui->status_code['not_run'];
        }
        $current_row[] = $statusGui->definition[$status];

        if ($args->show_user_column) 
        {
            $current_row[] = htmlspecialchars($lexec[$tcversion_id]['tester_login']);
        }


                    
        // need to check if we are using the right timestamp                      
        $current_row[] = htmlspecialchars($tcase['creation_ts']) . 
                         " (" . get_date_diff($tcase['creation_ts']) . ")";
        
        $rows[] = $current_row;
			}
		}
		
		/* different table id for different reports:
		 * - Assignment Overview if $args->show_all_users is set
		 * - Test Cases assigned to user if $args->build_id > 0
		 * - Test Cases assigned to me else
		 */
		$table_id = "tl_table_tc_assigned_to_me_for_tplan_";
		if($args->show_all_users) {
			$table_id = "tl_table_tc_assignment_overview_for_tplan_";
		}
		if($args->build_id) {
			$table_id = "tl_table_tc_assigned_to_user_for_tplan_";
		}
		
		// add test plan id to table id
		$table_id .= $tplan_id;
		
		$matrix = new tlExtTable($columns, $rows, $table_id);
		$matrix->title = $gui->l18n['testplan'] . ": " . htmlspecialchars($gui->tplanNames[$tplan_id]['name']);
		
		// default grouping by first column, which is user for overview, build otherwise
		$matrix->setGroupByColumnName(lang_get($columns[0]['title_key']));
		
		// make table collapsible if more than 1 table is shown and surround by frame
		if (count($tplanSet) > 1) {
			$matrix->collapsible = true;
			$matrix->frame = true;
		}
		
		// define toolbar
		$matrix->showToolbar = true;
		$matrix->toolbarExpandCollapseGroupsButton = true;
		$matrix->toolbarShowAllColumnsButton = true;
		
		$matrix->setSortByColumnName($sortByColumn);
		$matrix->sortDirection = 'DESC';
		$gui->tableSet[$tplan_id] = $matrix;
	}
}

$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * Replacement for the smarty helper function to get that functionality outside of templates.
 * Returns difference between a given date and the current time in days.
 * @author Andreas Simon
 * @param $date
 */
function get_date_diff($date) 
{
	$date = (is_string($date)) ? strtotime($date) : $date;
	$i = 1/60/60/24;
	return floor((time() - $date) * $i);
}


/**
 * init_args()
 * Get in an object all data that has arrived to page through _REQUEST or _SESSION.
 * If you think this page as a function, you can consider this data arguments (args)
 * to a function call.
 * Using all this data as one object property will help developer to understand
 * if data is received or produced on page.
 *
 * @author franciscom - francisco.mancardi@gmail.com
 * @args - used global coupling accessing $_REQUEST and $_SESSION
 * 
 * @return object of stdClass
 *
 * @internal revisions
 */
function init_args(&$dbHandler)
{
  $_REQUEST = strings_stripSlashes($_REQUEST);

  $args = new stdClass();
  
  $args->tproject_id = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0;
  if( $args->tproject_id == 0)
  {
    $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
  }  
  if( $args->tproject_id == 0)
  {
    throw new Exception(__FILE__ . ' Can not work without Test project ID => Aborting');
  }
  $mgr = new testproject($dbHandler);
  $info = $mgr->get_by_id($args->tproject_id);
  $args->tproject_name = $info['name'];
  $args->testprojectOptions = $info['opt'];
  unset($info);

  $args->user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
  
  if( $args->user_id != 0)
  {
    $args->user = new tlUser($args->user_id);
    $args->user->readFromDB($dbHandler); 
  }
  else 
  {
    $args->user_id = isset($_SESSION['userID']) ? intval($_SESSION['userID']) : 0;
    if( $args->user_id == 0)
    {
      throw new Exception(__FILE__ . ' Can not work without User ID => Aborting');
    }
    $args->user = $_SESSION['currentUser'];
  }	


  $args->executedBy = $args->user_id;
  $args->user_name = $args->user->login;
  $args->userSet =  $args->user->getNames($dbHandler);                  

  $args->tplan_id = isset($_REQUEST['tplan_id']) ? intval($_REQUEST['tplan_id']) : 0;
  $args->build_id = isset($_REQUEST['build_id']) && is_numeric($_REQUEST['build_id']) ? intval($_REQUEST['build_id']) : 0;

  $args->show_inactive_tplans = isset($_REQUEST['show_inactive_tplans']) ? true : false;

  $args->show_all_users = false;
  if(isset($_REQUEST['show_all_users']))
  {
    $args->show_all_users = (intval($_REQUEST['show_all_users']) == 1);
  }
  $args->show_user_column = $args->show_all_users; 


  $show_closed_builds = isset($_REQUEST['show_closed_builds']) ? true : false;
	$show_closed_builds_hidden = isset($_REQUEST['show_closed_builds_hidden']) ? true : false;
	if ($show_closed_builds) 
  {
		$selection = true;
	} 
  else if ($show_closed_builds_hidden) 
  {
		$selection = false;
	} 
  else if (isset($_SESSION['show_closed_builds'])) 
  {
		$selection = intval($_SESSION['show_closed_builds']);
	} 
  else 
  {
		$selection = false;
	}
	$args->show_closed_builds = $_SESSION['show_closed_builds'] = $selection;

	if ($args->show_all_users) 
  {
		$args->user_id = TL_USER_ANYBODY;
	}
	
  $args->show_inactive_and_closed = false;	
  if( isset($_REQUEST['show_inactive_and_closed']) )
  {
    $args->show_inactive_and_closed = (intval($_REQUEST['show_inactive_and_closed']) != 0);
  }

	$args->priority_enabled = $_SESSION['testprojectOptions']->testPriorityEnabled ? true : false;


  // quick & dirty execution
  $args->tpx = isset($_REQUEST['tpx']) ? intval($_REQUEST['tpx']) : 0;
  $dirtyHarry = array('pxi','bxi','tcvx');
  foreach($dirtyHarry as $tg)
  {
    $key = $tg . '_' . $args->tpx;
    $args->$tg = isset($_REQUEST[$key]) ? intval($_REQUEST[$key]) : 0;
  }  
	$args->result = isset($_REQUEST['result_' .  $args->tpx]) ? $_REQUEST['result_' .  $args->tpx][0] : '';

	return $args;
}


/**
 * get Columns definition for table to display
 *
 */
function getColumnsDefinition($dbHandler,$tplan_id,$optionalColumns)
{
  static $labels;
  static $tplan_mgr;
  if( is_null($labels) )
  {
    $tplan_mgr = new testplan($dbHandler);

    $lbl2get = array('build' => null,'testsuite' => null,'testcase' => null,'platform' => null,
                     'user' => null, 'priority' => null,'status' => null, 'version' => null, 
                     'low_priority' => null,'medium_priority' => null,'high_priority' => null,
                     'due_since' => null);
    $labels = init_labels($lbl2get);
  }

  $colDef = array();
  $sortByCol = $labels['testsuite'];
  
  // user column is only shown for assignment overview
  if ($optionalColumns['user']) 
  {
    $colDef[] = array('title_key' => 'user', 'width' => 80);
    $sortByCol = $labels['build'];
  }
  
  $colDef[] = array('title_key' => 'build', 'width' => 80);
  $colDef[] = array('title_key' => 'testsuite', 'width' => 130);
  $colDef[] = array('title_key' => 'testcase', 'width' => 130);

  $platforms = $tplan_mgr->getPlatforms($tplan_id,array('outputFormat' => 'map'));
  if( ($show_plat = !is_null($platforms)) )
  {
    $colDef[] = array('title_key' => 'platform', 'width' => 50, 'filter' => 'list', 'filterOptions' => $platforms);
  }
  
  if ($optionalColumns['priority']) 
  {
    $sortByCol = $labels['priority'];
    $colDef[] = array('title_key' => 'priority', 'width' => 50, 'filter' => 'ListSimpleMatch', 
                      'filterOptions' => array($labels['low_priority'],$labels['medium_priority'],$labels['high_priority']));
  }
  
  $colDef[] = array('title_key' => 'status', 'width' => 50, 'type' => 'status');
  if($optionalColumns['user'])
  {
    $colDef[] = array('title_key' => 'tester', 'width' => 80);
  }  
  

  $colDef[] = array('title_key' => 'due_since', 'width' => 100);
  
  return array($colDef, $sortByCol, $show_plat);
}


function initializeGui(&$dbHandler,$argsObj)
{
  $gui = new stdClass();
  $gui->tproject_name = $argsObj->tproject_name;

  // disable "show also closed builds" checkbox when a specific build is selected
  $gui->show_build_selector = ($argsObj->build_id == 0);
  $gui->show_closed_builds = $argsObj->show_closed_builds;

  $gui->glueChar = config_get('testcase_cfg')->glue_character;
  $gui->warning_msg = '';
  $gui->tableSet = null;
  $gui->l18n = init_labels(array('tcversion_indicator' => null,'goto_testspec' => null, 'version' => null, 
                                 'testplan' => null, 'assigned_tc_overview' => null,
                                 'testcases_assigned_to_user' => null,
                                 'quick_passed' => null, 'quick_failed' => null,'quick_blocked' => null,
                                 'low_priority' => null,'medium_priority' => null,'high_priority' => null,
                                 'design' => null, 'execution' => null, 'execution_history' => null));

  $gui->priority = array(LOW => $gui->l18n['low_priority'],MEDIUM => $gui->l18n['medium_priority'],
                         HIGH => $gui->l18n['high_priority']);

  if ($argsObj->show_all_users) 
  {
    $gui->pageTitle=sprintf($gui->l18n['assigned_tc_overview'], $gui->tproject_name);
  } 
  else 
  {
    $gui->pageTitle=sprintf($gui->l18n['testcases_assigned_to_user'],$gui->tproject_name, $argsObj->user_name);
  }

  $gui->user_id = $argsObj->user_id;
  $gui->tplan_id = $argsObj->tplan_id;

  $gui->directLink = $_SESSION['basehref'] . 
                     'ltx.php?item=xta2m&user_id=' . $gui->user_id .
                     '&tplan_id=' . $gui->tplan_id;

  return $gui;  
}


function initFilters($argsObj)
{
  $filters = array();
  
  $filters['tplan_status'] = $argsObj->show_inactive_tplans ? 'all' : 'active';
  $filters['build_status'] = $argsObj->show_closed_builds ? 'all' : 'open';
  
  if ($argsObj->build_id) 
  {
    $filters['build_id'] = $argsObj->build_id;
    
    // show assignments regardless of build and tplan status
    $filters['build_status'] = 'all';
    $filters['tplan_status'] = 'all';
  }
  return $filters;
}

function getStatusGuiCfg()
{
  $cfg = config_get('results');

  $ret = new stdClass();
  $ret->status_code = $cfg['status_code'];
  $ret->code_css = array();
  $ret->definition = array();
  
  foreach($cfg['code_status'] as $code => $status) 
  {
    if (isset($cfg['status_label'][$status])) 
    {
      $label = $cfg['status_label'][$status];
      $ret->code_css[$code] = array();
      $ret->code_css[$code]['translation'] = lang_get($label);
      $ret->code_css[$code]['css_class'] = $cfg['code_status'][$code] . '_text';
      $ret->definition[$code] = array("value" => $code,
                                      "text" => $ret->code_css[$code]['translation'],
                                      "cssClass" => $ret->code_css[$code]['css_class']);
    }
  }
  return $ret;
}

/**
 * ATTENTION: xx.value is strongly related to HTML input names on tcAssignedToUser.tpl
 */
function getQuickExecCfg($gui,$imgSet,$statusCode)
{
  $qexe['passed'] = "<img title=\"{$gui->l18n['quick_passed']}\" src=\"{$imgSet['exec_passed']}\" " .
                    " onclick=\"result_%s.value='{$statusCode['passed']}';"; 


  $qexe['failed'] = "<img title=\"{$gui->l18n['quick_failed']}\" src=\"{$imgSet['exec_failed']}\" " .
                    " onclick=\"result_%s.value='{$statusCode['failed']}';"; 

  $qexe['blocked'] = "<img title=\"{$gui->l18n['quick_blocked']}\" src=\"{$imgSet['exec_blocked']}\" " .
                    " onclick=\"result_%s.value='{$statusCode['blocked']}';"; 

  $qexe['common'] = 'pxi_%s.value=%s;bxi_%s.value=%s;tcvx_%s.value=%s;fog_%s.submit();" /> ';

  return $qexe;  
}
