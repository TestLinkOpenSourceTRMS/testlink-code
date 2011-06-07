<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * testcases commands
 *
 * @filesource	testcaseCommands.class.php
 * @package 	TestLink
 * @author 		Francisco Mancardi - francisco.mancardi@gmail.com
 * @copyright 	2007-2011, TestLink community 
 * @link 		http://www.teamst.org/index.php
 *
 *
 *	@internal revisions
 *  20110607 - Julian - BUGID 3952 - on Create stay here like Mantis does
 *	20110529 - franciscom - TICKET 4322: New Option to block delete of executed test cases
 *	20110413 - franciscom - BUGID 4410 - "Save step" doesn't work just after "Copy Step"
 *							doCopyStep()
 *  20101202 - asimon - BUGID 4067: refresh tree problems
 *  20101001 - asimon - custom fields do not lose entered values on errors
 *	20100927 - franciscom - BUGID 3810: Steps are manual by default, even when added to automatic test case
 *	20100905 - franciscom -	BUGID 3431 - Custom Field values at Test Case VERSION Level
 *	20100903 - franciscom - work on insert step
 *  20100902 - franciscom - BUGID 3736
 *  20100901 - franciscom - changes to *step method to display test case body on read only mode on GUI
 *							initTestCaseBasicInfo() - new method to gather common logic
 *  20100808 - franciscom - initGuiBean() - added steps key to remove error display from event viewer
 *  20100716 - eloff - BUGID 3610 - fixes missing steps_results_layout in $gui
 **/

class testcaseCommands
{
	private $db;
	private $tcaseMgr;
	private $templateCfg;
	private $execution_types;
	private $grants;

	function __construct(&$db)
	{
	    $this->db=$db;
	    $this->tcaseMgr = new testcase($db);
        $this->execution_types = $this->tcaseMgr->get_execution_types();
        $this->grants=new stdClass();
        $this->grants->requirement_mgmt=has_rights($db,"mgt_modify_req"); 

	}

	function setTemplateCfg($cfg)
	{
	    $this->templateCfg=$cfg;
	}

	/**
	 * 
	 *
	 */
	function initGuiBean(&$argsObj)
	{
	    $obj = new stdClass();
	    $obj->action = '';
		$obj->attachments = null;
    	$obj->cleanUpWebEditor = false;
		$obj->containerID = '';
		$obj->direct_link = null;
	    $obj->execution_types = $this->execution_types;

		$obj->grants = $this->grants;
   		$obj->has_been_executed = false;
    	$obj->initWebEditorFromTemplate = false;

		$obj->main_descr = '';
		$obj->name = '';
    	$obj->refreshTree=0;
	    $obj->sqlResult = '';
   		$obj->step_id = -1;
   		$obj->step_set = '';
   		$obj->steps = '';
    	$obj->tableColspan = 5;
        $obj->tcase_id = property_exists($argsObj,'tcase_id') ? $argsObj->tcase_id : -1;

		// BUGID 3493
        $p2check = 'goback_url';
        $obj->$p2check = '';
        if( property_exists($argsObj,$p2check) )
        {
        	$obj->$p2check = !is_null($argsObj->$p2check) ? $argsObj->$p2check : ''; 
        }
        
        $p2check = 'show_mode';
        if( property_exists($argsObj,$p2check) )
        {
        	$obj->$p2check = !is_null($argsObj->$p2check) ? $argsObj->$p2check : 'show'; 
        }

		// need to check where is used
        $obj->loadOnCancelURL = "archiveData.php?edit=testcase&show_mode={$obj->show_mode}&id=%s&version_id=%s";
		return $obj;
	}
	 
	/**
	 * initialize common test case information, useful when working on steps
 	 *
 	 */

	function initTestCaseBasicInfo(&$argsObj,&$guiObj)
	{
		$tcaseInfo = $this->tcaseMgr->get_by_id($argsObj->tcase_id,$argsObj->tcversion_id,
												null,array('output' => 'full_without_steps'));
		$external = $this->tcaseMgr->getExternalID($argsObj->tcase_id,$argsObj->testproject_id);
		$tcaseInfo[0]['tc_external_id'] = $external[0];
		$guiObj->testcase = $tcaseInfo[0];
		$guiObj->authorObj = tlUser::getByID($this->db,$guiObj->testcase['author_id'],'id');
		$guiObj->updaterObj = null;
		if( !is_null($guiObj->testcase['updater_id']) )
		{
			$guiObj->updaterObj = tlUser::getByID($this->db,$guiObj->testcase['updater_id'],'id');
		}	
	}

	 
	 
	 
	 
