<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * testcases commands
 *
 * @filesource	testcaseCommands.class.php
 * @package 	  TestLink
 * @author 		  Francisco Mancardi - francisco.mancardi@gmail.com
 * @copyright 	2007-2012, TestLink community 
 * @link 		    http://www.teamst.org/index.php
 *
 * @internal revisions
 **/

class testcaseCommands
{
	private $db;
	private $tcaseMgr;
	private $templateCfg;
	private $execution_types;
	private $grants;
	private $cfg;

	function __construct(&$db,&$userObj,$tproject_id)
	{
	  $this->db=$db;
	  $this->tcaseMgr = new testcase($db);
    $this->execution_types = $this->tcaseMgr->get_execution_types();
    $this->cfg = (object) array('testcase' => config_get('testcase_cfg'),
                                'spec' => config_get('spec_cfg')); 

    $this->grants = new stdClass();
    $g2c = array('mgt_modify_tc','mgt_view_req','testplan_planning',
                 'testproject_delete_executed_testcases','testproject_edit_executed_testcases');
                  
    foreach($g2c as $grant)
    {
		  $this->grants->$grant = $userObj->hasRight($db,$grant,$tproject_id);
    
    }
    $this->grants->requirement_mgmt = $userObj->hasRight($db,"mgt_modify_req",$tproject_id) ||
        								              $userObj->hasRight($db,"req_tcase_link_management",$tproject_id);

    $this->sep_1 = config_get('gui_title_separator_1');
    $this->sep_2 = config_get('gui_title_separator_2'); 
	}

	function setTemplateCfg($cfg)
	{
	    $this->templateCfg = $cfg;
	}

	function getTemplateCfg()
	{
	    return $this->templateCfg;
	}

