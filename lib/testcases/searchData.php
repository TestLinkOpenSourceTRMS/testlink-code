<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: searchData.php,v 1.36 2008/11/15 18:07:00 franciscom Exp $
 * Purpose:  This page presents the search results. 
 *
 * rev:
 *     20081115 - franciscom - refactored to improve:
 *     performance and information displayed.
 *              
**/
require_once("../../config.inc.php");
require_once("common.php");
// require_once("users.inc.php");
// require_once("attachments.inc.php");
testlinkInitPage($db);

$template_dir = 'testcases/';
$tproject_mgr = new testproject($db);

$tcase_cfg = config_get('testcase_cfg');
$gui = new stdClass();
$gui->pageTitle=lang_get('caption_search_form');
$gui->warning_msg='';
$gui->tcasePrefix='';
$gui->path_info=null;
$gui->resultSet=null;

$map = null;
$args = init_args();

if ($args->tprojectID)
{
    $from = array('by_keyword_id' => ' ', 'by_custom_field' => ' ');
    $filter = null;
    if($args->targetTestCase)
    {
        $tcase_mgr = new testcase ($db);
        $tcaseID = $tcase_mgr->getInternalID($args->targetTestCase,$tcase_cfg->glue_character);  
        $filter['by_tc_id'] = " AND NHB.parent_id = {$tcaseID} ";
    }
    else
    {
        $tproject_mgr->get_all_testcases_id($args->tprojectID,$a_tcid);
        $filter['by_tc_id'] = " AND NHB.parent_id IN (" . implode(",",$a_tcid) . ") ";
    }
    
    if($args->version)
    {
        $filter['by_version'] = " AND version = {$args->version} ";
    }
    
    if($args->keyword_id)				
    {
        $from['by_keyword_id'] = ' ,testcase_keywords KW';
        $filter['by_keyword_id'] = " AND NHA.id = KW.testcase_id AND KW.keyword_id = {$args->keyword_id} ";	
    }
    
    if(strlen($args->name))
    {
        $args->name =  $db->prepare_string($args->name);
        $filter['by_name'] = " AND NHA.name like '%{$args->name}%' ";
    }
    
    if(strlen($args->summary))
    {
        $summary = $db->prepare_string($args->summary);
        $filter['by_summary'] = " AND summary like '%{$args->summary}%' ";
    }    
    
    if(strlen($args->steps))
    {
        $args->steps = $db->prepare_string($args->steps);
        $filter['by_steps'] = " AND steps like '%{$args->steps}%' ";	
    }    
    
    if(strlen($args->expected_results))
    {
        $args->expected_results = $db->prepare_string($args->expected_results);
        $filter['by_expected_results'] = " AND expected_results like '%{$args->expected_results}%' ";	
    }    
    
    // ------------------------------------------------------------------------------------
    // BUGID
    if($args->custom_field_id > 0)
    {
        $args->custom_field_id = $db->prepare_string($args->custom_field_id);
        $args->custom_field_value = $db->prepare_string($args->custom_field_value);
        $from['by_custom_field']= ' ,cfield_design_values CFD'; 
        $filter['by_custom_field'] = " AND CFD.field_id={$args->custom_field_id} " .
                                     " AND CFD.node_id=NHA.id " .
                                     " AND CFD.value like '%{$args->custom_field_value}%' ";
    }
   
    // ------------------------------------------------------------------------------------
    $sql = " SELECT NHA.id AS testcase_id,NHA.name,tcversions.id AS tcversion_id," .
           " summary,steps,expected_results,version,tc_external_id".
           " FROM nodes_hierarchy NHA, nodes_hierarchy NHB, tcversions " .
           " {$from['by_keyword_id']} {$from['by_custom_field']}".
           " WHERE NHA.id = NHB.parent_id AND NHB.id = tcversions.id ";
    if ($filter)
    {
        $sql .= implode("",$filter);
    }
    $map = $db->fetchRowsIntoMap($sql,'testcase_id');	
}


$smarty = new TLSmarty();
$gui->row_qty=count($map);
if($gui->row_qty)
{
	// $tcase_mgr = new testcase($db);   
  // $tcase_set=array_keys($map);
  // $path_info=get_full_path_verbose($tcase_set,$tcase_mgr->tree_manager,$db);  
  // 
  // $attachmentRepository = tlAttachmentRepository::create($db);
	// $attachments = null;
	// foreach($map as $id => $dd)
	// {
	// 	$attachments[$id] = getAttachmentInfos($attachmentRepository,$id,'nodes_hierarchy',true,1);
	// }
	// $smarty->assign('attachments',$attachments);
	// $viewerArgs = array('hilite_testcase_name' => 1, 'show_match_count' => 1,
	//                     'title' => $gui->pageTitle);
  //
	// $tcase_mgr->show($smarty, $template_dir,$tcase_set,TC_ALL_VERSIONS,$viewerArgs,$path_info);

  $gui->pageTitle .= " - " . lang_get('match_count') . ":" . $gui->row_qty;
  if($gui->row_qty <= $tcase_cfg->search->max_qty_for_display)
  {	
	    $tcase_mgr = new testcase($db);   
      $tcase_set=array_keys($map);
      $gui->path_info=get_full_path_verbose($tcase_set,$tcase_mgr->tree_manager,$db);  

      $gui->tcasePrefix=$tproject_mgr->getTestCasePrefix($args->tprojectID);
      $gui->tcasePrefix .= $tcase_cfg->glue_character;
	    $gui->resultSet=$map;
	}
	else
	{
	    $gui->warning_msg=lang_get('too_wide_search_criteria');
	}
	$smarty->assign('gui',$gui);
	$smarty->display($template_dir . 'tcSearchResults.tpl');
}
else
{
	$the_tpl = config_get('tpl');
	$smarty->display($template_dir . $the_tpl['tcView']);
}

/*
  function: 

  args:
  
  returns: 

*/
function init_args()
{
   	$args = new stdClass();
    $_REQUEST = strings_stripSlashes($_REQUEST);
  
    $strnull = array('name','summary','steps','expected_results','custom_field_value','targetTestCase');
    foreach($strnull as $keyvar)
    {
        $args->$keyvar = isset($_REQUEST[$keyvar]) ? trim($_REQUEST[$keyvar]) : null;  
    }

    $int0 = array('keyword_id','version','custom_field_id');
    foreach($int0 as $keyvar)
    {
        $args->$keyvar = isset($_REQUEST[$keyvar]) ? intval($_REQUEST[$keyvar]) : 0;  
    }
    
    $args->userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
    $args->tprojectID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;

    return $args;
}


/*
  function: 

  args:
  
  returns: 

*/
function get_full_path_verbose(&$items,&$tree_mgr,&$db_handler)
{
   $goto_root=null;
   $path_to=null;
   $all_nodes=array();
   foreach($items as $item_id)
   {
       $path_to[$item_id]=$tree_mgr->get_path($item_id,$goto_root,'simple'); 
       $all_nodes = array_merge($all_nodes,$path_to[$item_id]);
   }
   
   // get only different items, to get descriptions
   $unique_nodes=implode(',',array_unique($all_nodes));
   $sql="SELECT id,name FROM nodes_hierarchy WHERE id IN ({$unique_nodes})"; 
   $decode=$db_handler->fetchRowsIntoMap($sql,'id');
   foreach($path_to as $key => $elem)
   {
        foreach($elem as $idx => $node_id)
        {
              $path_to[$key][$idx]=$decode[$node_id]['name'];
        }
   }   
   return $path_to; 
}
?>