	/**
	 * 
	 *
	 */
	function create(&$argsObj,&$otCfg,$oWebEditorKeys)
	{
    	$guiObj = $this->initGuiBean($argsObj);
    	$guiObj->initWebEditorFromTemplate = true;
    	
		$importance_default = config_get('testcase_importance_default');
		
    	$tc_default=array('id' => 0, 'name' => '', 'importance' => $importance_default,
  	                      'execution_type' => TESTCASE_EXECUTION_TYPE_MANUAL);

        $guiObj->containerID = $argsObj->container_id;
		if($argsObj->container_id > 0)
		{
			$pnode_info = $this->tcaseMgr->tree_manager->get_node_hierarchy_info($argsObj->container_id);
			$node_descr = array_flip($this->tcaseMgr->tree_manager->get_available_node_types());
			$guiObj->parent_info['name'] = $pnode_info['name'];
			$guiObj->parent_info['description'] = lang_get($node_descr[$pnode_info['node_type_id']]);
		}
        $sep_1 = config_get('gui_title_separator_1');
        $sep_2 = config_get('gui_title_separator_2'); 
        $guiObj->main_descr = $guiObj->parent_info['description'] . $sep_1 . $guiObj->parent_info['name'] . 
                              $sep_2 . lang_get('title_new_tc');
    	
    	$otCfg->to->map = array();
    	keywords_opt_transf_cfg($otCfg,'');

		$cfPlaces = $this->tcaseMgr->buildCFLocationMap();
		foreach($cfPlaces as $locationKey => $locationFilter)
		{ 
			// BUGID 3431 - NO CHANGE NEEDED
			// 20101001 - asimon - custom fields do not lose entered values on errors
			$guiObj->cf[$locationKey] = 
				$this->tcaseMgr->html_table_of_custom_field_inputs(null,null,'design','',null,null,
				                                                   $argsObj->testproject_id,$locationFilter, $_REQUEST);
		}	
    	$guiObj->tc=$tc_default;
    	$guiObj->opt_cfg=$otCfg;
		$templateCfg = templateConfiguration('tcNew');
		$guiObj->template=$templateCfg->default_template;
    	return $guiObj;
	}

	/**
	 * 
	 *
	 */
	function doCreate(&$argsObj,&$otCfg,$oWebEditorKeys,$request)
	{
        $guiObj = $this->create($argsObj,$otCfg,$oWebEditorKeys);
		  
		// compute order
		$new_order = config_get('treemenu_default_testcase_order');
		$nt2exclude=array('testplan' => 'exclude_me','requirement_spec'=> 'exclude_me','requirement'=> 'exclude_me');
		$siblings = $this->tcaseMgr->tree_manager->get_children($argsObj->container_id,$nt2exclude);
	
		if( !is_null($siblings) )
		{
			$dummy = end($siblings);
			$new_order = $dummy['node_order']+1;
		}
		$options = array('check_duplicate_name' => config_get('check_names_for_duplicates'),
		                 'action_on_duplicate_name' => 'block');
		$tcase = $this->tcaseMgr->create($argsObj->container_id,$argsObj->name,$argsObj->summary,$argsObj->preconditions,
		                            	 $argsObj->tcaseSteps,$argsObj->user_id,$argsObj->assigned_keywords_list,
		                            	 $new_order,testcase::AUTOMATIC_ID,
		                            	 $argsObj->exec_type,$argsObj->importance,$options);

		if($tcase['status_ok'])
		{
			// BUGID 3952 - on Create stay here like Mantis does
			if($argsObj->stay_here)
			{	 
				$cf_map = $this->tcaseMgr->cfield_mgr->get_linked_cfields_at_design($argsObj->testproject_id,ENABLED,
			     	                                                                NO_FILTER_SHOW_ON_EXEC,'testcase');
			
            	// BUGID 3162 - generated by not passing 3 argument
            	// 20100905 - franciscom - BUGID 3431 
				// $this->tcaseMgr->cfield_mgr->design_values_to_db($_REQUEST,$tcase['id'],$cf_map);
				$this->tcaseMgr->cfield_mgr->design_values_to_db($_REQUEST,$tcase['tcversion_id'],$cf_map);

		   		$guiObj->user_feedback = sprintf(lang_get('tc_created'),$argsObj->name);
		   		$guiObj->sqlResult = 'ok';
		   		$guiObj->initWebEditorFromTemplate = true;
		   		$guiObj->cleanUpWebEditor = true;
				$opt_list = '';
			}
			else
			{
				// we will not return to caller
				$argsObj->tcase_id = $tcase['id'];
				$argsObj->tcversion_id = $tcase['tcversion_id'];
				$this->show($argsObj,$request, array('status_ok' => 1));
			}
		}
		elseif(isset($tcase['msg']))
		{
			$guiObj->user_feedback = lang_get('error_tc_add');
			$guiObj->user_feedback .= '' . $tcase['msg'];
	    	$guiObj->sqlResult = 'ko';
    		$opt_list = $argsObj->assigned_keywords_list;
    		$guiObj->initWebEditorFromTemplate = false;
		}
        keywords_opt_transf_cfg($otCfg, $opt_list);
    	$guiObj->opt_cfg=$otCfg;
		$templateCfg = templateConfiguration('tcNew');
		$guiObj->template=$templateCfg->default_template;
		return $guiObj;    
    }