	/**
	 * 
	 *
	 */
	function initGuiBean(&$argsObj,$mandatory = null)
	{
    $obj = new stdClass();
    $prop2scan = array('tproject_id' => 'Test project id can not be <= 0',
                       'tsuiteID' => 'Test suite id can not be <= 0');

    if( !is_null($mandatory) )
    {
      foreach($mandatory as $key)
      {
        $p2check[$key] = $prop2scan[$key];        
      }
    }    
    else
    {
      $p2check = &$prop2scan;
    }
    
    foreach($p2check as $prop => $msg)
    {
      if( ($obj->$prop = intval($argsObj->$prop)) <= 0)
      {
        throw new Exception(__METHOD__ . ':' . $msg);
      }
    }                      
    
		$tprojectMgr = new testproject($this->db);
		$dummy = $tprojectMgr->get_by_id($obj->tproject_id);
		$obj->testPriorityEnabled = $dummy['opt']->testPriorityEnabled;
		$obj->automationEnabled = $dummy['opt']->automationEnabled;

    $this->keywordSet = array('testproject' => $tprojectMgr->get_keywords_map($argsObj->tproject_id),
                              'testcase' => null); 

    $obj->template_dir = $this->templateCfg->template_dir;
	  $obj->action = '';
		$obj->attachments = null;
    $obj->cleanUpWebEditor = false;
		$obj->direct_link = null;
	  $obj->execution_types = $this->execution_types;

		$obj->grants = $this->grants;
   	$obj->has_been_executed = false;
    $obj->initWebEditorFromTemplate = false;
    $obj->viewerArgs = null;
    $obj->path_info = null;

		$obj->main_descr = '';
		$obj->name = '';
	  $obj->sqlResult = '';
   	$obj->step_id = -1;
   	$obj->step_set = '';
   	$obj->steps = '';
    $obj->tableColspan = 5;
    	
    $obj->tcase_id = property_exists($argsObj,'tcase_id') ? intval($argsObj->tcase_id) : -1;

    $p2check = array('goback_url' => '', 'show_mode' => 'show', 'refreshTree' => !tlTreeMenu::REFRESH_GUI);
    foreach($p2check as $prop => $value)
    {
      if( property_exists($argsObj,$prop) && !is_null($argsObj->$prop) )
      {
    	  $obj->$prop = $argsObj->$prop;
      }
      else
      {
        $obj->$prop = $value;
      }
    }
    
		// need to check where is used -> on cancel button on tcStepEdit.tpl
    $obj->loadOnCancelURL = "archiveData.php?tproject_id={$obj->tproject_id}&edit=testcase" . 
        						        "&show_mode={$obj->show_mode}&id=%s&version_id=%s";

		// Used on tcStepEdit.tpl to creare goback_url URL parameter
		$obj->goBackAction = $_SESSION['basehref'] . "lib/testcases/" . $obj->loadOnCancelURL;


		$obj->keywordsViewHREF = "lib/keywords/keywordsView.php?tproject_id={$obj->tproject_id} " .
						 		             ' target="mainframe" class="bold" ' .
        			  	 		       ' title="' . lang_get('menu_manage_keywords') . '"';
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
		$external = $this->tcaseMgr->getExternalID($argsObj->tcase_id,$argsObj->tproject_id);
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
	function create(&$argsObj,$oWebEditorKeys,$userInput)
	{
    $guiObj = $this->initGuiBean($argsObj);
    $guiObj->initWebEditorFromTemplate = true;
 

		$info = $this->tcaseMgr->tree_manager->get_node_hierarchy_info($guiObj->tsuiteID);
		$guiObj->tsuite = array('name' => $info['name']);
    $guiObj->main_descr = lang_get('testsuite') . $this->sep_1 . $info['name'] . $this->sep_2 . lang_get('title_new_tc');
    
		$tcStatusConfig = getConfigAndLabels('testCaseStatus');

    $guiObj->tc = array('id' => 0, 'name' => '', 'importance' => config_get('testcase_importance_default'),
  	                    'execution_type' => testcase::EXECUTION_TYPE_MANUAL,
  	                    'estimated_execution_duration' => '',
  	                    'status' => $tcStatusConfig['cfg']['draft']);

    	
    list($guiObj->optionTransfer,$guiObj->optionTransferJSObject) = $this->initKeywordGuiControl($argsObj,$userInput);                                        
        
                                        
		$cfPlaces = $this->tcaseMgr->buildCFLocationMap();
		foreach($cfPlaces as $locationKey => $locationFilter)
		{ 
			// custom fields do not lose entered values on errors
			$guiObj->cf[$locationKey] = 
				$this->tcaseMgr->html_table_of_custom_field_inputs(null,null,'design','',null,null,
				                                                   $argsObj->tproject_id,$locationFilter, $_REQUEST);
		}	
		$templateCfg = templateConfiguration('tcNew');
		$guiObj->template = $templateCfg->default_template;
		
    return $guiObj;
	}

	/**
	 * 
	 *
	 */
	function doCreate(&$argsObj,$oWebEditorKeys,$request)
	{
    $guiObj = $this->create($argsObj,$oWebEditorKeys,$request);
		$item = array('parent_id' => $argsObj->tsuiteID, 'name' => $argsObj->name,
					        'summary' => $argsObj->summary, 'preconditions' => $argsObj->preconditions,
					        'steps' => $argsObj->tcaseSteps, 'author_id' => $argsObj->user_id,
					        'keywords_id' => $argsObj->assigned_keyword_list,
					        'tc_order' => config_get('treemenu_default_testcase_order'), 
					        'execution_type' => $argsObj->exec_type,
					        'importance' => $argsObj->importance, 'status' => $argsObj->tc_status,
					        'estimated_execution_duration' => $argsObj->estimated_execution_duration);
		  
		// compute order
		$nt2exclude = array('testplan' => 'exclude_me','requirement_spec'=> 'exclude_me','requirement'=> 'exclude_me');
		$siblings = $this->tcaseMgr->tree_manager->get_children($argsObj->tsuiteID,$nt2exclude);
	
		if( !is_null($siblings) )
		{
			$dummy = end($siblings);
			$item['tc_order'] = $dummy['node_order']+1;
		}

		$tcase = $this->tcaseMgr->create($item,
		                                 array('check_duplicate_name' => config_get('check_names_for_duplicates'),
		                                       'action_on_duplicate_name' => 'block'));
		if($tcase['status_ok'])
		{
			if($argsObj->stay_here)
			{	 
				$cf_map = $this->tcaseMgr->cfield_mgr->get_linked_cfields_at_design($argsObj->tproject_id,ENABLED,
			     	                                                                NO_FILTER_SHOW_ON_EXEC,'testcase');
			

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
				exit();
			}
		}
		elseif(isset($tcase['msg']))
		{
			$guiObj->user_feedback = lang_get('error_tc_add');
			$guiObj->user_feedback .= '' . $tcase['msg'];
	    $guiObj->sqlResult = 'ko';
    	$opt_list = $argsObj->assigned_keyword_list;
    	$guiObj->initWebEditorFromTemplate = false;
		}
	
		$templateCfg = templateConfiguration('tcNew');
		$guiObj->template=$templateCfg->default_template;
		return $guiObj;    
  }




	/*
	  function: edit (Test Case)
	
	  args:
	  
	  returns: 
	
	*/
	function edit(&$argsObj,$oWebEditorKeys,$userInput)
	{
    $guiObj = $this->initGuiBean($argsObj);
    $this->keywordSet['testcase'] = $this->tcaseMgr->get_keywords_map($argsObj->tcase_id," ORDER BY keyword ASC ");
    list($guiObj->optionTransfer,$guiObj->optionTransferJSObject) = $this->initKeywordGuiControl($argsObj,$userInput);                                        


  	$guiObj->tc = $this->tcaseMgr->get_by_id($argsObj->tcase_id,$argsObj->tcversion_id);
  	$guiObj->tc = $guiObj->tc[0];
  	foreach($oWebEditorKeys as $key)
   	{
   		$guiObj->$key = isset($guiObj->tc[$key]) ? $guiObj->tc[$key] : '';
   		$argsObj->$key = $guiObj->$key;
  	}
 		
  	$guiObj->cf = null;
		$cfPlaces = $this->tcaseMgr->buildCFLocationMap();
		foreach($cfPlaces as $locationKey => $locationFilter)
		{ 
			$guiObj->cf[$locationKey] = 
				$this->tcaseMgr->html_table_of_custom_field_inputs($argsObj->tcase_id,null,'design','',
				                                                   $argsObj->tcversion_id,null,null,$locationFilter);
		}	

   	$templateCfg = templateConfiguration('tcEdit');
		$guiObj->template = $templateCfg->default_template;

    new dBug($guiObj);
    return $guiObj;
  }


  /*
    function: doUpdate
    
			  IMPORTANT NOTICE	
			  this method will not return to caller  but act directly on GUI				
    args:
    
    returns: 

  */
  function doUpdate(&$argsObj,$request)
	{
		$item = array('id' => $argsObj->tcase_id, 'tcversion_id' => $argsObj->tcversion_id,
					        'name' => $argsObj->name,
					        'summary' => $argsObj->summary, 'preconditions' => $argsObj->preconditions,
					        'user_id' => $argsObj->user_id,'importance' => $argsObj->importance,
					        'estimated_execution_duration' => $argsObj->estimated_execution_duration,
					        'steps' => $argsObj->tcaseSteps, 
					        'keywords_id' => $argsObj->assigned_keyword_list,
					        'execution_type' => $argsObj->exec_type,
					        'status' => $argsObj->tc_status);
		
		$ret = $this->tcaseMgr->update($item);	
		$this->show($argsObj,$request,$ret);  // CF are written to db here
 
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
      	    
	  	      $smartyObj->templateCfg = $this->templateCfg;
            $identity = new stdClass();
            $identity->tproject_id = $argsObj->tproject_id;
            $identity->id = $argsObj->tcase_id;
            $identity->version_id = $argsObj->tcversion_id;
	  	      $this->tcaseMgr->show($smartyObj,$guiObj,$identity,$this->grants); 

      	}
      	return $guiObj;
  }

