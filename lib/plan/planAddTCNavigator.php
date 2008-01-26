<?php
/** 
*	TestLink Open Source Project - http://testlink.sourceforge.net/
* 
* @version $Id: planAddTCNavigator.php,v 1.27 2008/01/26 17:56:23 franciscom Exp $
*	@author Martin Havlat
* 
* 	Navigator for feature: add Test Cases to a Test Case Suite in Test Plan. 
*	It builds the javascript tree that allow the user select a required part 
*	Test specification. Keywords should be used for filter.
* 
* rev :
*      20080126 - franciscom - refactoring
*      20070920 - franciscom - REQ - BUGID test plan combo box
* 
*      20061112 - franciscom - changes in call to generateTestSpecTree()
*                              to manage the display ONLY of ACTIVE test case versions .
*/
require('../../config.inc.php');
require_once("common.php");
require_once("treeMenu.inc.php");
testlinkInitPage($db);

$template_dir='plan/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

echo "<pre>debug 20080126 - \ - " . __FUNCTION__ . " --- "; print_r($_REQUEST); echo "</pre>";

$args=init_args();

$src_workframe=null;
$do_reload=0;

$tproject_mgr = new testproject($db);
$keywords_map = $tproject_mgr->get_keywords_map($args->tproject_id); 
if(!is_null($keywords_map))
{
  $keywords_map = array( 0 => '') + $keywords_map;
}

// filter using user roles 
$tplans=getAccessibleTestPlans($db,$args->tproject_id,$args->user_id,1);
$map_tplans=array();
foreach($tplans as $key => $value)
{
  $map_tplans[$value['id']]=$value['name'];
}


// generate tree 
$workPath = 'lib/plan/planAddTC.php';
$treeArgs = '&tplan_id=' . $args->tplan_id;
if ($args->keyword_id)
{
	$treeArgs .= '&keyword_id=' . $args->keyword_id;
}

// link to load frame named 'workframe' when the update button is pressed
if(isset($_REQUEST['filter']))
{
	$src_workframe = $_SESSION['basehref']. $workPath . "?edit=testproject&id={$args->tproject_id}" . $treeArgs;
}
else if ( isset($_REQUEST['called_by_me']) )
{
  // Algorithm based on field order on URL call
  $dummy=explode('?',$_REQUEST['called_url']);
  $qs=explode('&',$dummy[1]);
  if($qs[0] == 'edit=testsuite')
  {
    $src_workframe = $dummy[0] . "?" . $qs[0] . "&" . $qs[1];
  }
  else
  {   
    $src_workframe = $_SESSION['basehref'].$workPath . "?edit=testproject&id={$args->tproject_id}";
  }
  $src_workframe .= $treeArgs;  
}

// added $tplan_id
$treeString = generateTestSpecTree($db,$args->tproject_id, $args->tproject_name,  
                                   $workPath,NOT_FOR_PRINTING,
                                   HIDE_TESTCASES,ACTION_TESTCASE_DISABLE,
                                   $treeArgs, $args->keyword_id,IGNORE_INACTIVE_TESTCASES);



                                   
$tree = invokeMenu($treeString,'',null);
$smarty = new TLSmarty();

$smarty->assign('tplan_id',$args->tplan_id);
$smarty->assign('map_tplans',$map_tplans);
$smarty->assign('do_reload',$do_reload);

$smarty->assign('src_workframe',$src_workframe);

$smarty->assign('treeKind', TL_TREE_KIND);
$smarty->assign('tree', $tree);
$smarty->assign('keywords_map', $keywords_map);
$smarty->assign('keyword_id', $args->keyword_id);
$smarty->assign('menuUrl', $workPath);

// A javascript variable 'args' will be initialized with this value
// using inc_head.tpl template.
$smarty->assign('args', $treeArgs);

$smarty->display($template_dir . $default_template);
?>


<?php
function init_args()
{
    $_REQUEST=strings_stripSlashes($_REQUEST);


    $args->keyword_id = isset($_REQUEST['keyword_id']) ? $_REQUEST['keyword_id'] : 0;
    $args->tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testPlanId'];

    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';
    $args->user_id=$_SESSION['userID'];

  
    return $args;
}
?>