	/*
	  function: edit (Test Case)
	
	  args:
	  
	  returns: 
	
	*/
	function edit(&$argsObj,&$otCfg,$oWebEditorKeys)
	{
    	$guiObj = $this->initGuiBean($argsObj);
    	$otCfg->to->map = $this->tcaseMgr->get_keywords_map($argsObj->tcase_id," ORDER BY keyword ASC ");
    	keywords_opt_transf_cfg($otCfg, $argsObj->assigned_keywords_list);
  		$tc_data = $this->tcaseMgr->get_by_id($argsObj->tcase_id,$argsObj->tcversion_id);
  		foreach($oWebEditorKeys as $key)
   		{
   			$guiObj->$key = isset($tc_data[0][$key]) ?  $tc_data[0][$key] : '';
   			$argsObj->$key = $guiObj->$key;
  		}
 		
  		$cf_smarty = null;
		$cfPlaces = $this->tcaseMgr->buildCFLocationMap();
		foreach($cfPlaces as $locationKey => $locationFilter)
		{ 
			$cf_smarty[$locationKey] = 
				$this->tcaseMgr->html_table_of_custom_field_inputs($argsObj->tcase_id,null,'design','',
				                                                   $argsObj->tcversion_id,null,null,$locationFilter);
		}	
		
   		$templateCfg = templateConfiguration('tcEdit');
		$guiObj->cf = $cf_smarty;
    	$guiObj->tc=$tc_data[0];
    	$guiObj->opt_cfg=$otCfg;
		$guiObj->template=$templateCfg->default_template;
    	return $guiObj;
  }


  /*
    function: doUpdate

    args:
    
    returns: 

  */
    function doUpdate(&$argsObj,$request)
	{
        $ret = $this->tcaseMgr->update($argsObj->tcase_id, $argsObj->tcversion_id, $argsObj->name, 
		                               $argsObj->summary, $argsObj->preconditions, $argsObj->tcaseSteps, 
		                               $argsObj->user_id, $argsObj->assigned_keywords_list,
		                               testcase::DEFAULT_ORDER, $argsObj->exec_type, $argsObj->importance);

		$this->show($argsObj,$request,$ret);
 
        return $guiObj;
  }  


  /**
   * doAdd2testplan
   *
   */
	function doAdd2testplan(&$argsObj,$request)
	{
      	$smartyObj = new TLSmarty();
      	$smartyObj->assign('attachments',null);
    	$guiObj = $this->initGuiBean($argsObj);

      	$viewer_args=array();
      	$tplan_mgr = new testplan($this->db);
      	
   	  	$guiObj->refreshTree = $argsObj->refreshTree? 1 : 0;
      	$item2link = null;
      	// $request['add2tplanid']
      	// main key: testplan id
      	// sec key : platform_id
      	if( isset($request['add2tplanid']) )
      	{
      	    foreach($request['add2tplanid'] as $tplan_id => $platformSet)
      	    {
      	    	foreach($platformSet as $platform_id => $dummy)
      	    	{
      	    		$item2link = null;
                    $item2link['tcversion'][$argsObj->tcase_id] = $argsObj->tcversion_id;
                    $item2link['platform'][$platform_id] = $platform_id;
                    $item2link['items'][$argsObj->tcase_id][$platform_id] = $argsObj->tcversion_id;
      	        	$tplan_mgr->link_tcversions($tplan_id,$item2link,$argsObj->user_id);  
      	        }
      	    }
      	    $this->tcaseMgr->show($smartyObj,$guiObj, $this->templateCfg->template_dir,
	  	                          $argsObj->tcase_id,$argsObj->tcversion_id,$viewer_args);
      	}
      	return $guiObj;
  }

