<?php

////////////////////////////////////////////////////////////////////////////////
// @version $Id: planAddTC.php,v 1.15 2006/03/20 18:02:33 franciscom Exp $
// File:     planAddTC.php
// Author:   Chad Rosen
// Purpose:  This page manages the importation of test cases into testlink.
//
// 20051001 - fm - refactoring
// 20050926 - fm - removed name from category and component insert
// 20051126 - scs - changed passing keyword to keywordID
////////////////////////////////////////////////////////////////////////////////
require('../../config.inc.php');
require("../functions/common.php");
require("../keywords/keywords.inc.php");
require("plan.inc.php");

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


echo "<pre>debug"; print_r($_POST); echo "</pre>"; 


// 20050807 - fm
$tplan_id =  $_SESSION['testPlanId'];

//Defining the keyword variable which is received from the left frame
$keywordID = isset($_REQUEST['key']) ? intval($_REQUEST['key']) : 0;
$keyword = "NONE";
$object_id=$_GET['data'];

$smarty = new TLSmarty;
$smarty->assign('testPlanName', $_SESSION['testPlanName']);

/*
20060311 - franciscom
if($keywordID)
{
	$keyword = getProductKeywords($db,$_SESSION['testprojectID'],null,$keywordID);
	if (sizeof($keyword))
		$keyword = $keyword[0];
	else
		$keyword = 'NONE';
}

if ($keyword != 'NONE')	
{
	$smarty->assign('key', $keyword);
}
*/

// ----------------------------------------------------------------------------------
// 20060311 - franciscom
if($_GET['edit'] == 'testsuite')
{
    $tsuite_data=$tsuite_mgr->get_by_id($object_id);
    $out=gen_spec_view($db,$object_id,$tsuite_data['name'],
                       $tplan_mgr->get_linked_tcversions($tplan_id));
 
    //echo "<pre>debug" . __FILE__ ; print_r($tplan_mgr->get_linked_tcversions($tplan_id)); echo "</pre>";                                    
     
    //echo "<pre>debug" . __FILE__ ; print_r($out); echo "</pre>";                                    
    echo "<pre>debug"; print_r($out); echo "</pre>"; 
  
    $do_display=1;  
//		$smarty->assign('arrData', $out);
//		$smarty->display('planAddTC_m1.tpl');
		//exit();
}
// ----------------------------------------------------------------------------------


// ----------------------------------------------------------------------------------
if(isset($_POST['link_tc']))
{

   	 
		// Remember checkboxes exists only when checked
		if( isset($_POST['achecked_tc']) )
		{
			  $atc=$_POST['achecked_tc'];
			  $atcversion=$_POST['tcversion_for_tcid'];
			  $items_to_link=my_array_intersect_keys($atc,$atcversion);
			  $tplan_mgr->link_tcversions($tplan_id,$items_to_link);
 
		}
    $tsuite_data=$tsuite_mgr->get_by_id($object_id);
    $out=gen_spec_view($db,$object_id,$tsuite_data['name'],
                       $tplan_mgr->get_linked_tcversions($tplan_id));

    
    $do_display=1;   
   //qta_checked_tc = count
   echo "<pre>debug out in link_tc "; print_r($out); echo "</pre>"; 
   //exit();
}


if( $do_display)
{
	  $smarty->assign('has_tc', ($out['num_tc'] > 0 ? 1:0));
		$smarty->assign('arrData', $out['spec_view']);
		$smarty->display('planAddTC_m1.tpl');
}



?>



