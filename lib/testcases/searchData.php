<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: searchData.php,v 1.15 2006/04/26 07:07:56 franciscom Exp $
 * Purpose:  This page presents the search results. 
 *
 * 20050821 - fm - changes to use template customization (trying to reduce code redundancy)
**/
require('../../config.inc.php');
require_once("../functions/common.php");
require_once("../functions/users.inc.php");
//require("../functions/testproject.class.php");


testlinkInitPage($db);

$_POST = strings_stripSlashes($_POST);

//Assign the values of the posts to variables
$name = isset($_POST['name']) ? $db->prepare_string($_POST['name']) : null;
$summary = isset($_POST['summary']) ? $db->prepare_string($_POST['summary']) : null;
$steps = isset($_POST['steps']) ? $db->prepare_string($_POST['steps']) : null;
$exresult = isset($_POST['exresult']) ? $db->prepare_string($_POST['exresult']) : null;
$key = isset($_POST['key']) ? $db->prepare_string($_POST['key']) : null;
$tc_id = isset($_POST['TCID']) ? $db->prepare_string($_POST['TCID']) : 0;

$arrTc = null;
$tproject = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tproject_mgr = new testproject($db);


$filter=array('by_tc_id' => '', 'by_name' => '', 'by_summary' => '');

if ($tproject > 0)
{
    $a_tcid=$tproject_mgr->get_all_testcases_id($tproject);
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

        if( !is_null($name) )				
        {
        	  $filter['by_name'] = " AND NHA.name like '%{$name}%' ";	
        }
     
				$sql = " SELECT NHA.id AS testcase_id,NHA.name,summary,steps,version
	      			   FROM nodes_hierarchy NHA, nodes_hierarchy NHB, tcversions
	        			 WHERE NHA.id = NHB.parent_id
	        			 AND   NHB.id = tcversions.id
	        			 {$filter['by_tc_id']} {$filter['by_name']}
	        			 ";  
	        echo "<br>debug - <b><i>" . __FUNCTION__ . "</i></b><br><b>" . $sql . "</b><br>";
  			 
		}

		$map = $db->fetchRowsIntoMap($sql,'testcase_id');

   echo "<pre>debug" . __FUNCTION__; print_r($map); echo "</pre>"; 

}


$smarty = new TLSmarty();
if( count($map) > 0 )
{
  $tcase_mgr = new testcase($db);   
  //foreach($map as $tcase_id => $tcase_data)
  //{
    $tcase_mgr->show_multi($smarty,array_keys($map), $_SESSION['userID']);
  //}
  
}

/*
$smarty->assign('modify_tc_rights', has_rights($db,"mgt_modify_tc"));
$smarty->assign('testcase', $arrTc);
$smarty->display($g_tpl['tcSearchView']);
*/
?>