  /**
   * add2testplan - is really needed???? 20090308 - franciscom - TO DO
   *
   */
	function add2testplan(&$argsObj,$request)
	{
      // $smartyObj = new TLSmarty();
      // $guiObj=new stdClass();
      // $viewer_args=array();
      // $tplan_mgr = new testplan($this->db);
      // 
   	  // $guiObj->refresh_tree=$argsObj->do_refresh?"yes":"no";
      // 
      // $item2link[$argsObj->tcase_id]=$argsObj->tcversion_id;
      // foreach($request['add2tplanid'] as $tplan_id => $value)
      // {
      //     $tplan_mgr->link_tcversions($tplan_id,$item2link);  
      // }
	    // $this->tcaseMgr->show($smartyObj,$this->templateCfg->template_dir,
	    //                       $argsObj->tcase_id,$argsObj->tcversion_id,$viewer_args);
      // 
      // return $guiObj;
  }


  /**
   * 
   *
   * @internal revisions
   * 20110529 - franciscom - TICKET 4322	
   */
	function delete(&$argsObj,$request)
	{
    	$guiObj = $this->initGuiBean($argsObj);
 		$guiObj->delete_message = '';
		$cfg = config_get('testcase_cfg');

		// TICKET 4322
 		$guiObj->exec_status_quo = $this->tcaseMgr->get_exec_status($argsObj->tcase_id,null, 
 																	array('addExecIndicator' => true));

		$guiObj->delete_enabled = 1;
		if( $guiObj->exec_status_quo['executed'] && !$cfg->can_delete_executed )
		{
			$guiObj->delete_message = lang_get('system_blocks_delete_executed_tc_when');
			$guiObj->delete_enabled = 0;
 		}
 		// need to remove 'executed' key, in order to do not have side effects on Viewer logic (template).
 		unset($guiObj->exec_status_quo['executed']);


 		$guiObj->delete_mode = 'single';
 		$guiObj->display_platform = false;
		            
		// Need to understand if platform column has to be displayed on GUI
		if( !is_null($guiObj->exec_status_quo) )             
		{
			// key level 1 : Test Case Version ID
			// key level 2 : Test Plan  ID
			// key level 3 : Platform ID
			
			$versionSet = array_keys($guiObj->exec_status_quo);
			$stop = false;
			foreach($versionSet as $version_id)
			{
				$tplanSet = array_keys($guiObj->exec_status_quo[$version_id]);
				foreach($tplanSet as $tplan_id)
				{
					if( ($guiObj->display_platform = !isset($guiObj->exec_status_quo[$version_id][$tplan_id][0])) )
					{
						$stop = true;
						break;
					}
				}
				if($stop)
				{
					break;
				}
			}
		}
		                  
		$tcinfo = $this->tcaseMgr->get_by_id($argsObj->tcase_id);
		list($prefix,$root) = $this->tcaseMgr->getPrefix($argsObj->tcase_id,$argsObj->testproject_id);
        $prefix .= $cfg->glue_character;
        $external_id = $prefix . $tcinfo[0]['tc_external_id'];
        
		$guiObj->title = lang_get('title_del_tc');
		$guiObj->testcase_name =  $tcinfo[0]['name'];
		$guiObj->testcase_id = $argsObj->tcase_id;
		$guiObj->tcversion_id = testcase::ALL_VERSIONS;
		$guiObj->refreshTree = 1;
 		$guiObj->main_descr = lang_get('title_del_tc') . TITLE_SEP . $external_id . TITLE_SEP . $tcinfo[0]['name'];  
    
    	$templateCfg = templateConfiguration('tcDelete');
  		$guiObj->template=$templateCfg->default_template;
		return $guiObj;
	}

  /**
   * 
   *
   */
	function doDelete(&$argsObj,$request)
	{
		$cfg = config_get('testcase_cfg');

    	$guiObj = $this->initGuiBean($argsObj);
 		$guiObj->user_feedback = '';
		$guiObj->delete_message = '';
		$guiObj->action = 'deleted';
		$guiObj->sqlResult = 'ok';
 		$guiObj->delete_mode = 'single';

		$tcinfo = $this->tcaseMgr->get_by_id($argsObj->tcase_id,$argsObj->tcversion_id);
		list($prefix,$root) = $this->tcaseMgr->getPrefix($argsObj->tcase_id,$argsObj->testproject_id);
        $prefix .= $cfg->glue_character;
        $external_id = $prefix . $tcinfo[0]['tc_external_id'];
		if (!$this->tcaseMgr->delete($argsObj->tcase_id,$argsObj->tcversion_id))
		{
			$guiObj->action = '';
			$guiObj->sqlResult = $this->tcaseMgr->db->error_msg();
		}
		else
		{
			$guiObj->user_feedback = sprintf(lang_get('tc_deleted'), ":" . $external_id . TITLE_SEP . $tcinfo[0]['name']);
		}
    	
		$guiObj->main_descr = lang_get('title_del_tc') . ":" . $external_id . TITLE_SEP . htmlspecialchars($tcinfo[0]['name']);
  
  		// 20080706 - refresh must be forced to avoid a wrong tree situation.
  		// if tree is not refreshed and user click on deleted test case he/she
  		// will get a SQL error
  		// $refresh_tree = $cfg->spec->automatic_tree_refresh ? "yes" : "no";
  		$guiObj->refreshTree = 1;
 
  		// When deleting JUST one version, there is no need to refresh tree
		if($argsObj->tcversion_id != testcase::ALL_VERSIONS)
		{
			  $guiObj->main_descr .= " " . lang_get('version') . " " . $tcinfo[0]['version'];
			  $guiObj->refreshTree = 0;
		  	  $guiObj->user_feedback = sprintf(lang_get('tc_version_deleted'),$tcinfo[0]['name'],$tcinfo[0]['version']);
		}

		$guiObj->testcase_name = $tcinfo[0]['name'];
		$guiObj->testcase_id = $argsObj->tcase_id;
    
    	$templateCfg = templateConfiguration('tcDelete');
  		$guiObj->template=$templateCfg->default_template;
		return $guiObj;
	}