<?php
/*
returns: array where every element is an associative array with the following
         structure:
         [testsuite] => Array( [id] => 28
                          [name] => TS1 )

         [testcases] => Array( [0] => Array( [id] => 79
                                             [name] => TC0)

                               [1] => Array( [id] => 81
            
                                             [name] => TC88))

         [tcversions] => Array( 79 => 1, 82 => 2)  
                         where key=tcversion id
                               value=version


*/
function gen_spec_view(&$db,$id,$name,&$linked_items)
{
	  $tcase_mgr = New testcase($db); 

    echo "<pre>debug lik " . __FUNCTION__; print_r($linked_items); echo "</pre>";
		$result = array('spec_view'=>array(), 'num_tc' => 0);

		$out = array(); 
    $a_tcid=array();
     
		$tree_manager = New tree($db);
    $test_spec = $tree_manager->get_subtree($id,array('testplan'=>'exclude me'),
                                                array('testcase'=>'exclude my_children'));
  
  	$hash_descr_id = $tree_manager->get_available_node_types();
  	$hash_id_descr = array_flip($hash_descr_id);
  
  
    $idx=0;
    $a_tcid=array();
    $a_tsuite_idx=array();
  	$hash_id_pos[$id]=$idx;
  	$out[$idx]['testsuite']=array('id' => $id, 'name' => $name);
  	$out[$idx]['testcases']=array();
  	$out[$idx]['write_buttons']='no';
    $idx++;
  	
  	if( count($test_spec) > 0 )
  	{
   			$pivot=$test_spec[0];
   			$the_level=1;
    		$level=array();
  
   			foreach ($test_spec as $elem)
   			{
   	 				$current = $elem;

     				if( $pivot['parent_id'] == $current['parent_id'])
     				{
       					$the_level=$the_level;
     				}
     				else if ($pivot['id'] == $current['parent_id'])
     				{
     	  				$the_level++;
     	  				$level[$current['parent_id']]=$the_level;
     				}
     				else 
     				{
     	  				$the_level=$level[$current['parent_id']];
     				}
            
            if( $hash_id_descr[$current['node_type_id']] == "testcase")
            {
            	  $tc_id = $current['id'];
            		$parent_idx=$hash_id_pos[$current['parent_id']];
              	$a_tsuite_idx[$tc_id]=$parent_idx;
              	
              	$out[$parent_idx]['testcases'][$tc_id]=array('id' => $tc_id,
     				                                                 'name' => $current['name']);
              	$out[$parent_idx]['testcases'][$tc_id]['tcversions']=array();             
                $out[$parent_idx]['testcases'][$tc_id]['linked_version_id']=0;

                $out[$parent_idx]['write_buttons']='yes';
                $a_tcid[]=$current['id'];
            }
            else
            {
              	$out[$idx]['testsuite']=array('id' => $current['id'],
     				                             'name' => $current['name']);
  	            $out[$idx]['testcases']=array();
  	            $out[$idx]['write_buttons']='no';
  	            $hash_id_pos[$current['id']]=$idx;
     				    $idx++;
	          }

     				// update pivot
     				$level[$current['parent_id']]= $the_level;
     				$pivot=$elem;
   			}
		}

    //echo "<pre>debug" . __FILE__ ; print_r($linked_items); echo "</pre>";                                    

    // Loop to get test case version information		
		$result['num_tc']=count($a_tcid);

    if( $result['num_tc'] > 0 )
    {
				$tcase_set=$tcase_mgr->get_by_id($a_tcid);
		
    		foreach($tcase_set as $the_k => $the_tc)
    		{
    			echo "<pre>debug SONO \$the_tc in " . __FUNCTION__ ; print_r($the_tc); echo "</pre>";
    			$tc_id = $the_tc['testcase_id'];
    		  $parent_idx=$a_tsuite_idx[$tc_id];
          $out[$parent_idx]['testcases'][$tc_id]['tcversions'][$the_tc['id']]= $the_tc['version'];
            
          if( !is_null($linked_items) )
          {
              foreach($linked_items as $the_item)
        		  {
        		     if( ($the_item['tc_id'] == $the_tc['testcase_id']) &&
        		         ($the_item['tcversion_id'] == $the_tc['id']) )
        		     {
        		     	   $out[$parent_idx]['testcases'][$tc_id]['linked_version_id']=$the_item['tcversion_id'];
        		         break;
        		     }
              }
          }    
    		}
		}
		
		//echo "<pre>debug SONO **** OUT in " . __FUNCTION__ ; print_r($out); echo "</pre>";
		
		$result['spec_view']=$out;
		
		return($result);
}


// 20060318 - franciscom
function my_array_intersect_keys($array1,$array2)
{
    $aresult=array();
		foreach($array1 as $key => $val)
		{
		  	if(isset($array2[$key]))
		  	{
		  			$aresult[$key]=$array2[$key];
		  	} 	
		}	
		return($aresult);	
}


?>