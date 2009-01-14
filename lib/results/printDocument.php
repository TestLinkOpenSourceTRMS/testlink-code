<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: printDocument.php,v $
 *
 * @version $Revision: 1.16 $
 * @modified $Date: 2009/01/14 19:33:01 $ by $Author: schlundus $
 * @author Martin Havlat
 *
 * SCOPE:
 * Generate documentation Test report based on Test plan data.
 *
 * Revisions :
 *      20081207 - franciscom - BUGID 1910 - fixed estimated execution time computation.  
 *      20070509 - franciscom - added Contribution BUGID
 *
 */
require_once('../../config.inc.php');
require_once("common.php");
require_once("print.inc.php");
testlinkInitPage($db);

$statistics=null;
$args = init_args();

// Elements in this array must be updated if $arrCheckboxes, in selectData.php is changed.
$printingOptions = array ( 'toc' => 0,'body' => 0,'summary' => 0,'header' => 0,
						               'passfail' => 0, 'author' => 0, 'requirement' => 0, 'keyword' => 0);

foreach($printingOptions as $opt => $val)
{
	$printingOptions[$opt] = (isset($_REQUEST[$opt]) && ($_REQUEST[$opt] == 'y'));
}					

$dummy = null;

$tproject_mgr = new testproject($db);
$tree_manager = &$tproject_mgr->tree_manager;

$hash_descr_id = $tree_manager->get_available_node_types();
$hash_id_descr = array_flip($hash_descr_id);

$resultsCfg = config_get('results');
// $statusCode = $resultsCfg['status_code'];
// $status_descr_code = config_get('tc_status');
$status_descr_code = $resultsCfg['status_code'];

$status_code_descr = array_flip($status_descr_code);

$decoding_hash = array('node_id_descr' => $hash_id_descr,
                     'status_descr_code' =>  $status_descr_code,
                     'status_code_descr' =>  $status_code_descr);


$test_spec = $tree_manager->get_subtree($args->itemID,
										array(
											'testplan'=>'exclude me',
											'requirement_spec'=>'exclude me',
											'requirement'=>'exclude me'),
											array('testcase'=>'exclude my children',
											'requirement_spec'=> 'exclude my children'),
											null,null,RECURSIVE_MODE
										);

$tree = null;
$generatedText = null;					
$item_type = $args->level;