	/**
   	 * createStep
     *
     * @internal revisions
     * 20100927 - franciscom - BUGID 3810
     */
	function createStep(&$argsObj,$request)
	{
	    $guiObj = $this->initGuiBean($argsObj);

		$this->initTestCaseBasicInfo($argsObj,$guiObj);
		$guiObj->main_descr = sprintf(lang_get('create_step'), $guiObj->testcase['tc_external_id'] . ':' . 
									  $guiObj->testcase['name'], $guiObj->testcase['version']); 
        
		$max_step = $this->tcaseMgr->get_latest_step_number($argsObj->tcversion_id); 
		$max_step++;;

		$guiObj->step_number = $max_step;
		$guiObj->step_exec_type = $guiObj->testcase['execution_type']; // BUGID 3810
		$guiObj->tcversion_id = $argsObj->tcversion_id;

		$guiObj->step_set = $this->tcaseMgr->get_step_numbers($argsObj->tcversion_id);
		$guiObj->step_set = is_null($guiObj->step_set) ? '' : implode(",",array_keys($guiObj->step_set));
        $guiObj->loadOnCancelURL = sprintf($guiObj->loadOnCancelURL,$argsObj->tcase_id,$argsObj->tcversion_id);
        
   		// Get all existent steps
		$guiObj->tcaseSteps = $this->tcaseMgr->get_steps($argsObj->tcversion_id);
        
    	$templateCfg = templateConfiguration('tcStepEdit');
  		$guiObj->template=$templateCfg->default_template;
		$guiObj->action = __FUNCTION__;
		
		return $guiObj;
	}

	/**
   	 * doCreateStep
     *
     * 20100927 - franciscom - BUGID 3810
     */
	function doCreateStep(&$argsObj,$request)
	{
	    $guiObj = $this->initGuiBean($argsObj);
		$guiObj->user_feedback = '';
		$guiObj->step_exec_type = $argsObj->exec_type;
        $guiObj->tcversion_id = $argsObj->tcversion_id;

		$this->initTestCaseBasicInfo($argsObj,$guiObj);
		$guiObj->main_descr = sprintf(lang_get('create_step'), $guiObj->testcase['tc_external_id'] . ':' . 
									  $guiObj->testcase['name'], $guiObj->testcase['version']); 


		$new_step = $this->tcaseMgr->get_latest_step_number($argsObj->tcversion_id); 
		$new_step++;
        $op = $this->tcaseMgr->create_step($argsObj->tcversion_id,$new_step,
                                           $argsObj->steps,$argsObj->expected_results,$argsObj->exec_type);	
                              
		if( $op['status_ok'] )
		{
			$guiObj->user_feedback = sprintf(lang_get('step_number_x_created'),$argsObj->step_number);
		    $guiObj->step_exec_type = $guiObj->testcase['execution_type'];  // BUGID 3810
		    $guiObj->cleanUpWebEditor = true;
			$this->tcaseMgr->update_last_modified($argsObj->tcversion_id,$argsObj->user_id);
			$this->initTestCaseBasicInfo($argsObj,$guiObj);
		}	

		$guiObj->action = __FUNCTION__;

   		// Get all existent steps
		$guiObj->tcaseSteps = $this->tcaseMgr->get_steps($argsObj->tcversion_id);

		$max_step = $this->tcaseMgr->get_latest_step_number($argsObj->tcversion_id); 
		$max_step++;;
		$guiObj->step_number = $max_step;

		$guiObj->step_set = $this->tcaseMgr->get_step_numbers($argsObj->tcversion_id);
		$guiObj->step_set = is_null($guiObj->step_set) ? '' : implode(",",array_keys($guiObj->step_set));
        $guiObj->loadOnCancelURL = sprintf($guiObj->loadOnCancelURL,$argsObj->tcase_id,$argsObj->tcversion_id);

    	$templateCfg = templateConfiguration('tcStepEdit');
  		$guiObj->template=$templateCfg->default_template;
		return $guiObj;
	}

