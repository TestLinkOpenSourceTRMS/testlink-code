<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource  frmWorkArea.php
 * @author      Martin Havlat
 * 
 *
**/
require_once('../../config.inc.php');
require_once("common.php");
testlinkInitPage($db);

$args = init_args();

// Important Notes for Developers
//
// if key found in this map, at User Interface level, screen will be divided 
// vertically in two frames.
// Normally on left will exists a tree menu. 
// On right frame an html named $key.html will be launched.
// Example:
// if key = printTc, an html page printTc.html must exists on help directory
//
// (aa_tfp -> Associative Array TreeFramePath)
// key  : feature
// value: page to lauch
//
$req_cfg = config_get('req_cfg');

// more info here
// array(0) => left pane
// array(1) => right pane
$aa_tfp = array( 
     'editTc' => array('lib/testcases/listTestCases.php?feature=edit_tc',
                       'lib/testcases/archiveData.php?edit=testproject&id='),

     'assignReqs' => 'lib/testcases/listTestCases.php?feature=assignReqs',
     'searchTc' => 'lib/testcases/tcSearchForm.php',

     'searchReq' => 'lib/requirements/reqSearchForm.php',
     'searchReqSpec' => 'lib/requirements/reqSpecSearchForm.php',
   
     'printTestSpec' => 'lib/results/printDocOptions.php?type=testspec',
     'printReqSpec' => 'lib/results/printDocOptions.php?type=reqspec',
     'keywordsAssign' => 'lib/testcases/listTestCases.php?feature=keywordsAssign',
     'planAddTC'    => array('lib/plan/planAddTCNavigator.php?loadRightPaneAddTC=0',
                             'lib/results/printDocOptions.php?activity=addTC'),
     'planRemoveTC' => 'lib/plan/planTCNavigator.php?feature=removeTC&help_topic=planRemoveTC',
     'planUpdateTC'    => 'lib/plan/planTCNavigator.php?feature=planUpdateTC',
     'show_ve' => 'lib/plan/planTCNavigator.php?feature=show_ve',  
     'newest_tcversions' => '../../lib/plan/newest_tcversions.php',
     'test_urgency' => 'lib/plan/planTCNavigator.php?feature=test_urgency',
     'tc_exec_assignment' => 'lib/plan/planTCNavigator.php?feature=tc_exec_assignment',
     'executeTest' => array('lib/execute/execNavigator.php?setting_testplan=', 'lib/execute/execDashboard.php?id='),
     'showMetrics' => 'lib/results/resultsNavigator.php',
     'reqSpecMgmt' => array('lib/requirements/reqSpecListTree.php','lib/project/project_req_spec_mgmt.php?id=')
);

$full_screen = array('newest_tcversions' => 1);

//cleanup session var
$_SESSION['currentSrsId'] = null;

/** feature to display */
$showFeature = $args->feature;
if (isset($aa_tfp[$showFeature]) === FALSE)
{
  // argument is wrong
  tLog("Wrong page argument feature = ".$showFeature, 'ERROR');
  exit();
}

// features that need to run the validate build function
if (in_array($showFeature,array('executeTest','showMetrics','tc_exec_assignment')))
{
  // Check if for test project selected at least a test plan exist
  if( isset($_SESSION['testplanID']) || !is_null($args->tplan_id))
  {
    // Filter on build attributes: ACTIVE,OPEN
    switch($showFeature)
    {
      case 'executeTest':
        $hasToBe['active'] = true;
        $hasToBe['open'] = true;
        $featureHint = lang_get('href_execute_test');
      break;

      case 'tc_exec_assignment':
        $txcfg = config_get('tree_filter_cfg');
        $cfg = $txcfg->testcases->plan_mode;
        $hasToBe['active'] = $cfg->setting_build_inactive_out ? true : null;
        $hasToBe['open'] = $cfg->setting_build_close_out ? true : null;        
        $featureHint = lang_get('href_tc_exec_assignment');
      break;

      default:
        $hasToBe['active'] = null;
        $hasToBe['open'] = null; 
        $featureHint = lang_get('href_rep_and_metrics');
      break;  
    }


    $tplanIDCard = new stdClass();
    $tplanIDCard->id = intval($_SESSION['testplanID']);
    $tplanIDCard->name = $_SESSION['testplanName'];
    $tplanMgr = new testplan($db);

    if(!is_null($args->tplan_id))
    {
      $tplanIDCard->id = intval($args->tplan_id);
      $dummy = $tplanMgr->tree_manager->get_node_hierarchy_info($tplanIDCard->id);
      $tplanIDCard->name = $dummy['name'];
    } 

    $ctx = new stdClass();
    $ctx->tplanIDCard = $tplanIDCard;
    $ctx->featureTitle = $featureHint;

    validateBuildAvailability($db,$tplanMgr,$ctx,$hasToBe);
  }
  else
  {
    redirect('../plan/planView.php');
    exit();
  }   
}

