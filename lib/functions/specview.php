<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * @filesource $RCSfile: specview.php,v $
 * @version $Revision: 1.22 $ $Author: schlundus $
 * @modified $Date: 2008/10/31 20:16:43 $
 *
 * @author 	Francisco Mancardi (francisco.mancardi@gmail.com)
 *
 * rev:
 *     20081030 - franciscom - created removeEmptyTestSuites(), removeEmptyBranches() to refactor.
 *                             refactored use of tproject_id on gen_spec_view()
 *
 *     20081019 - franciscom - removed new option to prune empty test suites
 *                             till we understand were this will be used.
 *                             In today implementation causes problems
 *                             Added logic to compute total count of test cases
 *                             for every test suite in a branch, to avoid use
 *                             of map_node_tccount argument
 *
 *     20081004 - franciscom - minor code clean up
 *     20080919 - franciscom - BUGID 1716
 *     20080811 - franciscom - BUGID 1650 (REQ)
 *                             documentation improvements
 *
 *     20080528 - franciscom - internal bug - only one version was retrieved
 *     20080510 - franciscom - added getFilteredLinkedVersions()
 *                                   keywordFilteredSpecView()
 *
 *     20080422 - franciscom - BUGID 1497
 *     Suggested by Martin Havlat execution order will be set to external_id * 10
 *     for test cases not linked yet
 *           
 **/ 