	/**
   	 * editStep
     *
     */
	function editStep(&$argsObj,$request)
	{
	    $guiObj = $this->initGuiBean($argsObj);
		$guiObj->user_feedback = '';
		$this->initTestCaseBasicInfo($argsObj,$guiObj);

		$stepInfo = $this->tcaseMgr->get_step_by_id($argsObj->step_id);
        
        $oWebEditorKeys = array('steps' => 'actions', 'expected_results' => 'expected_results');
  		foreach($oWebEditorKeys as $key => $field)
   		{
  		  	$argsObj->$key = $stepInfo[$field];
  		  	$guiObj->$key = $stepInfo[$field];
  		}

		$guiObj->main_descr = sprintf(lang_get('edit_step_number_x'),$stepInfo['step_number'],
									  $guiObj->testcase['tc_external_id'] . ':' . 
									  $guiObj->testcase['name'], $guiObj->testcase['version']); 

		$guiObj->tcase_id = $argsObj->tcase_id;
		$guiObj->tcversion_id = $argsObj->tcversion_id;
		$guiObj->step_id = $argsObj->step_id;
		$guiObj->step_exec_type = $stepInfo['execution_type'];
		$guiObj->step_number = $stepInfo['step_number']; // BUGID 3326: Editing a test step: execution type always "Manual"

		// Get all existent steps
		$guiObj->tcaseSteps = $this->tcaseMgr->get_steps($argsObj->tcversion_id);

		$guiObj->step_set = $this->tcaseMgr->get_step_numbers($argsObj->tcversion_id);
		$guiObj->step_set = is_null($guiObj->step_set) ? '' : implode(",",array_keys($guiObj->step_set));

		$templateCfg = templateConfiguration('tcStepEdit');
		$guiObj->template=$templateCfg->default_template;
        $guiObj->loadOnCancelURL = sprintf($guiObj->loadOnCancelURL,$argsObj->tcase_id,$argsObj->tcversion_id);
        
		return $guiObj;
	}

	/**
   	 * doUpdateStep
     *
     */
	function doUpdateStep(&$argsObj,$request)
	{
	    $guiObj = $this->initGuiBean($argsObj);
		$guiObj->user_feedback = '';
		
		$this->initTestCaseBasicInfo($argsObj,$guiObj);

		$stepInfo = $this->tcaseMgr->get_step_by_id($argsObj->step_id);
		$guiObj->main_descr = sprintf(lang_get('edit_step_number_x'),$stepInfo['step_number'],
									  $guiObj->testcase['tc_external_id'] . ':' . 
									  $guiObj->testcase['name'], $guiObj->testcase['version']); 

        $op = $this->tcaseMgr->update_step($argsObj->step_id,$argsObj->step_number,$argsObj->steps,
                                           $argsObj->expected_results,$argsObj->exec_type);		

		$this->tcaseMgr->update_last_modified($argsObj->tcversion_id,$argsObj->user_id);
		$this->initTestCaseBasicInfo($argsObj,$guiObj);

		$guiObj->tcversion_id = $argsObj->tcversion_id;
		$guiObj->step_id = $argsObj->step_id;
		$guiObj->step_number = $stepInfo['step_number'];
		$guiObj->step_exec_type = $argsObj->exec_type;
		
		// 20100403
		// Want to remain on same screen till user choose to cancel => go away
		// BUGID 3441
		$guiObj = $this->editStep($argsObj,$request);  
		return $guiObj;
	}


	/**
   	 * doReorderSteps
     *
     */
	function doReorderSteps(&$argsObj,$request)
	{
	    $guiObj = $this->initGuiBean($argsObj);
		$guiObj->main_descr = lang_get('test_case');
		$this->tcaseMgr->set_step_number($argsObj->step_set);
		$this->tcaseMgr->update_last_modified($argsObj->tcversion_id,$argsObj->user_id);
		$this->initTestCaseBasicInfo($argsObj,$guiObj);
		$guiObj->template="archiveData.php?version_id={$argsObj->tcversion_id}&" . 
						  "edit=testcase&id={$argsObj->tcase_id}&show_mode={$guiObj->show_mode}";
		return $guiObj;
	}