switch ($args->print_scope)
{
    case 'testproject':
    	  switch($item_type)
    	  {
    	      case 'testproject':
    	          $tree = &$test_spec;
    	  	      $printingOptions['title'] = '';
    	      break;
    	      
    	      case 'testsuite':
    	      	  $tsuite = new testsuite($db);
    	  	      $tInfo = $tsuite->get_by_id($args->itemID);
    	  	      $tInfo['childNodes'] = isset($test_spec['childNodes']) ? $test_spec['childNodes'] : null;
    	  	      $tree['childNodes'] = array($tInfo);
    	  	      $printingOptions['title'] = isset($tInfo['name']) ? $tInfo['name'] : $args->tproject_name;
    	  	  break;    
    	  }
    	  break;
    
    case 'testplan':
    	   $tplan_mgr = new testplan($db);
         $tcase_filter = null;
         $execid_filter = null;
         $executed_qty = 0;
         
         switch($item_type)
         {
             case 'testproject':
    	   	       $tp_tcs = $tplan_mgr->get_linked_tcversions($args->tplan_id);
    	   	       $tree = &$test_spec;
    	   	       if (!$tp_tcs)
    	   	       {
    	   	           $tree['childNodes'] = null;
    	   	       }
    	   	       //@TODO:REFACTOR	
    	   	       prepareNode($db,$tree,$decoding_hash,$dummy,
    	   	                   $dummy,$tp_tcs,SHOW_TESTCASES,null,null,0,1,0);
    	   	       $printingOptions['title'] = $args->tproject_name;
             break;
    	       
    	       case 'testsuite':
                 $tsuite = new testsuite($db);
    	   	       $tInfo = $tsuite->get_by_id($args->itemID);
                 
    	           $children_tsuites=$tree_manager->get_subtree_list($args->itemID,$hash_descr_id['testsuite']);
    	           if( !is_null($children_tsuites) and strlen(trim($children_tsuites)) > 0)
    	           {
                     $branch_tsuites = explode(',',$children_tsuites);
                 }
                 $branch_tsuites[]=$args->itemID;
    	   	       
    	   	       
    	   	       $tp_tcs = $tplan_mgr->get_linked_tcversions($args->tplan_id,null,0,null,null,null,0,null,false,null, 
    	   	                                                   $branch_tsuites);
    	   	       $tcase_filter=array_keys($tp_tcs);
    	         
    	   	       $tInfo['node_type_id'] = $hash_descr_id['testsuite'];
    	   	       $tInfo['childNodes'] = isset($test_spec['childNodes']) ? $test_spec['childNodes'] : null;
    	   	       
    	   	       //@TODO: schlundus, can we speed up with NO_EXTERNAL?
    	   	       prepareNode($db,$tInfo,$decoding_hash,$dummy,$dummy,$tp_tcs,SHOW_TESTCASES);
    	   	       $printingOptions['title'] = isset($tInfo['name']) ? $tInfo['name'] : $args->tproject_name;
                  
    	   	       $tree['childNodes'] = array($tInfo);
             break;
         }  // switch($item_type)
         
         // Create list of execution id, that will be used to compute execution time if
    	   // CF_EXEC_TIME custom field exists and is linked to current testproject                                            
         $executed_qty=0;
    	 if ($tp_tcs)
    	 {
    	 	foreach($tp_tcs as $tcase_id => $info)
	    	{
	             if( $info['exec_status'] != $status_descr_code['not_run'] )
	             {  
	                 $execid_filter[]=$info['exec_id'];
	                 $executed_qty++;
	             }    
	         }    
    	 }
         $statistics['estimated_execution']['minutes']=$tplan_mgr->get_estimated_execution_time($args->tplan_id,$tcase_filter);
         $statistics['estimated_execution']['tcase_qty']=count($tp_tcs);
         
         if( $executed_qty > 0)
         { 
             $statistics['real_execution']['minutes']=$tplan_mgr->get_execution_time($args->tplan_id,$execid_filter);
             $statistics['real_execution']['tcase_qty']=$executed_qty;
         }
    break;
}

if($tree)
{
	$tree['name'] = $args->tproject_name;
	$tree['id'] = $args->tproject_id;
	$tree['node_type_id'] = $hash_descr_id['testproject'];
	switch ($args->print_scope)
	{
		case 'testproject':
			$generatedText = renderTestSpecTreeForPrinting($db,$tree,$item_type,$printingOptions,null,0,1,$args->user_id);
			break;
	
		case 'testplan':
			$generatedText = renderTestPlanForPrinting($db,$tree,$item_type,$printingOptions,null,0,1,
		                                             $args->user_id,$args->tplan_id,$args->tproject_id,
		                                             $statistics);
		    break;
	}
}

// add MS Word header to HTTP 
if ($args->format == 'msword')
{
	header("Content-Disposition: inline; filename=testplan.doc");
	header("Content-Description: PHP Generated Data");
	header("Content-type: application/vnd.ms-word; name='My_Word'");
	flush();
}
echo $generatedText;


/** Process input data */
function init_args()
{
	$args = new stdClass();
	$args->print_scope = $_REQUEST['print_scope'];
	$args->level = isset($_REQUEST['level']) ?  $_REQUEST['level'] : null;
	$args->format = isset($_REQUEST['format']) ? $_REQUEST['format'] : null;
	$args->itemID = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	
	$args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
	$args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : 'xxx';
	$args->tplan_id = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;
	$args->user_id = isset($_SESSION['userID']) ? intval($_SESSION['userID']) : null;

	return $args;
}
?>