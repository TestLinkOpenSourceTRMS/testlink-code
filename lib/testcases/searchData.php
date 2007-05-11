<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: searchData.php,v 1.21 2007/05/11 20:29:19 schlundus Exp $
 * Purpose:  This page presents the search results. 
 *
 * 20060427 - franciscom - added include tescase class
 * 20050821 - fm - changes to use template customization (trying to reduce code redundancy)
**/
require('../../config.inc.php');
require_once("../functions/common.php");
require_once("../functions/users.inc.php");
require_once("../functions/attachments.inc.php");
testlinkInitPage($db);

$_POST = strings_stripSlashes($_POST);

$name = isset($_POST['name']) ? trim($_POST['name']) : null;
$summary = isset($_POST['summary']) ? trim($_POST['summary']) : null;
$steps = isset($_POST['steps']) ? trim($_POST['steps']) : null;
$expected_results = isset($_POST['expected_results']) ? trim($_POST['expected_results']) : null;
$keyword_id = isset($_POST['key']) ? intval($_POST['key']) : 0;
$tc_id = isset($_POST['TCID']) ? intval($_POST['TCID']) : 0;
$version = isset($_POST['version']) ? intval($_POST['version']) : 0;

$arrTc = null;
$userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
$tproject = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tproject_mgr = new testproject($db);

$map = null;
if ($tproject)
{
	$from = array(
				'by_keyword_id' => ' '
			);
    $a_tcid = $tproject_mgr->get_all_testcases_id($tproject);
	$filter = null;
	if(count($a_tcid))
	{
		if($tc_id)
		{
			$filter['by_tc_id'] = " AND NHB.parent_id = {$tc_id} ";
		}
		else
		{
			$filter['by_tc_id'] = " AND NHB.parent_id IN (" . implode(",",$a_tcid) . ") ";
		}
	
		if($version)
		{
			$filter['by_version'] = " AND version = {$version} ";
		}
     
		if($keyword_id)				
		{
			$from['by_keyword_id'] = ' ,testcase_keywords KW';
			$filter['by_keyword_id'] = " AND NHA.id = KW.testcase_id AND KW.keyword_id = {$keyword_id} ";	
		}

        if(strlen($name))
        {
            $name =  $db->prepare_string($name);
        	$filter['by_name'] = " AND NHA.name like '%{$name}%' ";
        }
      
	    if(strlen($summary))
        {
            $summary = $db->prepare_string($summary);
        	$filter['by_summary'] = " AND summary like '%{$summary}%' ";	
        }    

        if(strlen($steps))
        {
            $steps = $db->prepare_string($steps);
        	$filter['by_steps'] = " AND steps like '%{$steps}%' ";	
        }    

        if(strlen($expected_results))
        {
            $expected_results = $db->prepare_string($expected_results);
        	$filter['by_expected_results'] = " AND expected_results like '%{$expected_results}%' ";	
        }    

		$sql = " SELECT NHA.id AS testcase_id,NHA.name,summary,steps,expected_results,version ".
			   " FROM nodes_hierarchy NHA, nodes_hierarchy NHB, tcversions {$from['by_keyword_id']}".
  			   " WHERE NHA.id = NHB.parent_id AND NHB.id = tcversions.id ";
			
		if ($filter)
			$sql .= implode("",$filter);
		$map = $db->fetchRowsIntoMap($sql,'testcase_id');			
	}
	
}
$smarty = new TLSmarty();
if(count($map))
{
	$attachments = null;
	foreach($map as $id => $dd)
	{
		$attachments[$id] = getAttachmentInfos($db,$id,'nodes_hierarchy',true,1);
	}
	$smarty->assign('attachments',$attachments);
	$tcase_mgr = new testcase($db);   
	$tcase_mgr->show($smarty,array_keys($map), $userID);
}
else
{
	$the_tpl = config_get('tpl');
	$smarty->display($the_tpl['tcView']);
}
?>