	/**
   	 * doDeleteStep
     *
     */
	function doDeleteStep(&$argsObj,$request)
	{
		$guiObj = $this->initGuiBean($argsObj); // BUGID 3493

		$viewer_args=array();
		$guiObj->refreshTree = 0;
		$step_node = $this->tcaseMgr->tree_manager->get_node_hierarchy_info($argsObj->step_id);
		$tcversion_node = $this->tcaseMgr->tree_manager->get_node_hierarchy_info($step_node['parent_id']);
		$tcversion_id = $step_node['parent_id'];
		$tcase_id = $tcversion_node['parent_id'];
		
		$guiObj->template="archiveData.php?version_id={$tcversion_id}&" . 
						  "edit=testcase&id={$tcase_id}&show_mode={$guiObj->show_mode}";

		$guiObj->user_feedback = '';
        $op = $this->tcaseMgr->delete_step_by_id($argsObj->step_id);
   		$this->tcaseMgr->update_last_modified($argsObj->tcversion_id,$argsObj->user_id);
		$this->initTestCaseBasicInfo($argsObj,$guiObj);

		return $guiObj;
	}

	/**
   	 * doCopyStep
     *
     */
	function doCopyStep(&$argsObj,$request)
	{
	    $guiObj = $this->initGuiBean($argsObj);
		$guiObj->user_feedback = '';
		$guiObj->step_exec_type = $argsObj->exec_type;
        $guiObj->tcversion_id = $argsObj->tcversion_id;
		
		// need to document difference bewteen these two similar concepts
		$guiObj->action = __FUNCTION__;
		$guiObj->operation = 'doUpdateStep';
		
		$this->initTestCaseBasicInfo($argsObj,$guiObj);
		$guiObj->main_descr = sprintf(lang_get('edit_step_number_x'),$argsObj->step_number,
									  $guiObj->testcase['tc_external_id'] . ':' . 
									  $guiObj->testcase['name'], $guiObj->testcase['version']); 

		$new_step = $this->tcaseMgr->get_latest_step_number($argsObj->tcversion_id); 
		$new_step++;

	    $source_info = $this->tcaseMgr->get_steps($argsObj->tcversion_id,$argsObj->step_number);
	    $source_info = current($source_info);
        $op = $this->tcaseMgr->create_step($argsObj->tcversion_id,$new_step,$source_info['actions'],
                                 		   $source_info['expected_results'],$source_info['execution_type']);			

		if( $op['status_ok'] )
		{
			$guiObj->user_feedback = sprintf(lang_get('step_number_x_created_as_copy'),$new_step,$argsObj->step_number);
		    $guiObj->step_exec_type = TESTCASE_EXECUTION_TYPE_MANUAL;

   			$this->tcaseMgr->update_last_modified($argsObj->tcversion_id,$argsObj->user_id);
			$this->initTestCaseBasicInfo($argsObj,$guiObj);
		}	


   		// Get all existent steps
		$guiObj->tcaseSteps = $this->tcaseMgr->get_steps($argsObj->tcversion_id);

		// After copy I would like to return to target step in edit mode, 
		// is enough to set $guiObj->step_number to target test step --> FOUND THIS is WRONG
		// generated BUGID 4410
		$guiObj->step_number = $argsObj->step_number;
		$guiObj->step_id = $argsObj->step_id;

		$guiObj->step_set = $this->tcaseMgr->get_step_numbers($argsObj->tcversion_id);
		$guiObj->step_set = is_null($guiObj->step_set) ? '' : implode(",",array_keys($guiObj->step_set));
        $guiObj->loadOnCancelURL = sprintf($guiObj->loadOnCancelURL,$argsObj->tcase_id,$argsObj->tcversion_id);

    	$templateCfg = templateConfiguration('tcStepEdit');
  		$guiObj->template=$templateCfg->default_template;
		return $guiObj;
	}



