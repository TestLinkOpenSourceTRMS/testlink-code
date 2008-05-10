<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * @version $Id: planTCRemove.php,v 1.9 2008/05/10 17:59:15 franciscom Exp $ 
 * 
 * Remove Test Cases from Test Plan
 * 
 * 20080510 - franciscom - multiple keyword filter
 * 20080114 - franciscom - added testCasePrefix management
 * 20070408 - franciscom - refactoring to use planAddTC_m1.tpl, 
 *                         wrapped by planRemoveTC_m1.tpl
 *
 *
 * 20070124 - franciscom
 * use show_help.php to apply css configuration to help pages
 *
 */         
require('../../config.inc.php');
require_once("common.php");
require("specview.php");

testlinkInitPage($db);

$tree_mgr = new tree($db); 
$tsuite_mgr = new testsuite($db); 
$tplan_mgr = new testplan($db); 
$tcase_mgr = new testcase($db); 

$templateCfg = templateConfiguration();


$args = init_args();
$gui = new stdClass();

$tcase_cfg=config_get('testcase_cfg');
$gui->pageTitle = lang_get('test_plan') . $guiCfg->title_sep_1 . $tplan_info['name'];

$gui->testCasePrefix = $tcase_mgr->tproject_mgr->getTestCasePrefix($args->tproject_id);
$gui->testCasePrefix .= $tcase_cfg->glue_character;
$gui->user_feedback='';
$gui->keywords_filter = '';

$gui->keywordsFilterType=new stdClass();
$gui->keywordsFilterType->options = array('OR' => 'Or' , 'AND' =>'And'); 
$gui->keywordsFilterType->selected=$args->keywordsFilterType;

$keywordsFilter=null;
if( is_array($args->keyword_id) )
{
    $keywordsFilter=new stdClass();
    $keywordsFilter->items = $args->keyword_id;
    $keywordsFilter->type = $gui->keywordsFilterType->selected;
}


// ---------------------------------------------------------------------------------------
if($args->doAction == 'doAddRemove')
{
  $do_remove=1;
  $a_tc = isset($_POST['remove_checked_tc']) ? $_POST['remove_checked_tc'] : null;
  if(!is_null($a_tc))
  {
      // remove without warning
      $tplan_mgr->unlink_tcversions($args->tplan_id,$a_tc);   
      
      $gui->user_feedback=lang_get("tcase_removed_from_tplan");
      if( count($a_tc) > 1 )
      {
        $gui->user_feedback=lang_get("multiple_tcase_removed_from_tplan");
      }
  }  
  else
  {
    // 20070225 - BUGID 644
    $do_remove=0;
  }
}

$out = null;
$map_node_tccount = get_testplan_nodes_testcount($db,$args->tproject_id,$args->tproject_name,
                                                     $args->tplan_id,$args->tplan_name,$keywordsFilter);
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
		  $linked_items[$id] = $pp[$args->version_id];
		  $linked_items[$id]['testsuite_id'] = $tsuite_data['id'];
		  $linked_items[$id]['tc_id'] = $args->id;

  		$out = gen_spec_view($db,'testplan',$args->tplan_id,$tsuite_data['id'],$tsuite_data['name'],
	  			                 $linked_items,$map_node_tccount,$args->keyword_id,
	  			                 FILTER_BY_TC_OFF,WRITE_BUTTON_ONLY_IF_LINKED);
		}
	  break;
		
	case 'testsuite':
		if( $total_tccount > 0 )
		{
        $out=processTestSuite($db,$args,$map_node_tccount,$keywordsFilter,$tplan_mgr,$tcase_mgr);
    }                       
		break;
		
	default:
  show_instructions('planRemoveTC');
	exit();
	break;
}

$gui->refreshTree=($do_remove ? 1 : 0);
$gui->items=null;
$gui->has_tc=1;
if(is_null($out) )
{
  show_instructions('planRemoveTC',1);
	exit();
}
else
{
  $gui->has_tc=($out['num_tc'] > 0 ? 1:0);
  $gui->items=$out['spec_view'];
}

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . 'planRemoveTC_m1.tpl');


/*
  function: init_args
            creates a sort of namespace

  args:

  returns: object with some REQUEST and SESSION values as members

*/
function init_args()
{
	$_REQUEST = strings_stripSlashes($_REQUEST);

	$args = new stdClass();

  $args->id = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
  $args->version_id = isset($_REQUEST['version_id']) ? $_REQUEST['version_id'] : 0;
  $args->level = isset($_REQUEST['level']) ? $_REQUEST['level'] : null;
  
  // Can be a list (string with , (comma) has item separator), that will be trasformed in an array.
  $keywordSet = isset($_REQUEST['keyword_id']) ? $_REQUEST['keyword_id'] : null;
  $args->keyword_id = is_null($keywordSet) ? 0 : explode(',',$keywordSet); 
  $args->keywordsFilterType=isset($_REQUEST['keywordsFilterType']) ? $_REQUEST['keywordsFilterType'] : 'OR';

  
  $args->doAction = isset($_REQUEST['doAction']) ? $_REQUEST['doAction'] : null;


  $args->tplan_id = $_SESSION['testPlanId'];
  $args->tplan_name = $_SESSION['testPlanName'];
  $args->tproject_id =  $_SESSION['testprojectID'];
  $args->tproject_name =  $_SESSION['testprojectName'];

	return $args;
}

/*
  function: processTestSuite 

  args :
  
  returns: 

*/
function processTestSuite(&$dbHandler,&$argsObj,$map_node_tccount,
                          $keywordsFilter,&$tplanMgr,&$tcaseMgr)
{
    $out=keywordFilteredSpecView($dbHandler,$argsObj,$map_node_tccount,
                                 $keywordsFilter,$tplanMgr,$tcaseMgr);
    return $out;
}
?>