/*
arguments:
          spec_view_type: can get one of the following values:
                          'testproject','testplan'
                          
                          This setting change the processing done 
                          to get the keywords.
                          And indicates the type of id (testproject/testplan) 
                          contained in the argument tobj_id.

         tobj_id
         
         id: node id, that we are using as root for the view we want to build

         name:
         linked_items:  map where key=testcase_id
                        value map with following keys:
                              [testsuite_id] => 2732            
                              [tc_id] => 2733        
                              [z] => 100  ---> nodes_hierarchy.order             
                              [name] => TC1          
                              [tcversion_id] => 2734 
                              [feature_id] => 9      --->> testplan_tcversions.ID
                              [execution_order] => 10
                              [version] => 1         
                              [active] => 1          
                              [external_id] => 1     
                              [exec_id] => 1         
                              [tcversion_number] => 1
                              [executed] => 2734     
                              [exec_on_tplan] => 2735
                              [user_id] =>           
                              [type] =>              
                              [status] =>            
                              [assigner_id] =>       
                              [urgency] => 2         
                              [exec_status] => b     
                        
                        
                        
         map_node_tccount,
                            
         [keyword_id] default 0
         [tcase_id] default null, can be an array
			   [write_button_only_if_linked] default 0
                        
                        
         [prune_unlinked_tcversions]: default 0. 
                     Useful when working on spec_view_type='testplan'.
                     1 -> will return only linked tcversion
                     0 -> returns all test cases specs. 
                        
         [add_custom_fields]: default=0
                              useful when working on spec_view_type='testproject'
                              when doin test case assign to test plans.
                              1 -> for every test case cfields of area 'testplan_design'
                                   will be fetched and displayed.
                              0 -> do nothing     
         [$tproject_id]: default = null
                         useful to improve performance in custom field method calls
                         when add_custom_fields=1.
                         @TODO probably this argument is not needed, but it will depend
                         of how this feature (gen_spec_view) will be used on other TL areas.
                        
returns: array where every element is an associative array with the following
         structure: (to get last updated info add debug code and print_r returned value)
        
         [testsuite] => Array( [id] => 28
                               [name] => TS1 )

         [testcases] => Array(  [2736] => Array
                                (
                                    [id] => 2736
                                    [name] => TC2
                                    [tcversions] => Array
                                        (
                                            [2738] => 2   // key=tcversion id,value=version
                                            [2737] => 1
                                        )

                                    [tcversions_active_status] => Array
                                        (
                                            [2738] => 1  // key=tcversion id,value=active status
                                            [2737] => 1
                                        )

                                    [tcversions_execution_type] => Array
                                        (
                                            [2738] => 1
                                            [2737] => 2
                                        )

                                    [tcversions_qty] => 2
                                    [linked_version_id] => 2737
                                    [executed] => no
                                    [user_id] => 0       ---> !=0 if execution has been assigned
                                    [feature_id] => 12   ---> testplan_tcversions.id
                                    [execution_order] => 20
                                    [external_id] => 2
                                )

                               [81] => Array( [id] => 81
                                             [name] => TC88)
                                             ...
                                             )

       [level] =  
       [write_buttons] => yes or no

       level and write_buttons are used to generate the user interface
       
       
       Warning:
       if the root element of the spec_view, has 0 test => then the default
       structure is returned ( $result = array('spec_view'=>array(), 'num_tc' => 0))


20070707 - franciscom - BUGID 921 - problems with display order in execution screen

20070630 - franciscom
added new logic to include in for inactive test cases, testcase version id.
This is needed to show testcases linked to testplans, but after be linked to
test plan, has been set to inactive on test project.

20061105 - franciscom
added new data on output: [tcversions_qty] 
                          used in the logic to filter out inactive tcversions,
                          and inactive test cases.
                          Counts the quantity of active versions of a test case.
                          If 0 => test case is considered INACTIVE
                                          
       
*/
function gen_spec_view(&$db,$spec_view_type='testproject',
                            $tobj_id,$id,$name,&$linked_items,
                            $map_node_tccount,$keyword_id = 0,$tcase_id = null,
							              $write_button_only_if_linked = 0,
							              $prune_unlinked_tcversions=0,$add_custom_fields=0,$tproject_id = null)
{
	  $write_status = $write_button_only_if_linked ? 'no' : 'yes';
	  $is_tplan_view_type=$spec_view_type == 'testplan' ? 1 : 0;

    if( !$is_tplan_view_type && is_null($tproject_id) )
    {
        $tproject_id=$tobj_id;
    }
	  
	  $result = array('spec_view'=>array(), 'num_tc' => 0, 'has_linked_items' => 0);
	  $out = array(); 
	  
	  $tcase_mgr = new testcase($db); 
	  
	  $hash_descr_id = $tcase_mgr->tree_manager->get_available_node_types();
	  $hash_id_descr = array_flip($hash_descr_id);
    
    $filters=array('keyword_id' => $keyword_id, 'tcase_id' => $tcase_id);
    $test_spec = getTestSpecFromNode($db,$tobj_id,$id,$spec_view_type,$filters);
    $idx = 0;
    $a_tcid = array();
    $a_tsuite_idx = array();
  	$hash_id_pos[$id] = $idx;
  	$out[$idx]['testsuite'] = array('id' => $id, 'name' => $name);
  	$out[$idx]['testcases'] = array();
  	$out[$idx]['write_buttons'] = 'no';
  	$out[$idx]['testcase_qty'] = 0;
  	$out[$idx]['level'] = 1;
    
    $idx++;
    $tsuite_tcqty=array($id => 0);
    $parent_idx=-1;
    
  	if(count($test_spec))
  	{
  		$pivot = $test_spec[0];
  		$the_level = $out[0]['level']+1;
  		$level = array();
  
  		foreach ($test_spec as $current)
  		{
  			if(is_null($current))
  				continue;

  			if($hash_id_descr[$current['node_type_id']] == "testcase")
  			{
  				$tc_id = $current['id'];
  				$parent_idx = $hash_id_pos[$current['parent_id']];
  				$a_tsuite_idx[$tc_id] = $parent_idx;
  				$out[$parent_idx]['testcases'][$tc_id] = array('id' => $tc_id,'name' => $current['name']);
  				$out[$parent_idx]['testcases'][$tc_id]['tcversions'] = array();
  				$out[$parent_idx]['testcases'][$tc_id]['tcversions_active_status'] = array();

  				// 20080811 - franciscom - BUGID 1650 (REQ)
  				$out[$parent_idx]['testcases'][$tc_id]['tcversions_execution_type'] = array();
  				
  				$out[$parent_idx]['testcases'][$tc_id]['tcversions_qty'] = 0;
  				$out[$parent_idx]['testcases'][$tc_id]['linked_version_id'] = 0;
  				$out[$parent_idx]['testcases'][$tc_id]['executed'] = 'no';

  				$out[$parent_idx]['write_buttons'] = $write_status;
  				$out[$parent_idx]['testcase_qty']++;
  				$out[$parent_idx]['linked_testcase_qty'] = 0;
  				
  				// useful for tc_exec_assignment.php          
  				$out[$parent_idx]['testcases'][$tc_id]['user_id'] = 0;
  				$out[$parent_idx]['testcases'][$tc_id]['feature_id'] = 0;
  				
  				$a_tcid[] = $current['id'];
  			}
  			else
  			{
  			  if($parent_idx >= 0)
  			  { 
  			      $xdx=$out[$parent_idx]['testsuite']['id'];
  			      $tsuite_tcqty[$xdx]=$out[$parent_idx]['testcase_qty'];
  			  }
  			  
  				if($pivot['parent_id'] != $current['parent_id'])
  				{
  					if ($pivot['id'] == $current['parent_id'])
  					{
  						$the_level++;
  						$level[$current['parent_id']] = $the_level;
  					}
  					else 
  						$the_level = $level[$current['parent_id']];
  				}
  				$out[$idx]['testsuite']=array('id' => $current['id'], 'name' => $current['name']);
  				$out[$idx]['testcases'] = array();
  				$out[$idx]['testcase_qty'] = 0;
  				$out[$idx]['linked_testcase_qty'] = 0;
  				$out[$idx]['level'] = $the_level;
  				$out[$idx]['write_buttons'] = 'no';
  				$hash_id_pos[$current['id']] = $idx;
  				$idx++;
  				    
  				// update pivot.
  				$level[$current['parent_id']] = $the_level;
      		$pivot = $current;
  	 
  		    }
  		} // foreach
  		
  		// Update after finished loop
  		if($parent_idx >= 0)
  		{ 
  		    $xdx=$out[$parent_idx]['testsuite']['id'];
  			$tsuite_tcqty[$xdx]=$out[$parent_idx]['testcase_qty'];
  		}
	} // count($test_spec))
	$tsuite_tcqty[$id] = $out[$hash_id_pos[$id]]['testcase_qty'];
  // This code has been replace (see below on Remove empty branches)
  // Once we have created array with testsuite and children testsuites
  // we are trying to remove nodes that has 0 test case count.
  // May be this can be done (as noted by schlundus during performance
  // analisys done on october 2008) in a better way, or better can be absolutely avoided.
  // 
  // This process is neede to prune whole branches that are empty
	// Need to look for every call in TL and understand if this can be removed
	//
	if(!is_null($map_node_tccount))
	{
		foreach($out as $key => $elem)
		{
			if(isset($map_node_tccount[$elem['testsuite']['id']]) &&
				$map_node_tccount[$elem['testsuite']['id']]['testcount'] == 0)  
				{
				  // why not unset ?
				  // 
				  $out[$key]=null;
				}
			}
	}
	   
  // and now ???
	if( !is_null($out[0]) )
	{
	  $result['has_linked_items'] = 0;
    if(count($a_tcid))
    {
  		$tcase_set = $tcase_mgr->get_by_id($a_tcid,TC_ALL_VERSIONS);
  		$result['num_tc']=0;
  		$pivot_id=-1;

  		foreach($tcase_set as $the_k => $the_tc)
    	{
			  $tc_id = $the_tc['testcase_id'];
  		  if($pivot_id != $tc_id )
  		  {
  		    $pivot_id=$tc_id;
  		    $result['num_tc']++;
  		  }
  			$parent_idx = $a_tsuite_idx[$tc_id];
  		
        // --------------------------------------------------------------------------
        if($the_tc['active'] == 1 && !is_null($out[$parent_idx]) )
        {       
  	      if( !isset($out[$parent_idx]['testcases'][$tc_id]['execution_order']) )
  	      {
              // Doing this I will set order for test cases that still are not linked.
              // But Because I loop over all version (linked and not) if I always write, 
              // will overwrite right execution order of linked tcversion.
              //
              // N.B.:
              // As suggested by Martin Havlat order will be set to external_id * 10
  	          $out[$parent_idx]['testcases'][$tc_id]['execution_order'] = $the_tc['tc_external_id']*10;
          } 
    			$out[$parent_idx]['testcases'][$tc_id]['tcversions'][$the_tc['id']] = $the_tc['version'];
  				$out[$parent_idx]['testcases'][$tc_id]['tcversions_active_status'][$the_tc['id']] = 1;
          $out[$parent_idx]['testcases'][$tc_id]['external_id'] = $the_tc['tc_external_id'];
  				  
  				// 20080811 - franciscom - BUGID 1650
  				$out[$parent_idx]['testcases'][$tc_id]['tcversions_execution_type'][$the_tc['id']] = $the_tc['execution_type'];
            
  				  
		    	if (isset($out[$parent_idx]['testcases'][$tc_id]['tcversions_qty']))  
				     $out[$parent_idx]['testcases'][$tc_id]['tcversions_qty']++;
			    else
				     $out[$parent_idx]['testcases'][$tc_id]['tcversions_qty'] = 1;
        }
        
  			if(!is_null($linked_items))
  			{
  				foreach($linked_items as $linked_testcase)
  				{
  					if(($linked_testcase['tc_id'] == $the_tc['testcase_id']) &&
  						($linked_testcase['tcversion_id'] == $the_tc['id']) )
  					{
       				if( !isset($out[$parent_idx]['testcases'][$tc_id]['tcversions'][$the_tc['id']]) )
       				{
        				$out[$parent_idx]['testcases'][$tc_id]['tcversions'][$the_tc['id']] = $the_tc['version'];
  	    			  $out[$parent_idx]['testcases'][$tc_id]['tcversions_active_status'][$the_tc['id']] = 0;
                $out[$parent_idx]['testcases'][$tc_id]['external_id'] = $the_tc['tc_external_id'];

                // 20080811 - franciscom - BUGID 1650 (REQ)
  	    			  $out[$parent_idx]['testcases'][$tc_id]['tcversions_execution_type'][$the_tc['id']] = $the_tc['execution_type'];
				      }
  						$out[$parent_idx]['testcases'][$tc_id]['linked_version_id'] = $linked_testcase['tcversion_id'];
              $exec_order= isset($linked_testcase['execution_order'])? $linked_testcase['execution_order']:0;
              $out[$parent_idx]['testcases'][$tc_id]['execution_order'] = $exec_order;
  						$out[$parent_idx]['write_buttons'] = 'yes';
  						$out[$parent_idx]['linked_testcase_qty']++;
  						
  						$result['has_linked_items'] = 1;
  						
  						if(intval($linked_testcase['executed']))
  							$out[$parent_idx]['testcases'][$tc_id]['executed']='yes';
  						
  						if( isset($linked_testcase['user_id']))
  							$out[$parent_idx]['testcases'][$tc_id]['user_id']=intval($linked_testcase['user_id']);
  						if( isset($linked_testcase['feature_id']))
  							$out[$parent_idx]['testcases'][$tc_id]['feature_id']=intval($linked_testcase['feature_id']);
  						break;
  					}
  				}
  			} 
  		} //foreach($tcase_set
  	} 
  	$result['spec_view'] = $out;
  	} // !is_null($out[0])
	// --------------------------------------------------------------------------------------------
	unset($out);
	
	// Try to prune empty test suites, to reduce memory usage and to remove elements
	// that do not need to be displayed on user interface.
	if( count($result['spec_view']) > 0)
	{
	  removeEmptyTestSuites($result['spec_view'],$tcase_mgr->tree_manager,
	                        ($prune_unlinked_tcversions && $is_tplan_view_type),$hash_descr_id);
	}
	
  // -----------------------------------------------------------------------------------------------
  // Remove empty branches
  // Loop to compute test case qty on every level and prune test suite branchs that are empty
 	if( count($result['spec_view']) > 0)
	{
      removeEmptyBranches($result['spec_view'],$tsuite_tcqty);
  }   
  // -----------------------------------------------------------------------------------------------

	//@TODO: maybe we can integrate this into already present loops above?
	//
	// 20081019 - franciscom 
	// This is not right condition for identifing an empty test suite for the porpouse
	// of gen_spec_view(), because for following structure
	// TS1 
	//  \--- TS2
	//        \--TC1
	//        \--TC2
	//
	//  \--- TS3 
	//        \-- TXX
	//
	// When we are displaying a Test Specification we want to see previous structure
	// But if we apply this criteria for empty test suite, TS1 results empty and will
	// be removed -> WRONG
	//
	// Need to understand when this feature will be needed and then reimplement
	//
	// if ($prune_empty_tsuites)
	// {
	// 	foreach($result['spec_view'] as $key => $value) 
	//     {
	//    		if(is_null($value) || !isset($value['testcases']) || !count($value['testcases']))
	//       		unset($result['spec_view'][$key]);
	//     }
	// }
	// --------------------------------------------------------------------------------------------

	// --------------------------------------------------------------------------------------------
	// BUGID 1650 (REQ)
  // We want to manage custom fields when user is doing test case execution assigment
	if( count($result['spec_view']) > 0 && $add_custom_fields)
	{    
	  // Future implementation to improve function readability                      
    // addCustomFieldsToView($result['spec_view'])
    //
    addCustomFieldsToView($result['spec_view'],$tproject_id,$tcase_mgr);
  }
  // --------------------------------------------------------------------------------------------

  // 20081004 - franciscom - with array_values() we reindex array to avoid "holes"
  $result['spec_view']= array_values($result['spec_view']);
  return $result;
}


