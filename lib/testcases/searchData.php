<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: searchData.php,v 1.16 2006/04/28 17:07:41 franciscom Exp $
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
$name = isset($_POST['name']) ? $db->prepare_string($_POST['name']) : null;
$summary = isset($_POST['summary']) ? $db->prepare_string($_POST['summary']) : null;
$steps = isset($_POST['steps']) ? $db->prepare_string($_POST['steps']) : null;
$exresult = isset($_POST['exresult']) ? $db->prepare_string($_POST['exresult']) : null;
$keyword_id = isset($_POST['key']) ? $db->prepare_string($_POST['key']) : null;
$tc_id = isset($_POST['TCID']) ? $db->prepare_string($_POST['TCID']) : 0;

$arrTc = null;
$tproject = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tproject_mgr = new testproject($db);

// 20060427 - franciscom
$filter=array('by_tc_id' => '', 'by_name' => '', 
              'by_summary' => '', 'by_keyword_id' => '');
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

        if( !is_null($name) && strlen(trim($name)) > 0)				
        {
        	  $filter['by_name'] = " AND NHA.name like '%{$name}%' ";	
        }

        if( !is_null($keyword_id) )				
        {
            $from['by_keyword_id'] = ' ,testcase_keywords KW';
        	  $filter['by_keyword_id'] = " AND NHA.id = KW.testcase_id 
        	                               AND KW.keyword_id = {$keyword_id} ";	
        }

     
				$sql = " SELECT NHA.id AS testcase_id,NHA.name,summary,steps,version
	      			   FROM nodes_hierarchy NHA, nodes_hierarchy NHB, tcversions {$from['by_keyword_id']}
	        			 WHERE NHA.id = NHB.parent_id
	        			 AND   NHB.id = tcversions.id
	        			 {$filter['by_tc_id']} {$filter['by_name']} {$filter['by_keyword_id']}
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
