<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: searchData.php,v 1.32 2008/09/21 19:02:48 schlundus Exp $
 * Purpose:  This page presents the search results. 
 *
 * rev:
 *     20080120 - franciscom
**/
require('../../config.inc.php');
require_once("common.php");
require_once("users.inc.php");
require_once("attachments.inc.php");
testlinkInitPage($db);

$template_dir = 'testcases/';
$tproject_mgr = new testproject($db);

$map = null;
$args = init_args();
if ($args->tprojectID)
{
	$from = array('by_keyword_id' => ' ', 'by_custom_field' => ' ');
  
	$a_tcid = $tproject_mgr->get_all_testcases_id($args->tprojectID);
	$filter = null;
	if(count($a_tcid))
	{
	  
		if($args->targetTestCase)
		{
			$tcase_mgr = new testcase ($db);
	      	$cfg = config_get('testcase_cfg');
			$tcaseID = $tcase_mgr->getInternalID($args->targetTestCase,$cfg->glue_character);  
			$filter['by_tc_id'] = " AND NHB.parent_id = {$tcaseID} ";
		}
		else
		{
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

		$sql = " SELECT NHA.id AS testcase_id,NHA.name,summary,steps,expected_results,version ".
			     " FROM nodes_hierarchy NHA, nodes_hierarchy NHB, tcversions " .
			     " {$from['by_keyword_id']} {$from['by_custom_field']}".
  			   " WHERE NHA.id = NHB.parent_id AND NHB.id = tcversions.id ";
			
		if ($filter)
			$sql .= implode("",$filter);
		$map = $db->fetchRowsIntoMap($sql,'testcase_id');			
	}
	
}

$smarty = new TLSmarty();
if(count($map))
{
	$attachmentRepository = tlAttachmentRepository::create($db);
	$attachments = null;
	foreach($map as $id => $dd)
	{
		$attachments[$id] = getAttachmentInfos($attachmentRepository,$id,'nodes_hierarchy',true,1);
	}
	$smarty->assign('attachments',$attachments);
	$tcase_mgr = new testcase($db);   
	$viewerArgs=array('display_parent_testsuite' => 1);
	
	$tcase_mgr->show($smarty, $template_dir,array_keys($map),TC_ALL_VERSIONS,$viewerArgs);
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
  
    $strnull=array('name','summary','steps','expected_results','custom_field_value','targetTestCase');
    foreach($strnull as $keyvar)
    {
        $args->$keyvar=isset($_REQUEST[$keyvar]) ? trim($_REQUEST[$keyvar]) : null;  
    }

    $int0=array('keyword_id','version','custom_field_id');
    foreach($int0 as $keyvar)
    {
        $args->$keyvar=isset($_REQUEST[$keyvar]) ? intval($_REQUEST[$keyvar]) : 0;  
    }
    
    $args->userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
    $args->tprojectID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;

    return $args;
}
?>