/*
  function: 

  args :
  
  returns: 
  
  rev: 20080919 - franciscom - BUGID 2716

*/
function getFilteredLinkedVersions(&$argsObj,&$tplanMgr,&$tcaseMgr)
{
    define('DONT_FILTER_BY_TCASE_ID',null);
    $doFilterByKeyword=(!is_null($argsObj->keyword_id) && $argsObj->keyword_id > 0) ? true : false;

    // Multiple step algoritm to apply keyword filter on type=AND
    // get_linked_tcversions filters by keyword ALWAYS in OR mode.
    $tplan_tcases = $tplanMgr->get_linked_tcversions($argsObj->tplan_id,DONT_FILTER_BY_TCASE_ID,
                                                     $argsObj->keyword_id);

    // BUGID 2716
    if( !is_null($tplan_tcases) && $doFilterByKeyword && $argsObj->keywordsFilterType == 'AND')
    {
      $filteredSet=$tcaseMgr->filterByKeyword(array_keys($tplan_tcases),
                                              $argsObj->keyword_id,$argsObj->keywordsFilterType);

      $testCaseSet=array_keys($filteredSet);   
      $tplan_tcases = $tplanMgr->get_linked_tcversions($argsObj->tplan_id,$testCaseSet);
    }
    return $tplan_tcases; 
}