  /**
   * 
   *
   */
	function delete(&$argsObj,$request)
	{
	  new dBug($argsObj);
	  
    $guiObj = $this->initGuiBean($argsObj);
 		$guiObj->delete_message = '';
	
 		$guiObj->exec_status_quo = $this->tcaseMgr->get_exec_status($argsObj->tcase_id,null, 
 																	                              array('addExecIndicator' => true));

		$guiObj->delete_enabled = 1;
		if( $guiObj->exec_status_quo['executed'] && !$this->grants->testproject_delete_executed_testcases )
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
		list($external_id,$root) = $this->tcaseMgr->getPrefix($argsObj->tcase_id,$argsObj->tproject_id);
    $external_id .= $this->cfg->testcase->glue_character . $tcinfo[0]['tc_external_id'];
        
		$guiObj->title = lang_get('title_del_tc');
		$guiObj->testcase_name =  $tcinfo[0]['name'];
		$guiObj->testcase_id = $argsObj->tcase_id;
		$guiObj->tcversion_id = testcase::ALL_VERSIONS;
		$guiObj->refreshTree = 1;
 		$guiObj->main_descr = lang_get('title_del_tc') . TITLE_SEP . $external_id . TITLE_SEP . $tcinfo[0]['name'];  
    
    $templateCfg = templateConfiguration('tcDelete');
  	$guiObj->template = $templateCfg->default_template;
	
		return $guiObj;
	}

