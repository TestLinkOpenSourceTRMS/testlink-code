<?php
/** 
*	TestLink Open Source Project - http://testlink.sourceforge.net/
* 
* @version $Id: planAddTCNavigator.php,v 1.22 2007/09/24 20:51:45 schlundus Exp $
*	@author Martin Havlat
* 
* 	Navigator for feature: add Test Cases to a Test Case Suite in Test Plan. 
*	It builds the javascript tree that allow the user select a required part 
*	Test specification. Keywords should be used for filter.
* 
* rev :
*      20070920 - franciscom - REQ - BUGID test plan combo box
* 
*      20061112 - franciscom - changes in call to generateTestSpecTree()
*                              to manage the display ONLY of ACTIVE test case versions .
*/
require('../../config.inc.php');
require_once("common.php");
require_once("treeMenu.inc.php");
testlinkInitPage($db);

$src_workframe=null;
$do_reload=0;
$keyword_id = isset($_POST['keyword_id']) ? $_POST['keyword_id'] : 0;
$tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';
$user_id=$_SESSION['userID'];
$tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testPlanId'];

$tproject_mgr = new testproject($db);
$keywords_map = $tproject_mgr->get_keywords_map($tproject_id, " order by keyword "); 
if(!is_null($keywords_map))
{
  $keywords_map = array( 0 => '') + $keywords_map;
}

// filter using user roles 
$tplans=getAccessibleTestPlans($db,$tproject_id,$user_id,1);
$map_tplans=array();
foreach($tplans as $key => $value)
{
  $map_tplans[$value['id']]=$value['name'];
}


// generate tree 
$workPath = 'lib/plan/planAddTC.php';
$args = '&tplan_id=' . $tplan_id;       // 20070922 - franciscom
if ($keyword_id)
{
	$args .= '&keyword_id=' . $keyword_id;
}

// link to load frame named 'workframe' when the update button is pressed
if(isset($_REQUEST['filter']))
{
	$src_workframe = $_SESSION['basehref']. $workPath . "?edit=testproject&id={$tproject_id}" . $args;
}
else if ( isset($_REQUEST['called_by_me']) )
{
  // Algorithm bases on field order on URL call
  $dummy=explode('?',$_REQUEST['called_url']);
  $qs=explode('&',$dummy[1]);
  if($qs[0] == 'edit=testsuite')
  {
    $src_workframe = $dummy[0] . "?" . $qs[0] . "&" . $qs[1];
  }
  else
  {   
    $src_workframe = $_SESSION['basehref'].$workPath . "?edit=testproject&id={$tproject_id}";
  }
  $src_workframe .= $args;  
}



define('ACTION_TESTCASE_DISABLE',0);
define('IGNORE_INACTIVE_TESTCASES',1);

// added $tplan_id
$treeString = generateTestSpecTree($db,$tproject_id, $tproject_name,  
                                   $workPath,HIDE_TESTCASES,ACTION_TESTCASE_DISABLE,
                                   $args, $keyword_id,IGNORE_INACTIVE_TESTCASES);



                                   
$tree = invokeMenu($treeString);
$smarty = new TLSmarty();

$smarty->assign('tplan_id',$tplan_id);
$smarty->assign('map_tplans',$map_tplans);
$smarty->assign('do_reload',$do_reload);

$smarty->assign('src_workframe',$src_workframe);

$smarty->assign('treeKind', TL_TREE_KIND);
$smarty->assign('tree', $tree);
$smarty->assign('keywords_map', $keywords_map);
$smarty->assign('keyword_id', $keyword_id);
$smarty->assign('menuUrl', $workPath);

// A javascript variable 'args' will be initialized with this value
// using inc_head.tpl template.
$smarty->assign('args', $args);

$smarty->display('planAddTCNavigator.tpl');
?>