/*
  function: 

  args :
  
  returns: 

*/
function keywordFilteredSpecView(&$dbHandler,&$argsObj,$keywordsFilter,&$tplanMgr,&$tcaseMgr)
{
	  $tsuiteMgr = new testsuite($dbHandler); 
	  $tprojectMgr = new testproject($dbHandler); 
	  $tsuite_data = $tsuiteMgr->get_by_id($argsObj->id);
	  	
	  	
	  // @TODO - 20081019 
	  // Really understand differences between:
	  // $argsObj->keyword_id and $keywordsFilter
	  //
	  // 	
	  // BUGID 1041
	  $tplan_linked_tcversions = $tplanMgr->get_linked_tcversions($argsObj->tplan_id,FILTER_BY_TC_OFF,
	                                                              $argsObj->keyword_id,FILTER_BY_EXECUTE_STATUS_OFF,
	                                                              $argsObj->filter_assigned_to);
	  // This does filter on keywords ALWAYS in OR mode.
	  $tplan_linked_tcversions = getFilteredLinkedVersions($argsObj,$tplanMgr,$tcaseMgr);
	  // With this pieces we implement the AND type of keyword filter.
	  $testCaseSet = null;
	if(!is_null($keywordsFilter))
	  { 
		  $keywordsTestCases = $tprojectMgr->get_keywords_tcases($argsObj->tproject_id,
		                                                         $keywordsFilter->items,$keywordsFilter->type);
		  $testCaseSet = array_keys($keywordsTestCases);
    }
    $out = gen_spec_view($dbHandler,'testplan',$argsObj->tplan_id,$argsObj->id,$tsuite_data['name'],
                         $tplan_linked_tcversions,null,
                         $argsObj->keyword_id,$testCaseSet,WRITE_BUTTON_ONLY_IF_LINKED,1,0,1);

    return $out;
}


