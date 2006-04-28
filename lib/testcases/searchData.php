<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: searchData.php,v 1.17 2006/04/28 17:34:58 franciscom Exp $
 * Purpose:  This page presents the search results. 
 *
 * 20060427 - franciscom - added include tescase class
 * 20050821 - fm - changes to use template customization (trying to reduce code redundancy)
**/
require('../../config.inc.php');
require_once("../functions/common.php");
require_once("../functions/users.inc.php");

require_once(dirname(__FILE__) . "/../functions/testcase.class.php"); // 20060427 - franciscom

testlinkInitPage($db);

$_POST = strings_stripSlashes($_POST);

//Assign the values of the posts to variables
$name = isset($_POST['name']) ? $_POST['name'] : null;
$summary = isset($_POST['summary']) ? $_POST['summary'] : null;
$steps = isset($_POST['steps']) ? $_POST['steps'] : null;
$expected_results = isset($_POST['expected_results']) ? $_POST['expected_results'] : null;

$keyword_id = isset($_POST['key']) ? $_POST['key'] : null;
$tc_id = isset($_POST['TCID']) ? $_POST['TCID'] : 0;
$version = isset($_POST['version']) ? $_POST['version'] : 0;


$arrTc = null;
$tproject = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tproject_mgr = new testproject($db);

// 20060428 - franciscom
$filter=array('by_tc_id' => '', 'by_name' => '', 
              'by_summary' => '', 'by_keyword_id' => '',
              'by_steps' => '', 'by_expected_results' => '');

$from  =array('by_keyword_id' => ' ');

if ($tproject > 0)
{
    // Due to changes in the DB schema we can't do a simple query to
    // filter testcase by Test project. We need to walk the tree hierarchy.
    //
    $a_tcid=$tproject_mgr->get_all_testcases_id($tproject);
    
    // Now start building the query to filter
		if( count($a_tcid) > 0 )
		{
			  if($tc_id > 0)
			  {
						$filter['by_tc_id'] = " AND NHB.parent_id = {$tc_id} ";
				}
				else
				{
						$filter['by_tc_id'] = " AND NHB.parent_id IN (" . implode(",",$a_tcid) . ") ";
				}

			  if($version > 0)
			  {
						$filter['by_version'] = " AND version = {$version} ";
				}
     
        if( !is_null($keyword_id) && $keyword_id > 0)				
        {
            $from['by_keyword_id'] = ' ,testcase_keywords KW';
        	  $filter['by_keyword_id'] = " AND NHA.id = KW.testcase_id 
        	                               AND KW.keyword_id = {$keyword_id} ";	
        }

        if( !is_null($name) && strlen(trim($name)) > 0)				
        {
            $name =  $db->prepare_string($name);
        	  $filter['by_name'] = " AND NHA.name like '%{$name}%' ";	
        }

      
        if( !is_null($summary) && strlen(trim($summary)) > 0)				
        {
            $summary = $db->prepare_string($_POST['summary']);
        	  $filter['by_summary'] = " AND summary like '%{$summary}%' ";	
        }    

        if( !is_null($steps) && strlen(trim($steps)) > 0)				
        {
            $steps = $db->prepare_string($_POST['steps']);
        	  $filter['by_steps'] = " AND steps like '%{$steps}%' ";	
        }    

        if( !is_null($expected_results) && strlen(trim($expected_results)) > 0)				
        {
            $expected_results = $db->prepare_string($_POST['expected_results']);
        	  $filter['by_expected_results'] = " AND steps like '%{$expected_results}%' ";	
        }    

				$sql = " SELECT NHA.id AS testcase_id,NHA.name,summary,steps,expected_results,version
	      			   FROM nodes_hierarchy NHA, nodes_hierarchy NHB, tcversions {$from['by_keyword_id']}
	        			 WHERE NHA.id = NHB.parent_id
	        			 AND   NHB.id = tcversions.id
	        			 {$filter['by_tc_id']} {$filter['by_name']}  {$filter['by_summary']}  
	        			 {$filter['by_steps']} {$filter['by_expected_results']}  
	        			 {$filter['by_keyword_id']} {$filter['by_version']}
	        			 ";  
		}

		$map = $db->fetchRowsIntoMap($sql,'testcase_id');
}


$smarty = new TLSmarty();
if( count($map) > 0 )
{
  $tcase_mgr = new testcase($db);   
  $tcase_mgr->show($smarty,array_keys($map), $_SESSION['userID']);
}

/*
$smarty->assign('modify_tc_rights', has_rights($db,"mgt_modify_tc"));
$smarty->assign('testcase', $arrTc);
$smarty->display($g_tpl['tcSearchView']);
*/
?>