/// 1. get path from global var
/// 2. the URL made easier after setting some rules for help/instruction files
///    naming convention.
/// </enhancement>
$smarty = new TLSmarty();

// try to add context in order to avoid using global coupling via $_SESSION
// this will be useful to open different test projects on different browser TAB
if( is_array($aa_tfp[$showFeature]) )
{
  $leftPane = $aa_tfp[$showFeature][0];
  $rightPane = $aa_tfp[$showFeature][1];
  
  if($rightPane[strlen($rightPane)-1] == '=')
  {
    $rightPane .= intval($_SESSION['testprojectID']);
  }  
  
  if($showFeature == 'executeTest')
  {
    $leftPane .= $args->tplan_id;
  }
  // new dBug($leftPane);

} 
else
{
  $leftPane = $aa_tfp[$showFeature];
  $rightPane = 'lib/general/staticPage.php?key=' . $showFeature;
} 

if( intval($args->tproject_id) > 0 || intval($args->tproject_id) > 0)
{  
  $leftPane .= (strpos($leftPane,"?") === false) ? "?" : "&";
  $leftPane .= "tproject_id={$args->tproject_id}&tplan_id={$args->tplan_id}";

  // for execDashboard is OK, need to understand if will be ok for other features
  // or is going to create issues.
  $rightPane .= (strpos($rightPane,"?") === false) ? "?" : "&";
  $rightPane .= "tproject_id={$args->tproject_id}&tplan_id={$args->tplan_id}";
}

if(isset($full_screen[$showFeature]))
{
  redirect($leftPane);
}
else
{
  $smarty->assign('treewidth', TL_FRMWORKAREA_LEFT_FRAME_WIDTH);
  $smarty->assign('treeframe', $leftPane);
  $smarty->assign('workframe', $rightPane);
  $smarty->display('frmInner.tpl');
}


/** 
 *  validate that some build exists (for Test Plan related features).
 *  If no valid build is found give feedback to user and exit.
 *
 *  check if user can create builds, then put a link on the message page
 *  to create link feature
 *
 *
 * 
 *
 **/
function validateBuildAvailability(&$db,&$tplanMgr,$context,$attrFilter)
{
  $tpID = $context->tplanIDCard->id;
  $tpName = $context->tplanIDCard->name;
  
  if (!$tplanMgr->getNumberOfBuilds($tpID, $attrFilter['active'], $attrFilter['open']))
  {            
    $msx = null;
    if($attrFilter['active'])
    {
      $msx[] = lang_get('active');
    }  
    
    if($attrFilter['open'])
    {
      $msx[] = lang_get('open');
    }  
    
    $mzx = '';
    if(count($msx) > 0)
    {
      $mzx = "(" . implode(' & ',$msx) . ")";
    }  


    $message = "<p>" . $context->featureTitle .
               "<p>" . sprintf(lang_get('no_good_build'),$mzx) .
               "<b> " . htmlspecialchars($tpName) . "</b>";
    
    $link_to_op = '';
    $hint_text = '';
    if(has_rights($db,"testplan_create_build") == 'yes')
    { 
      // final url will be composed adding to $basehref 
      // (one TL variable available on smarty templates) to $link_to_op
      $link_to_op = "lib/plan/buildEdit.php?do_action=create&tplan_id=$tpID";
      $hint_text = lang_get('create_a_build');
    }  
    else
    {
      $message .= '</p><p>' . lang_get('no_build_warning_part2') . '</p>';
    }
      
    // show info and exit
    $smarty = new TLSmarty;
    $smarty->assign('content', $message);
    $smarty->assign('link_to_op', $link_to_op);
    $smarty->assign('hint_text', $hint_text);
    $smarty->display('workAreaSimple.tpl');
    exit();
  }
}

/**
 *
 */
function init_args()
{
  $_REQUEST=strings_stripSlashes($_REQUEST);
  $args = new stdClass();
  $iParams = array("feature" => array(tlInputParameter::STRING_N),
                   "tproject_id" => array(tlInputParameter::INT_N),
                   "tplan_id" => array(tlInputParameter::INT_N));
  R_PARAMS($iParams,$args);

  return $args;
}