/*
  function: getTestSpecFromNode 
            using nodeId (that normally is a test suite id) as starting point
            will return subtree that start at nodeId.
            If filters are given, the subtree returned is filtered.

  args :
        $masterContainerId: can be a Test Project Id, or a Test Plan id.
                            is used only if keyword id filter has been specified
                            to get all keyword defined on masterContainer.
                            
        $nodeId: node that will be root of the view we want to build.
                             
  
  returns: map with view (test cases subtree)


*/
function getTestSpecFromNode(&$dbHandler,$masterContainerId,$nodeId,$specViewType,$filters)
{
    $applyFilters=false;
    $testCaseSet=null;
    $tobj_mgr = new testproject($dbHandler);
	$test_spec = $tobj_mgr->get_subtree($nodeId);
    $useFilter=array('keyword_id' => false, 'tcase_id' => false);

  	if(($useFilter['keyword_id']=$filters['keyword_id'] > 0))
	  {
	    $applyFilters=true;
	    switch ($specViewType)
	    {
			    case 'testplan':
			    	$tobj_mgr = new testplan($dbHandler); 
			    	break;  
	    }
	    $tck_map = $tobj_mgr->get_keywords_tcases($masterContainerId,$filters['keyword_id']);
	  }  
	  
	  if( ($useFilter['tcase_id']=!is_null($filters['tcase_id']) ))
	  {
	    $applyFilters=true;
	    $testCaseSet = is_array($filters['tcase_id']) ? $filters['tcase_id'] : array($filters['tcase_id']);
	  }
	  
	  if( $applyFilters )
	  {
	      foreach($test_spec as $key => $node)
	      {
		       if( ($node['node_type_id'] == $tcase_node_type) &&
		       		 ($useFilter['keyword_id'] && !isset($tck_map[$node['id']])) ||
		       		 ($useFilter['tcase_id'] && !in_array($node['id'],$testCaseSet))  
		         )
		       {
		        
		           $test_spec[$key]=null; 
		       }
		    }
	  }

    unset($tobj_mgr);
    return $test_spec;
}


