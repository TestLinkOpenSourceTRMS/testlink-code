<?php

////////////////////////////////////////////////////////////////////////////////
// @version $Id: planAddTC.php,v 1.18 2006/05/03 08:30:07 franciscom Exp $
// File:     planAddTC.php
// Author:   Chad Rosen
// Purpose:  This page manages the importation of test cases into testlink.
//
// 20051001 - fm - refactoring
// 20050926 - fm - removed name from category and component insert
// 20051126 - scs - changed passing keyword to keywordID
////////////////////////////////////////////////////////////////////////////////
require('../../config.inc.php');

// 20060425 - franciscom - require_once
require_once("../functions/common.php");
require_once("../keywords/keywords.inc.php");
require_once("plan.inc.php");

// 20060311 - franciscom
require_once('testsuite.class.php');
require_once('testproject.class.php');
require_once('tree.class.php');
require_once('testplan.class.php');


testlinkInitPage($db);

// 20060311 - franciscom
//$tproject_mgr = New testproject($db); 
//$tcase_mgr = New testcase($db); 

$tree_mgr = New tree($db); 
$tsuite_mgr = New testsuite($db); 
$tplan_mgr = New testplan($db); 

$tplan_id =  $_SESSION['testPlanId'];
$tproject_id =  $_SESSION['testprojectID'];

$keyword_id = isset($_REQUEST['keyword_id']) ? intval($_REQUEST['keyword_id']) : 0;
$object_id=$_GET['data'];

echo "<pre>debug -- \$_REQUEST --"; print_r($_REQUEST); echo "</pre>";

$smarty = new TLSmarty;
$smarty->assign('testPlanName', $_SESSION['testPlanName']);

define('DONT_FILTER_BY_TCASE_ID',null);

// ----------------------------------------------------------------------------------
// 20060311 - franciscom
if($_GET['edit'] == 'testsuite')
{
    
    $tsuite_data=$tsuite_mgr->get_by_id($object_id);
    $out=gen_spec_view($db,'testproject',
                       $tproject_id,$object_id,$tsuite_data['name'],
                       $tplan_mgr->get_linked_tcversions($tplan_id,DONT_FILTER_BY_TCASE_ID,$keyword_id),
                       $keyword_id,DONT_FILTER_BY_TCASE_ID);
                       
    echo "<pre>debug \$out -- "; print_r($out); echo "</pre>";                   
    $do_display=1;  
}
else
{
	redirect($_SESSION['basehref'] .$g_rpath['help'].'/planAddTC.html');
}
// ----------------------------------------------------------------------------------


// ----------------------------------------------------------------------------------
if(isset($_POST['do_action']))
{
		// Remember checkboxes exists only when checked
		if( isset($_POST['achecked_tc']) )
		{
			  $atc=$_POST['achecked_tc'];
			  $atcversion=$_POST['tcversion_for_tcid'];
			  $items_to_link=my_array_intersect_keys($atc,$atcversion);
			  $tplan_mgr->link_tcversions($tplan_id,$items_to_link);
 
		}
		
		if( isset($_POST['remove_checked_tc']) )
		{
        // remove without warning
			  $rtc=$_POST['remove_checked_tc'];
        $tplan_mgr->unlink_tcversions($tplan_id,$rtc);      
  	}
		

    $tsuite_data=$tsuite_mgr->get_by_id($object_id);
    $out=gen_spec_view($db,'testproject',
                       $tproject_id,$object_id,$tsuite_data['name'],
                       $tplan_mgr->get_linked_tcversions($tplan_id,DONT_FILTER_BY_TCASE_ID,$keyword_id),
                       $keyword_id,DONT_FILTER_BY_TCASE_ID);

    
    $do_display=1;   
}


if( $do_display)
{


	  $smarty->assign('has_tc', ($out['num_tc'] > 0 ? 1:0));
		$smarty->assign('arrData', $out['spec_view']);
	  $smarty->assign('has_linked_items',$out['has_linked_items']);

		$smarty->display('planAddTC_m1.tpl');
}
?>