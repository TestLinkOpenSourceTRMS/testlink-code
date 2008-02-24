<?php
/** 
* 	TestLink Open Source Project - http://testlink.sourceforge.net/
* 
* 	@version 	$Id: listTestCases.php,v 1.24 2008/02/24 17:55:00 franciscom Exp $
* 	@author 	Martin Havlat
* 
* 	Generates tree menu with test specification. 
*   It builds the javascript tree that allows the user to choose testsuite or testcase.
*
*   rev: 20080223 - franciscom -
*        20070217 - franciscom - added test suite filter
*/
require_once('../../config.inc.php');
require_once("common.php");
require_once("treeMenu.inc.php");
testlinkInitPage($db);

$template_dir='testcases/';

$spec_cfg = config_get('spec_cfg');

$feature = isset($_REQUEST['feature']) ? $_REQUEST['feature'] : null;
$tproject_id   = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : 'xxx';

$do_refresh_on_action = manage_tcspec($_REQUEST,$_SESSION,
                                    'tcspec_refresh_on_action','hidden_tcspec_refresh_on_action',
                                    $spec_cfg->automatic_tree_refresh);

$_SESSION['tcspec_refresh_on_action'] = $do_refresh_on_action;

$tsuites_to_show = isset($_REQUEST['tsuites_to_show']) ? $_REQUEST['tsuites_to_show'] : 0;
$title = lang_get('title_navigator'). ' - ' . lang_get('title_test_spec');

$feature_action = array('edit_tc' => "lib/testcases/archiveData.php",
                        'keywordsAssign' => "lib/keywords/keywordsAssign.php",
                        'assignReqs' => "lib/requirements/reqTcAssign.php");

if(!is_null($feature) && strlen($feature))
{
	if(isset($feature_action[$feature]))
		$workPath = $feature_action[$feature];
	else
	{
		tLog("Wrong get argument 'feature'.", 'ERROR');
		exit();
	}
}
else
{
	tLog("Missing argument 'feature'.", 'ERROR');
	exit();
}


$draw_filter = $spec_cfg->show_tsuite_filter;
$exclude_branches = null;
$tsuites_combo = null;
if($spec_cfg->show_tsuite_filter)
{
	$mappy = tsuite_filter_mgmt($db,$tproject_id,$tsuites_to_show);
	$exclude_branches = $mappy['exclude_branches'];
	$tsuites_combo = $mappy['html_options'];
	$draw_filter = $mappy['draw_filter'];
}
$treeString = generateTestSpecTree($db,$tproject_id, $tproject_name,
                                   $workPath,NOT_FOR_PRINTING,
                                   SHOW_TESTCASES,DO_ON_TESTCASE_CLICK,
                                   NO_ADDITIONAL_ARGS, NO_KEYWORD_ID_TO_FILTER,
                                   DO_NOT_FILTER_INACTIVE_TESTCASES,$exclude_branches);

$tree = null;
if (strlen($treeString))
	$tree = invokeMenu($treeString,"",null);
		
$smarty = new TLSmarty();

$smarty->assign('tcspec_refresh_on_action',$do_refresh_on_action);
$smarty->assign('tsuites_combo',$tsuites_combo);
$smarty->assign('tsuite_choice',$tsuites_to_show);
$smarty->assign('draw_filter',$draw_filter) ;
$smarty->assign('treeKind', TL_TREE_KIND);
$smarty->assign('tree', $tree);
$smarty->assign('treeHeader', $title);
$smarty->assign('menuUrl',$workPath);
$smarty->display($template_dir . 'tcTree.tpl');

/*
  function: tsuite_filter_mgmt

  args :
  
  returns: map keys  draw_filter -> 1 / 0
                     map for smarty html_options

*/
function tsuite_filter_mgmt(&$db,$tproject_id,$tsuites_to_show)
{
  $tproject_mgr = New testproject($db);

  $ret=array('draw_filter' => 0,
             'html_options' => array(0 =>''),
             'exclude_branches' => null);
             
  $fl_tsuites=$tproject_mgr->get_first_level_test_suites($tproject_id,'smarty_html_options');
  if( $tsuites_to_show > 0 )
  {
     foreach($fl_tsuites as $tsuite_id => $name)
     {
        if($tsuite_id != $tsuites_to_show)
        {
          $ret['exclude_branches'][$tsuite_id] = 'exclude_me';
        } 
     }  
  } 
  
  $ret['draw_filter']=(!is_null($fl_tsuites) && count($fl_tsuites) > 0) ? 1 :0;
  $tsuites_combo=array(0 =>'');
  if($ret['draw_filter'])
  {
    // add blank option as first choice
    $ret['html_options'] += $fl_tsuites;
  }
  return($ret);
}


/*
  function: 

  args:
  
  returns: 

*/
function manage_tcspec($hash_REQUEST,$hash_SESSION,$key2check,$hidden_name,$default)
{
    if (isset($hash_REQUEST[$hidden_name]))
    {
      $do_refresh = "no";
      if( isset($hash_REQUEST[$key2check]) )
      {
  	    $do_refresh = $hash_REQUEST[$key2check] > 0 ? "yes": "no";
      }
    }
    elseif (isset($hash_SESSION[$key2check]))
    {
       $do_refresh = $hash_SESSION[$key2check] > 0 ? "yes": "no";
    }
    else
    {  
       $do_refresh = $default > 0 ? "yes": "no";
    }
    return $do_refresh;
}
?>