/*
  function: removeEmptyTestSuites

  args: $testSuiteSet: reference to set to analyse and clean.
        $treeMgr: reference to object
        $pruneUnlinkedTcversions: useful when working on test plans
        $nodeTypes: hash key: node type description, value: code
        
  returns: -

*/
function removeEmptyTestSuites(&$testSuiteSet,&$treeMgr,$pruneUnlinkedTcversions,$nodeTypes)
{
	  foreach($testSuiteSet as $key => $value)
	  {
	      // We will remove test suites that meet the empty conditions:
	      // - do not contain other test suites    OR
	      // - do not contain test cases
	      //
	      if( is_null($value) ) 
	      {
	          unset($testSuiteSet[$key]);
	      }
	      else if ($pruneUnlinkedTcversions)
	      {
            // only linked tcversion must be returned, if test suite has no linked tcversion
            // must be removed
	          if( isset($value['linked_testcase_qty']) && $value['linked_testcase_qty']== 0)
	          {
	              unset($testSuiteSet[$key]);
	          } 
            else
            {
                // Only if test suite has children test cases we need to understand
                // if they are linked or not
           	    if( isset($value['testcases']) && count($value['testcases']) > 0 )
           	    {
                   foreach($value['testcases'] as $skey => $svalue)
                   {
                     if( $svalue['linked_version_id'] == 0)
                     {
                       unset($testSuiteSet[$key]['testcases'][$skey]);
                     }
                   }
                } 
            } // is_null($value)
	      }
	      else
	      {
            // list of children test suites if useful on smarty template, in order
            // to draw nested div.
	          $tsuite_id=$value['testsuite']['id'];  
	          $testSuiteSet[$key]['children_testsuites']=
	                       $treeMgr->get_subtree_list($tsuite_id,$nodeTypes['testsuite']);  

	          if( $value['testcase_qty'] == 0 && $testSuiteSet[$key]['children_testsuites']=='' )
	          {
	              unset($testSuiteSet[$key]);
	          }
	      }  
	  }
	  
} // function end	  