  /**
   * 
   *
   */
	function doDelete(&$argsObj,$request)
	{
   	$guiObj = $this->initGuiBean($argsObj,array('tproject_id'));
 		$guiObj->user_feedback = '';
		$guiObj->delete_message = '';
		$guiObj->action = 'deleted';
		$guiObj->sqlResult = 'ok';
 		$guiObj->delete_mode = 'single';

		$tcinfo = $this->tcaseMgr->get_by_id($argsObj->tcase_id,$argsObj->tcversion_id);
		list($external_id,$root) = $this->tcaseMgr->getPrefix($argsObj->tcase_id,$argsObj->tproject_id);
    $external_id .= $this->cfg->testcase->glue_character . $tcinfo[0]['tc_external_id'];
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
  
  	// refresh must be forced to avoid a wrong tree situation.
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
        $guiObj->goBackAction = sprintf($guiObj->goBackAction,$argsObj->tcase_id,$argsObj->tcversion_id);
        
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


		// seems to be used as argument "attach_loadOnCancelURL"
		// on {include file="inc_attachments.tpl"}
		// on tcView.tpl
    $guiObj->loadOnCancelURL = sprintf($guiObj->loadOnCancelURL,$argsObj->tcase_id,$argsObj->tcversion_id);
		$guiObj->goBackAction = sprintf($guiObj->goBackAction,$argsObj->tcase_id,$argsObj->tcversion_id);

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

		$guiObj->tproject_id = $argsObj->tproject_id;
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
		$guiObj->template = $templateCfg->default_template;

		// Need to understand if booth are needed
    $guiObj->loadOnCancelURL = sprintf($guiObj->loadOnCancelURL,$argsObj->tcase_id,$argsObj->tcversion_id);
		$guiObj->goBackAction = sprintf($guiObj->goBackAction,$argsObj->tcase_id,$argsObj->tcversion_id);

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
		
		// Want to remain on same screen till user choose to cancel => go away
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
		$guiObj->step_number = $argsObj->step_number;
		$guiObj->step_id = $argsObj->step_id;

		$guiObj->step_set = $this->tcaseMgr->get_step_numbers($argsObj->tcversion_id);
		$guiObj->step_set = is_null($guiObj->step_set) ? '' : implode(",",array_keys($guiObj->step_set));

        $guiObj->loadOnCancelURL = sprintf($guiObj->loadOnCancelURL,$argsObj->tcase_id,$argsObj->tcversion_id);
		$guiObj->goBackAction = sprintf($guiObj->goBackAction,$argsObj->tcase_id,$argsObj->tcversion_id);

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
    $viewer_args = array();
    
    $guiObj = $this->initGuiBean($argsObj);
   	$guiObj->refreshTree = ($argsObj->refreshTree && $userFeedback['status_ok']) ? 1 : 0;
   	$guiObj->has_been_executed = $argsObj->has_been_executed;
		$guiObj->steps_results_layout = $this->cfg->spec->steps_results_layout;

    $guiObj->user_feedback = isset($userFeedback['msg']) ? $userFeedback['msg'] : '';
    if($userFeedback['status_ok'])
		{
      $guiObj->user_feedback = '';
  		$ENABLED = 1;
	  	$NO_FILTERS = null;
		  $cf_map = $this->tcaseMgr->cfield_mgr->get_linked_cfields_at_design($argsObj->tproject_id,
		                                                                      $ENABLED,$NO_FILTERS,'testcase') ;

			$this->tcaseMgr->cfield_mgr->design_values_to_db($request,$argsObj->tcversion_id,$cf_map);
      $guiObj->attachments[$argsObj->tcase_id] = $this->tcaseMgr->getAttachmentInfos($argsObj->tcase_id);
		}

    $viewer_args['refreshTree'] = $guiObj->refreshTree;
    $viewer_args['user_feedback'] = $guiObj->user_feedback;
    $smartyObj->templateCfg = $this->templateCfg;

    $identity = new stdClass();
    $identity->tproject_id = $argsObj->tproject_id;
    $identity->id = $argsObj->tcase_id;
    $identity->version_id = $argsObj->tcversion_id;

    $this->tcaseMgr->show($smartyObj,$guiObj,$identity,$this->grants); 
		exit();	
	}


