<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * @version $Id: planUpdateTC.php,v 1.17 2008/01/26 17:56:23 franciscom Exp $ 
 * 
 *
 *
 */         
require('../../config.inc.php');
require_once("common.php");

testlinkInitPage($db);

$tree_mgr = new tree($db); 
$tsuite_mgr = new testsuite($db); 
$tplan_mgr = new testplan($db); 
$tcase_mgr = new testcase($db); 

$template_dir='plan/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

$args=init_args();
$tcase_cfg=config_get('testcase_cfg');

$testCasePrefix = $tcase_mgr->tproject_mgr->getTestCasePrefix($args->tproject_id);
$testCasePrefix .= $tcase_cfg->glue_character;


$user_feedback='';

$resultString = null;
$arrData = array();

// echo "<pre>debug 20080126 - \ - " . __FUNCTION__ . " --- "; print_r($_REQUEST); echo "</pre>";

$do_remove=0;
// ---------------------------------------------------------------------------------------
if($do_remove)
{
  $a_tc = isset($_POST['remove_checked_tc']) ? $_POST['remove_checked_tc'] : null;
  if(!is_null($a_tc))
  {
      // remove without warning
      $tplan_mgr->unlink_tcversions($args->tplan_id,$a_tc);   
      
      $user_feedback=lang_get("tcase_removed_from_tplan");
      if( count($a_tc) > 1 )
      {
        $user_feedback=lang_get("multiple_tcase_removed_from_tplan");
      }
  }  
  else
  {
    // 20070225 - BUGID 644
    $do_remove=0;
  }
}

$dummy = null;
$out = null;
$map_node_tccount = get_testplan_nodes_testcount($db,$args->tproject_id,$args->tproject_name,
                                                     $args->tplan_id,$args->tplan_name,$args->keyword_id);
$total_tccount=0;
foreach($map_node_tccount as $elem)
{
  $total_tccount +=$elem['testcount'];
}		

switch($args->level)
{
	case 'testcase':
		
		if( $total_tccount > 0 && !$do_remove)
		{
  		// build data needed to call gen_spec_view
	  	$my_path = $tree_mgr->get_path($args->id);
		  $idx_ts = count($my_path)-1;
		  $tsuite_data= $my_path[$idx_ts-1];
		
		  $pp = $tcase_mgr->get_versions_status_quo($args->id, $args->version_id, $args->tplan_id);
		  $linked_items[$id] = $pp[$version_id];
		  $linked_items[$id]['testsuite_id'] = $tsuite_data['id'];
		  $linked_items[$id]['tc_id'] = $id;

  		$out = gen_spec_view($db,'testplan',$args->tplan_id,$tsuite_data['id'],$tsuite_data['name'],
	  			                 $linked_items,$map_node_tccount,$args->keyword_id,
	  			                 FILTER_BY_TC_OFF,WRITE_BUTTON_ONLY_IF_LINKED);
		}
	  break;
		
	case 'testsuite':
		if( $total_tccount > 0 )
		{
  		$tsuite_data = $tsuite_mgr->get_by_id($args->id);

	  	$out = gen_spec_view($db,'testplan',$args->tplan_id,$args->id,$tsuite_data['name'],
                           $tplan_mgr->get_linked_tcversions($args->tplan_id,FILTER_BY_TC_OFF,$args->keyword_id),
                           $map_node_tccount,
                           $args->keyword_id,FILTER_BY_TC_OFF,WRITE_BUTTON_ONLY_IF_LINKED);
    }                       
		break;
		
	default:
		// show instructions
  	//redirect($_SESSION['basehref'] . "/lib/general/staticPage.php?key=planRemoveTC");

	break;
}


$smarty = new TLSmarty();

$smarty->assign('has_tc', 1);
$smarty->assign('arrData',null);
$smarty->assign('testCasePrefix', $testCasePrefix);

if( !is_null($out) )
{
  $smarty->assign('has_tc', ($out['num_tc'] > 0 ? 1:0));
  $smarty->assign('arrData', $out['spec_view']);
}

$smarty->assign('user_feedback', $user_feedback);
$smarty->assign('testPlanName', $args->tplan_name);
$smarty->assign('refreshTree', $do_remove ? 1 : 0);

$smarty->display($template_dir . $default_template);
?>

<?php
function init_args()
{
    $_REQUEST=strings_stripSlashes($_REQUEST);
    
    $args->id = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
    $args->version_id = isset($_REQUEST['version_id']) ? $_REQUEST['version_id'] : 0;
    $args->level = isset($_REQUEST['level']) ? $_REQUEST['level'] : null;
    $args->keyword_id = isset($_REQUEST['keyword_id']) ? $_REQUEST['keyword_id'] : 0;
    // $do_remove = isset($_POST['do_action']) ? 1 : 0;


    $args->tplan_id = $_SESSION['testPlanId'];
    $args->tplan_name = $_SESSION['testPlanName'];
    $args->tproject_id =  $_SESSION['testprojectID'];
    $args->tproject_name =  $_SESSION['testprojectName'];


    return $args;  
}

?>