function  removeEmptyBranches(&$testSuiteSet,&$tsuiteTestCaseQty)
{
	foreach($testSuiteSet as $key => $elem)
    {
      $tsuite_id=$elem['testsuite']['id'];
      if( !isset($tsuiteTestCaseQty[$tsuite_id]) )
      {
          $tsuiteTestCaseQty[$tsuite_id]=0;
      }    
	  if( $elem['children_testsuites'] != '' )
      {
          $children=explode(',',$elem['children_testsuites']);
          foreach($children as $access_id)
          {
              if( isset($tsuiteTestCaseQty[$access_id]) )
              {
          	    $tsuiteTestCaseQty[$tsuite_id] += $tsuiteTestCaseQty[$access_id];                
              }
          }
      }
      if( $tsuiteTestCaseQty[$tsuite_id]== 0 )
      {
          unset($testSuiteSet[$key]);
      } 
    }
} // function end	  



function addCustomFieldsToView(&$testSuiteSet,$tprojectId,&$tcaseMgr)
{
	  // Important:
	  // testplan_tcversions.id value, that is used to link to manage custom fields that are used
	  // during testplan_design is present on key 'feature_id' (only is linked_version_id != 0)
    foreach($testSuiteSet as $key => $value) 
    {
      if( !is_null($value) )
      {
         if( isset($value['testcases']) && count($value['testcases']) > 0 )
         {
           foreach($value['testcases'] as $skey => $svalue)
           {
          
             $linked_version_id=$svalue['linked_version_id'];
             $testSuiteSet[$key]['testcases'][$skey]['custom_fields']='';
             if( $linked_version_id != 0  )
             {
               $cf_name_suffix = "_" . $svalue['feature_id'];
               $cf_map = $tcaseMgr->html_table_of_custom_field_inputs($linked_version_id,null,'testplan_design',
                                                                      $cf_name_suffix,$svalue['feature_id'],
                                                                      $tprojectId);
               $testSuiteSet[$key]['testcases'][$skey]['custom_fields'] = $cf_map;
             }
           }
         } 
         
      } // is_null($value)
    }
} // function end	  

?>