  private function initKeywordGuiControl($argsObj,$userInput)
  {
    $widget = new stdClass();
    $widget->optionTransfer = tlKeyword::optionTransferGuiControl();
    
    $widget->optionTransfer->from->lbl = lang_get('available_kword');
    $widget->optionTransfer->to->lbl = lang_get('assigned_kword');

    $widget->optionTransfer->setNewRightInputName('assigned_keyword_list');
    $widget->optionTransfer->initFromPanel(null,lang_get('available_kword'));
    $widget->optionTransfer->initToPanel(null,lang_get('assigned_kword'));
  
    $widget->optionTransfer->setFromPanelContent($this->keywordSet['testproject']);
    $widget->optionTransfer->setToPanelContent($this->keywordSet['testcase']);
    
    $inputNames = $widget->optionTransfer->getHtmlInputNames();
    $toKeywordSet = isset($userInput[$inputNames->newRight])? $userInput[$inputNames->newRight] : "";
 
    $widget->optionTransfer->updatePanelsContent($toKeywordSet);
    
    $widget->optionTransferJSObject = json_encode($inputNames);
    
    return array($widget->optionTransfer,$widget->optionTransferJSObject);
  }


	function doCreateNewVersion(&$argsObj,$oWebEditorKeys,$userInput)
	{
  	
    $guiObj = $this->initGuiBean($argsObj);
  	

  	$viewer_args['action'] = "do_update";
  	$viewer_args['refreshTree'] = !tlTreeMenu::REFRESH_GUI;
  	$viewer_args['user_feedback'] = '';
  	$viewer_args['msg_result'] = '';
  	$op = $this->tcaseMgr->create_new_version($args->tcase_id,$args->user_id,$args->tcversion_id);
  	if ($op['msg'] == "ok")
  	{
  		$viewer_args['user_feedback'] = sprintf(lang_get('tc_new_version'),$op['version']);
  		$viewer_args['msg_result'] = 'ok';
  	}
  	$this->show($argsObj,$userInput);					         
  }

} // end class  
?>