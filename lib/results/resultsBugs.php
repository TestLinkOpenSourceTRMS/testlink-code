<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource resultsBugs.php
 * @author kevinlevy
 * 
 * @internal revisions
 * @since 1.9.13
 */
require('../../config.inc.php');
require_once('common.php');
require_once("lang_api.php");
require_once('displayMgr.php');
require_once('exec.inc.php'); // used for bug string lookup
require_once('exttable.class.php');


testlinkInitPage($db,true,false,"checkRights");
$gui = new stdClass();
$gui->warning_msg = '';
$gui->tableSet = null;

$templateCfg = templateConfiguration();
$args = init_args();

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

$smarty = new TLSmarty;
$img = $smarty->getImages();
$openBugs = array();
$resolvedBugs = array();
$arrData = array();

$tplan_mgr = new testplan($db);
$metricsMgr = new tlTestPlanMetrics($db);
$tproject_mgr = new testproject($db);

$tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
$tproject_info = $tproject_mgr->get_by_id($args->tproject_id);
unset($tproject_mgr);

// $filters = array();
// $options = array('output' => 'array', 'only_executed' => true, 'details' => 'full');
// $execSet = $tplan_mgr->get_linked_tcversions($args->tplan_id, $filters, $options);

switch($args->verboseType)
{
  case 'all':
    $execSet = $tplan_mgr->getAllExecutionsWithBugs($args->tplan_id);
  break;

  case 'latest':
  default:
    $execSet = (array)$metricsMgr->getLTCVNewGeneration($args->tplan_id,null,
                                                        array('addExecInfo' => true, 'accessKeyType' => 'index',
                                                              'specViewFields' => true, 'testSuiteInfo' => true,
                                                              'includeNotRun' => false));
  break;


}

$testcase_bugs = array();
$mine = array();

$l18n = init_labels(array('execution_history' => null,'design' => null,'no_linked_bugs' => null));
foreach ($execSet as $execution) 
{
  $tc_id = $execution['tc_id'];
  $mine[] = $execution['exec_id'];
  
  $bug_urls = buildBugString($db, $execution['exec_id'],$its,  $openBugs, $resolvedBugs);
  if ($bug_urls)
  {
    // First bug found for this tc
    if (!isset($testcase_bugs[$tc_id])) 
    {
      // This is ONLY PARENT TEST SUITE !!!
      $suiteName = $execution['tsuite_name'];
      $tc_name = $execution['full_external_id'] . ":" . $execution['name'];

      // add linked icons
      $exec_history_link = "<a href=\"javascript:openExecHistoryWindow({$tc_id});\">" .
                           "<img title=\"" . $l18n['execution_history'] ."\" src=\"{$img['history']}\" /></a> ";
      $edit_link = "<a href=\"javascript:openTCEditWindow({$tc_id});\">" .
                   "<img title=\"" . $l18n['design'] . "\" src=\"{$img['edit']}\" /></a> ";

      $tc_name = "<!-- " . sprintf("%010d", $execution['external_id']) . " -->" . $exec_history_link .
                 $edit_link . $tc_name;

      $testcase_bugs[$tc_id] = array($suiteName, $tc_name, array());
    }
    foreach ($bug_urls as $url)
    {
      if (!in_array($url, $testcase_bugs[$tc_id][2]))
      {
        $testcase_bugs[$tc_id][2][] = $url;
      }
    }
  }
}
foreach ($testcase_bugs as &$row)
{
  $row[2] = implode("<br/>", $row[2]);
}
$arrData = array_values($testcase_bugs);