	/**
   	 * doInsertStep
     *
     */
	function doInsertStep(&$argsObj,$request)
	{
	    $guiObj = $this->initGuiBean($argsObj);
		$guiObj->user_feedback = '';
		$guiObj->step_exec_type = $argsObj->exec_type;
        $guiObj->tcversion_id = $argsObj->tcversion_id;

		$this->initTestCaseBasicInfo($argsObj,$guiObj);

   		// Get all existent steps - info needed to do renumbering
   		$stepNumberSet = array();
		$existentSteps = $this->tcaseMgr->get_steps($argsObj->tcversion_id);
		$stepsQty = count($existentSteps);
		for($idx=0; $idx < $stepsQty; $idx++)
		{
			$stepNumberSet[$idx] = $existentSteps[$idx]['step_number'];
			$stepIDSet[$idx] = $existentSteps[$idx]['id'];
		}
		
		$stepInfo = $this->tcaseMgr->get_step_by_id($argsObj->step_id);
		$newStepNumber = $stepInfo['step_number'] + 1;
	    $op = $this->tcaseMgr->create_step($argsObj->tcversion_id,$newStepNumber,'','');
 		$guiObj->main_descr = sprintf(lang_get('edit_step_number_x'),$newStepNumber,
									  $guiObj->testcase['tc_external_id'] . ':' . 
									  $guiObj->testcase['name'], $guiObj->testcase['version']); 

		if( $op['status_ok'] )
		{
			$guiObj->user_feedback = sprintf(lang_get('step_number_x_created'),$newStepNumber);
		    $guiObj->step_exec_type = TESTCASE_EXECUTION_TYPE_MANUAL;
		    $guiObj->cleanUpWebEditor = true;

			// renumber steps only if new step hits an existent step number
			$hitPos = array_search($newStepNumber, $stepNumberSet);
			if( $hitPos !== FALSE )
			{
				// Process starts from this position
				$just_renumbered = array('pos' => $hitPos, 'value' => $newStepNumber+1); 
				$renumbered[$stepIDSet[$hitPos]] = $just_renumbered['value']; 
				
				// now check if new renumbered collides with next
				// if not nothing needs to be done
				// if yes need to loop
				$startFrom = $hitPos +1;
				$endOn = count($stepNumberSet);
				for($jdx = $startFrom; $jdx < $endOn; $jdx++)
				{
					if( $stepNumberSet[$jdx] == $just_renumbered['value'] )
					{
						$just_renumbered['value']++;
						$renumbered[$stepIDSet[$jdx]] = $just_renumbered['value']; 
					}
				}
				$this->tcaseMgr->set_step_number($renumbered);
			}
			$this->tcaseMgr->update_last_modified($argsObj->tcversion_id,$argsObj->user_id);
			$this->initTestCaseBasicInfo($argsObj,$guiObj);
		}	

   		// Get all existent steps - updated
		$guiObj->tcaseSteps = $this->tcaseMgr->get_steps($argsObj->tcversion_id);
		$guiObj->action = __FUNCTION__;
		$guiObj->step_number = $newStepNumber;
		$guiObj->step_id = $op['id'];

		$guiObj->step_set = $this->tcaseMgr->get_step_numbers($argsObj->tcversion_id);
		$guiObj->step_set = is_null($guiObj->step_set) ? '' : implode(",",array_keys($guiObj->step_set));
        $guiObj->loadOnCancelURL = sprintf($guiObj->loadOnCancelURL,$argsObj->tcase_id,$argsObj->tcversion_id);
    	$templateCfg = templateConfiguration('tcStepEdit');
  		$guiObj->template=$templateCfg->default_template;
		return $guiObj;
	}
	
	
	/**
	 * 
	 *
	 */
	function show(&$argsObj,$request,$userFeedback)
	{
        $smartyObj = new TLSmarty();
        $viewer_args=array();
    	$guiObj = $this->initGuiBean($argsObj);
   	    $guiObj->refreshTree = ($argsObj->refreshTree && $userFeedback['status_ok']) ? 1 : 0;
   	    $guiObj->has_been_executed = $argsObj->has_been_executed;
		$guiObj->steps_results_layout = config_get('spec_cfg')->steps_results_layout;

		
        if($userFeedback['status_ok'])
		{
		    $guiObj->user_feedback = '';
  			$ENABLED = 1;
	  		$NO_FILTERS = null;
		  	$cf_map = $this->tcaseMgr->cfield_mgr->get_linked_cfields_at_design($argsObj->testproject_id,
		                                                                        $ENABLED,$NO_FILTERS,'testcase') ;
			$this->tcaseMgr->cfield_mgr->design_values_to_db($request,$argsObj->tcversion_id,$cf_map);
			
            $guiObj->attachments[$argsObj->tcase_id] = getAttachmentInfosFrom($this->tcaseMgr,$argsObj->tcase_id);
		}
		else
		{
		    $guiObj->user_feedback = $userFeedback['msg'];
		}

	    $viewer_args['refreshTree'] = $guiObj->refreshTree;
 	    $viewer_args['user_feedback'] = $guiObj->user_feedback;
	    $this->tcaseMgr->show($smartyObj,$guiObj, $this->templateCfg->template_dir, $argsObj->tcase_id,
	                          $argsObj->tcversion_id,$viewer_args,null,$argsObj->show_mode);
		exit();	
	}

	
} // end class  
?>