if(count($arrData) > 0) 
{
  // Create column headers
  $columns = getColumnsDefinition();

  // Extract the relevant data and build a matrix
  $matrixData = array();
  
  foreach($arrData as $bugs) 
  {
    $rowData = array();
    $rowData[] = $bugs[0];
    $rowData[] = $bugs[1];
    $rowData[] = $bugs[2];
    
    $matrixData[] = $rowData;
  }
  
  $table = new tlExtTable($columns, $matrixData, 'tl_table_bugs_per_test_case');
  
  $table->setGroupByColumnName(lang_get('title_test_suite_name'));
  
  $table->setSortByColumnName(lang_get('title_test_case_title'));
  $table->sortDirection = 'ASC';
  
  $table->showToolbar = true;
  $table->toolbarExpandCollapseGroupsButton = true;
  $table->toolbarShowAllColumnsButton = true;
  
  $gui->tableSet = array($table);
} 
else 
{
  $gui->warning_msg = $l18n['no_linked_bugs'];
}

$totalOpenBugs = count($openBugs);
$totalResolvedBugs = count($resolvedBugs);
$totalBugs = $totalOpenBugs + $totalResolvedBugs;
$totalCasesWithBugs = count($arrData);

$gui->user = $args->user;
$gui->printDate = '';
$gui->tproject_name = $tproject_info['name'];
$gui->tplan_name = $tplan_info['name'];
$gui->title = $args->title;
$gui->totalOpenBugs = $totalOpenBugs;
$gui->totalResolvedBugs = $totalResolvedBugs;
$gui->totalBugs = $totalBugs;
$gui->totalCasesWithBugs = $totalCasesWithBugs;
$gui->hint = $args->hint;

$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * Get links to bugs related to execution.
 * @param $db
 * @param $execID execution id
 * @param $openBugsArray array to count open bugs
 * @param $resolvedBugsArray array to count resolved bugs
 *
 * @return array List of links to related bugs
 */
function buildBugString(&$db,$execID,&$bugInterface,&$openBugsArray,&$resolvedBugsArray)
{
  $bugUrls = array();
  if ($bugInterface)
  {
    $bugs = get_bugs_for_exec($db,$bugInterface,$execID);
    if ($bugs)
    {
      foreach($bugs as $bugID => $bugInfo)
      {
        if($bugInfo['isResolved'])
        {
          if(!in_array($bugID, $resolvedBugsArray))
          {
            $resolvedBugsArray[] = $bugID;
          } 
        } 
        else
        {
          if(!in_array($bugID, $openBugsArray))
          {
            $openBugsArray[] = $bugID;
          }
        }
        $bugUrls[] = $bugInfo['link_to_bts'];
      }
    }
  }
  return $bugUrls;
}

/**
 * get Columns definition for table to display
 *
 */
function getColumnsDefinition()
{
  $colDef = array();
  
  $colDef[] = array('title_key' => 'title_test_suite_name', 'width' => 30, 'type' => 'text');
  $colDef[] = array('title_key' => 'title_test_case_title', 'width' => 30, 'type' => 'text');
  $colDef[] = array('title_key' => 'title_test_case_bugs', 'width' => 40, 'type' => 'text');

  return $colDef;
}


/*
  function: init_args()

  args :
  
  returns: 

*/
function init_args()
{
  $iParams = array("format" => array(tlInputParameter::INT_N),
                   "tplan_id" => array(tlInputParameter::INT_N),
                   "type" => array(tlInputParameter::INT_N) );

  $args = new stdClass();
  $pParams = R_PARAMS($iParams,$args);
  
  $args->tproject_id = intval($_SESSION['testprojectID']);
  $args->user = $_SESSION['currentUser'];

  switch($args->type)
  {
    case 1:
      $args->verboseType = 'all';   
      $args->title = lang_get('link_report_total_bugs_all_exec');
      $args->hint = lang_get('link_report_total_bugs_all_exec');
    break;

    default:
    case 0:
      $args->verboseType = 'latest';  
      $args->title = lang_get('link_report_total_bugs');
      $args->hint = '';
    break;
  }
  return $args;
}

function checkRights(&$db,&$user)
{
  return $user->hasRight($db,'testplan_metrics');
}