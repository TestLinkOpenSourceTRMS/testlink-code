<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public 
 * License 2 or later.
 *
 * testcases commands
 *
 * @filesource  testcaseCommands.class.php
 * @package     TestLink
 * @author      Francisco Mancardi - francisco.mancardi@gmail.com
 * @copyright   2007-2023, TestLink community 
 * @link        http://www.testlink.org/
 *
 **/

class testcaseCommands {
  private $db;
  private $tcaseMgr;
  private $tprojectMgr;
  private $templateCfg;
  private $execution_types;
  private $grants;
  private $tproject_id;

  const UPDATECFONDB = true;

  /**
   *
   */
  function __construct(&$db,&$userObj,$tproject_id) 
  {
    $this->db = $db;
    $this->tcaseMgr = new testcase($db);
    $this->tcaseMgr->setTestProject($tproject_id);

    $this->tprojectMgr = &$this->tcaseMgr->tproject_mgr;
    $this->tproject_id = $tproject_id;

    $this->execution_types = $this->tcaseMgr->get_execution_types();
    $this->grants = new stdClass();

    $g2c = array('mgt_modify_tc','mgt_view_req','testplan_planning',
                 'req_tcase_link_management','mgt_modify_req',
                 'testproject_delete_executed_testcases',
                 'testproject_edit_executed_testcases');
    foreach($g2c as $grant) {
      $this->grants->$grant = $userObj->hasRight($db,$grant,$tproject_id);
    }

    $this->grants->requirement_mgmt = $this->grants->mgt_modify_req ||
      $this->grants->req_tcase_link_management;

    $this->tables = $this->tcaseMgr->getDBTables(array('keywords','platforms'));  
  }

  function setTemplateCfg($cfg) {
    $this->templateCfg=$cfg;
  }

  /**
   * 
   * context 
   *   'tcase_id',
   *   'tcversion_id',
   *   'tplan_id',
   *   'tproject_id'
   *
   *  is initialized here!!
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

    $key = 'req_tcase_link_management';
    $obj->$key = $obj->grants->$key;

    $obj->has_been_executed = false;
    $obj->initWebEditorFromTemplate = false;

    $obj->main_descr = '';
    $obj->name = '';
    $obj->path_info = null;
    $obj->refreshTree = 0;
    $obj->sqlResult = '';
    $obj->step_id = -1;
    $obj->step_set = '';
    $obj->steps = '';

    $obj->uploadOp = null;
    if (property_exists($argsObj, 'uploadOp')) {
      $obj->steps = '';
    }

    $dummy = testcase::getLayout();
    $obj->tableColspan = 
      $dummy->tableToDisplayTestCaseSteps->colspan;

    // Context!!!  
    $tck = [
      'tcase_id',
      'tcversion_id',
      'tplan_id',
      'tproject_id'
    ];
    foreach ($tck as $pkey) {
      $obj->$pkey = property_exists($argsObj,$pkey) ? $argsObj->$pkey : -1;
    }

    $obj->viewerArgs = null;

    $p2check = 'goback_url';
    $obj->$p2check = '';
    if( property_exists($argsObj,$p2check) ) {
      $obj->$p2check = !is_null($argsObj->$p2check) ? $argsObj->$p2check : ''; 

      if ($obj->$p2check != '') {
        if( property_exists($obj, 'tplan_id') ) {
          $obj->$p2check .= "&tplan_id={$obj->tplan_id}";
        }

        if( property_exists($obj, 'tproject_id') ) {
          $obj->$p2check .= "&tproject_id={$obj->tproject_id}";
        }
      }
    }
    
    $p2check = 'show_mode';
    if( property_exists($argsObj,$p2check) ) {
      $obj->$p2check = !is_null($argsObj->$p2check) ? $argsObj->$p2check : 'show'; 
    }

    $obj->codeTrackerEnabled = $this->tprojectMgr->isCodeTrackerEnabled($this->tproject_id);

    // need to check where is used
    $obj->loadOnCancelURL = 
      "archiveData.php?edit=testcase&show_mode={$obj->show_mode}&id=%s&version_id=%s";
    $obj->tcaseMgrURL = "archiveData.php?edit=testcase&id=%s&caller=%s";

    if( property_exists($obj, 'tplan_id') ) {
      $obj->loadOnCancelURL .= "&tplan_id={$obj->tplan_id}";
      $obj->tcaseMgrURL .= "&tplan_id={$obj->tplan_id}";
    }

    if( property_exists($obj, 'tproject_id') ) {
      $obj->loadOnCancelURL .= "&tproject_id={$obj->tproject_id}";
      $obj->tcaseMgrURL .= "&tproject_id={$obj->tproject_id}";
    }

    if( property_exists($obj, 'show_mode') ) {
      $obj->loadOnCancelURL .= "&show_mode={$obj->show_mode}";
    }

    $obj->codeTrackerEnabled = $this->tprojectMgr->isCodeTrackerEnabled($this->tproject_id);

    return $obj;
  }
   
  /**
   * initialize common test case information, useful when working on steps
   *
   */
  function initTestCaseBasicInfo(&$argsObj,&$guiObj,$opt=null) 
  {

    $my['opt'] = array('accessByStepID' => true);
    $my['opt'] = array_merge($my['opt'],(array)$opt);

    // Security
    // https://cxsecurity.com/issue/WLB-2019110139
    if (intval($argsObj->tcase_id) == 0 && 
        intval($argsObj->tcversion_id) ==0) {
      die("Error Processing Request:" . __METHOD__);
    }

    $greenCard = [
      'tcase_id' => $argsObj->tcase_id, 
      'tcversion_id' => $argsObj->tcversion_id
    ];
    
    if( $my['opt']['accessByStepID'] ) {  
      foreach($greenCard as $ky) {
        // this logic need to be explained BETTER
        if($ky == 0) {
          $greenCard = $this->tcaseMgr->getIdCardByStepID($argsObj->step_id);   
          break;
        }  
      }
    }

    $gopt = [
      'output' => 'full_without_steps',
      'renderGhost' => true,
      'renderImageInline' => true,
      'renderVariables' => true,
      'tproject_id' => intval($argsObj->testproject_id)
    ]; 

    $tcaseInfo = $this->tcaseMgr->get_by_id(
      $greenCard['tcase_id'],$greenCard['tcversion_id'],null,$gopt);

    $external = $this->tcaseMgr->getExternalID(
      $greenCard['tcase_id'],$argsObj->testproject_id);
    $tcaseInfo[0]['tc_external_id'] = $external[0];
    $guiObj->testcase = $tcaseInfo[0];

    if(!isset($guiObj->testcase['ghost'])) {
      $guiObj->testcase['ghost'] = null;  
    }  
    $guiObj->authorObj = tlUser::getByID($this->db,$guiObj->testcase['author_id']);
    
    $guiObj->updaterObj = null;
    if( !is_null($guiObj->testcase['updater_id']) ) {
      $guiObj->updaterObj = tlUser::getByID($this->db,$guiObj->testcase['updater_id']);
    } 

    $cfCtx = array('scope' => 'design',
                   'tproject_id' => $argsObj->testproject_id,
                   'link_id' => $argsObj->tcversion_id);

    $cfPlaces = $this->tcaseMgr->buildCFLocationMap();
    foreach($cfPlaces as $cfpKey => $cfpFilter) {
      $guiObj->cfieldsDesignTime[$cfpKey] =
         $this->tcaseMgr->htmlTableOfCFValues(
           $argsObj->tcase_id,$cfCtx,$cfpFilter);
    }


  }

   
   
   
   
  /**
   * 
   *
   */
  function create(&$argsObj,&$otCfg)
  {
    $parentKeywords = array();
    $guiObj = $this->initGuiBean($argsObj);
    $guiObj->initWebEditorFromTemplate = true;
      
    $guiObj->containerID = $argsObj->container_id;
    if($argsObj->container_id > 0) {
      $pnode_info = $this->tcaseMgr->tree_manager->get_node_hierarchy_info($argsObj->container_id);
      $node_descr = array_flip($this->tcaseMgr->tree_manager->get_available_node_types());
      $guiObj->parent_info['name'] = $pnode_info['name'];
      $guiObj->parent_info['description'] = lang_get($node_descr[$pnode_info['node_type_id']]);

      // get keywords
      $tsuiteMgr = new testsuite($this->db);      
      $parentKeywords = $tsuiteMgr->getKeywords($argsObj->container_id);  
    }
    $sep_1 = config_get('gui_title_separator_1');
    $sep_2 = config_get('gui_title_separator_2'); 
    $guiObj->main_descr = $guiObj->parent_info['description'] . $sep_1 . $guiObj->parent_info['name'] . 
                          $sep_2 . lang_get('title_new_tc');
      
    
    $otCfg->to->map = array();
    keywords_opt_transf_cfg($otCfg,implode(',',array_keys((array)$parentKeywords)));

    $guiObj->tc = array('id' => 0, 'name' => '', 'importance' => config_get('testcase_importance_default'),
                        'status' => null, 'estimated_exec_duration' => null, 
                        'execution_type' => TESTCASE_EXECUTION_TYPE_MANUAL);

    $guiObj->opt_cfg=$otCfg;
    $templateCfg = templateConfiguration('tcNew');
    $guiObj->template=$templateCfg->default_template;


    $cfPlaces = $this->tcaseMgr->buildCFLocationMap();
    foreach($cfPlaces as $locationKey => $locationFilter) { 
      $guiObj->cf[$locationKey] = 
      $this->tcaseMgr->html_table_of_custom_field_inputs(null,null,
        'design','',null,null,
        $argsObj->testproject_id,$locationFilter, $_REQUEST);
    }  

    $guiObj->cancelActionJS = 'location.href=fRoot+' . "'" . 
      "lib/testcases/archiveData.php?id=" . 
      intval($argsObj->container_id);

    if( property_exists($guiObj, 'tplan_id') ) {
      $guiObj->cancelActionJS .= "&tplan_id={$guiObj->tplan_id}";
    }

    if( property_exists($argsObj, 'tproject_id') ) {
      $guiObj->tproject_id = $argsObj->tproject_id;
      $guiObj->cancelActionJS .= "&tproject_id={$argsObj->tproject_id}";
    }

    if( property_exists($guiObj, 'show_mode') ) {
      $guiObj->cancelActionJS .= "&show_mode={$guiObj->show_mode}";
    }


    $guiObj->cancelActionJS .= 
      '&edit=testsuite&level=testsuite&containerType=testsuite' .  "'"; 

    return $guiObj;
  }

  /**
   * 
   *
   */
  function doCreate(&$argsObj,&$otCfg,$oWebEditorKeys,$request) {
    $guiObj = $this->create($argsObj,$otCfg,$oWebEditorKeys);
      
    // compute order
    $new_order = config_get('treemenu_default_testcase_order');
    $co = $this->tcaseMgr->tree_manager->getBottomOrder($argsObj->container_id,array('node_type' => 'testcase'));
    if( $co > 0){
      $new_order = $co+1; 
    }  

    $options = array('check_duplicate_name' => config_get('check_names_for_duplicates'),
                     'action_on_duplicate_name' => 'block',
                     'status' => $argsObj->tc_status,
                     'estimatedExecDuration' => $argsObj->estimated_execution_duration);

    $tcase = $this->tcaseMgr->create($argsObj->container_id,$argsObj->name,$argsObj->summary,$argsObj->preconditions,
                                     $argsObj->tcaseSteps,$argsObj->user_id,$argsObj->assigned_keywords_list,
                                     $new_order,testcase::AUTOMATIC_ID,
                                     $argsObj->exec_type,$argsObj->importance,$options);

    if($tcase['status_ok']) {
      $guiObj->actionOK = true;
      if($argsObj->stay_here) {   
        $cf_map = $this->tcaseMgr->cfield_mgr->get_linked_cfields_at_design($argsObj->testproject_id,ENABLED,
                                                                             NO_FILTER_SHOW_ON_EXEC,'testcase');
      
        $this->tcaseMgr->cfield_mgr->design_values_to_db($_REQUEST,$tcase['tcversion_id'],$cf_map);

        $guiObj->user_feedback = sprintf(lang_get('tc_created'),$argsObj->name);
        $guiObj->sqlResult = 'ok';
        $guiObj->initWebEditorFromTemplate = true;
        $guiObj->cleanUpWebEditor = true;
        $opt_list = '';
      }
      else {
        // we will not return to caller
        $argsObj->tcase_id = intval($tcase['id']);
        $argsObj->tcversion_id = intval($tcase['tcversion_id']);
        
        // BAD Choice Custom fields are written to db on $this->show()
        $this->show($argsObj,$request, array('status_ok' => 1));
      }
    }
    elseif(isset($tcase['msg'])) {
      $guiObj->actionOK = false;
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
  function edit(&$argsObj,&$otCfg,$oWebEditorKeys) {
    $guiObj = $this->initGuiBean($argsObj);
    $otCfg->to->map = 
      $this->tcaseMgr->get_keywords_map($argsObj->tcase_id,$argsObj->tcversion_id,
        array('orderByClause' =>" ORDER BY keyword ASC "));

    keywords_opt_transf_cfg($otCfg, $argsObj->assigned_keywords_list);

    $gopt = [
      'renderImageInline' => false, 
      'renderImageInline' => false, 
      'caller' => __METHOD__
    ];
    
    $tc_data = $this->tcaseMgr->get_by_id($argsObj->tcase_id,$argsObj->tcversion_id,null,$gopt);
    foreach($oWebEditorKeys as $key) {
      $guiObj->$key = isset($tc_data[0][$key]) ?  $tc_data[0][$key] : '';
      $argsObj->$key = $guiObj->$key;
    }
     
    $cf_smarty = null;
    $cfPlaces = $this->tcaseMgr->buildCFLocationMap();
    foreach($cfPlaces as $locationKey => $locationFilter)
    { 
      $cf_smarty[$locationKey] = 
        $this->tcaseMgr->html_table_of_custom_field_inputs(
                            $argsObj->tcase_id,null,'design','',
                            $argsObj->tcversion_id,null,null,$locationFilter);
    }  

    $templateCfg = templateConfiguration('tcEdit');
    $guiObj->cf = $cf_smarty;
    $guiObj->tc=$tc_data[0];
    $guiObj->opt_cfg=$otCfg;

    $guiObj->cancelActionJS = 
      'location.href=fRoot+' . "'" . 
      "lib/testcases/archiveData.php?version_id=" . 
      $argsObj->tcversion_id . 
      "&tcversion_id=" . $argsObj->tcversion_id .
      '&edit=testcase&id=' . intval($argsObj->tcase_id); 

    if( property_exists($argsObj, 'tplan_id') ) {
      $guiObj->cancelActionJS .= "&tplan_id={$argsObj->tplan_id}";
    }

    if( property_exists($argsObj, 'tproject_id') ) {
      $guiObj->tproject_id = $argsObj->tproject_id;
      $guiObj->cancelActionJS .= "&tproject_id={$argsObj->tproject_id}";
    }

    if( property_exists($argsObj, 'show_mode') ) {
      $guiObj->cancelActionJS .= "&show_mode={$argsObj->show_mode}";
    }

    $guiObj->cancelActionJS .= "'"; 

    $guiObj->template=$templateCfg->default_template;
    return $guiObj;
  }


  /*
    function: doUpdate

    args:
    
    returns: 

  */
  function doUpdate(&$argsObj,$request) {
    $options = array('status' => $argsObj->tc_status,
                     'estimatedExecDuration' => $argsObj->estimated_execution_duration);

    $ret = $this->tcaseMgr->update($argsObj->tcase_id, $argsObj->tcversion_id, $argsObj->name, 
                                   $argsObj->summary, $argsObj->preconditions, $argsObj->tcaseSteps, 
                                   $argsObj->user_id, $argsObj->assigned_keywords_list,
                                   testcase::DEFAULT_ORDER, $argsObj->exec_type, 
                                   $argsObj->importance,$options);

    $this->show($argsObj,$request,$ret);
    return $guiObj;
  }  


  /**
   * doAdd2testplan
   *
   */
  function doAdd2testplan(&$argsObj,$request) {
    $smartyObj = new TLSmarty();
    $smartyObj->assign('attachments',null);
    $guiObj = $this->initGuiBean($argsObj);
    $guiObj->refreshTree = $argsObj->refreshTree? 1 : 0;
 
    $tplan_mgr = new testplan($this->db);
   
    // $request['add2tplanid']
    // main key: testplan id
    // sec key : platform_id
    $item2link = null;
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
            
      $identity = new stdClass();
      $identity->tproject_id = $argsObj->tproject_id;
      $identity->id = $argsObj->tcase_id;
      $identity->version_id = $argsObj->tcversion_id;
      
      $this->tcaseMgr->show($smartyObj,$guiObj,$identity,$this->grants); 
      exit();
    }
    return $guiObj;
  }

  /**
   * add2testplan - is really needed???? 20090308 - franciscom - TO DO
   *
   */
  function add2testplan(&$argsObj,$request)
  {
  }


  /**
   * 
   *
   * @internal revisions
   */
  function delete(&$argsObj,$request) {
    
    $guiObj = $this->initGuiBean($argsObj);
    $guiObj->delete_message = '';
    $cfg = config_get('testcase_cfg');

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
    list($prefix,$root) = $this->tcaseMgr->getPrefix($argsObj->tcase_id,$argsObj->testproject_id);
    $prefix .= $cfg->glue_character;
    $external_id = $prefix . $tcinfo[0]['tc_external_id'];
        
    $guiObj->title = lang_get('title_del_tc');
    $guiObj->testcase_name =  $tcinfo[0]['name'];
    $guiObj->testcase_id = $argsObj->tcase_id;
    $guiObj->tcversion_id = testcase::ALL_VERSIONS;
    $guiObj->refreshTree = 1;
    $guiObj->main_descr = lang_get('title_del_tc') . TITLE_SEP . $external_id . TITLE_SEP . $tcinfo[0]['name'];  
    
    $guiObj->cancelActionJS = 'location.href=fRoot+' . "'" . 
      'lib/testcases/archiveData.php?version_id=undefined';

    if( property_exists($argsObj, 'tproject_id') ) {
      $guiObj->tproject_id = $argsObj->tproject_id;
      $guiObj->cancelActionJS .= "&tproject_id={$argsObj->tproject_id}";
    }

    $guiObj->cancelActionJS .=
      '&edit=testcase&id=' . intval($guiObj->testcase_id) . "'";    


    $templateCfg = templateConfiguration('tcDelete');
    $guiObj->template = $templateCfg->default_template;
    return $guiObj;
  }

  /**
   * 
   *
   */
  function doDelete(&$argsObj,$request) {
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
    
    $guiObj->main_descr = lang_get('title_del_tc') . ":" . $external_id . TITLE_SEP . htmlspecialchars($tcinfo[0]['name']);
    
    if($argsObj->tcversion_id == testcase::ALL_VERSIONS)
    {
	  $guiObj->refreshTree = 1;
	  logAuditEvent(TLS("audit_testcase_deleted",$external_id),"DELETE",$argsObj->tcase_id,"testcases");
      $guiObj->user_feedback = sprintf(lang_get('tc_deleted'), ":" . $external_id . TITLE_SEP . $tcinfo[0]['name']);
    }
	  else{
      $guiObj->main_descr .= " " . lang_get('version') . " " . $tcinfo[0]['version'];
	  // When deleting JUST one version, there is no need to refresh tree
      $guiObj->refreshTree = 0;
	  logAuditEvent(TLS("audit_testcase_version_deleted",$tcinfo[0]['version'],$external_id),"DELETE",$argsObj->tcase_id,"testcases");
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
   */
  function createStep(&$argsObj,$request) {
    $guiObj = $this->initGuiBean($argsObj);

    $this->initTestCaseBasicInfo($argsObj,$guiObj);

    $guiObj->main_descr = sprintf(lang_get('create_step'), $guiObj->testcase['tc_external_id'] . ':' . 
                    $guiObj->testcase['name'], $guiObj->testcase['version']); 
        
    $max_step = $this->tcaseMgr->get_latest_step_number($argsObj->tcversion_id); 
    $max_step++;;

    $guiObj->step_number = $max_step;
    $guiObj->step_exec_type = $guiObj->testcase['execution_type']; 
    $guiObj->tcversion_id = $argsObj->tcversion_id;

    $guiObj->step_set = $this->tcaseMgr->get_step_numbers($argsObj->tcversion_id);
    $guiObj->step_set = is_null($guiObj->step_set) ? '' : implode(",",array_keys($guiObj->step_set));
    $guiObj->loadOnCancelURL = sprintf($guiObj->loadOnCancelURL,$argsObj->tcase_id,$argsObj->tcversion_id);
        
    // Get all existent steps
    $guiObj->tcaseSteps = $this->tcaseMgr->get_steps($argsObj->tcversion_id);
        
    $templateCfg = templateConfiguration('tcStepEdit');
    $guiObj->template = $templateCfg->default_template;
    $guiObj->action = __FUNCTION__;
    
    return $guiObj;
  }

  /**
   * doCreateStep
   *
   */
  function doCreateStep(&$argsObj,$request,$doAndExit=false) {
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
                              
    $guiObj->doExit = false;
    if( $op['status_ok'] )
    {
      $guiObj->doExit = $doAndExit;
      $guiObj->user_feedback = sprintf(lang_get('step_number_x_created'),$argsObj->step_number);
      $guiObj->step_exec_type = $guiObj->testcase['execution_type'];
      $guiObj->cleanUpWebEditor = true;
      $this->tcaseMgr->update_last_modified($argsObj->tcversion_id,$argsObj->user_id);
      $this->initTestCaseBasicInfo($argsObj,$guiObj);
    }  

    if(!$guiObj->doExit)
    {  
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
    }
    return $guiObj;
  }

  /**
   * doCreateStepAndExit
   *
   */
  function doCreateStepAndExit(&$argsObj,$request) {
    $guiObj = $this->doCreateStep($argsObj,$request,true);
    if($guiObj->doExit) {
      // when working on step, refreshing tree is nonsense
      $argsObj->refreshTree = 0;

      $opt= array('updateCFOnDB' => !self::UPDATECFONDB);
      $this->show($argsObj,$request,array('status_ok' => true),$opt);
      exit();
    } else {
      return $guiObj;
    }  
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
    $guiObj->step_number = $stepInfo['step_number'];

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
  function doUpdateStep(&$argsObj,$request) {
    $guiObj = $this->initGuiBean($argsObj);
    $guiObj->user_feedback = '';
    
    $this->initTestCaseBasicInfo($argsObj,$guiObj);
    try {
      $stepInfo = $this->tcaseMgr->get_step_by_id($argsObj->step_id);
    }
    catch (Exception $e) {
      echo $e->getMessage();
    }


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
    

    $guiObj = $this->editStep($argsObj,$request);  
    return $guiObj;
  }

  /**
   * doUpdateStepAndExit
   *
   */
  function doUpdateStepAndExit(&$argsObj,$request) {
    $this->doUpdateStep($argsObj,$request);

    // when working on step, refreshing tree is nonsense
    $argsObj->refreshTree = 0;
    $opt= array('updateCFOnDB' => !self::UPDATECFONDB);
    $this->show($argsObj,$request,array('status_ok' => true),$opt);
  }
  

  /**
   * doReorderSteps
   *
   */
  function doReorderSteps(&$argsObj,$request) {
    $guiObj = $this->initGuiBean($argsObj);
    $guiObj->main_descr = lang_get('test_case');
    $this->tcaseMgr->set_step_number($argsObj->step_set);
    $this->tcaseMgr->update_last_modified($argsObj->tcversion_id,$argsObj->user_id);
    $this->initTestCaseBasicInfo($argsObj,$guiObj);

    $argsObj->refreshTree = 0;
    $opt= array('updateCFOnDB' => !self::UPDATECFONDB);
    $this->show($argsObj,$request,array('status_ok' => true),$opt);
    exit();

  }


  /**
   * doDeleteStep
   *
   */
  function doDeleteStep(&$argsObj,$request) {
    $guiObj = $this->initGuiBean($argsObj);

    $guiObj->main_descr = lang_get('test_case');
    $guiObj->viewerArgs = array();
    $guiObj->refreshTree = 0;
    $guiObj->user_feedback = '';

    $step_node = $this->tcaseMgr->tree_manager->get_node_hierarchy_info($argsObj->step_id);

    $tcversion_node = $this->tcaseMgr->tree_manager->get_node_hierarchy_info($step_node['parent_id']);
    $argsObj->tcversion_id = $step_node['parent_id'];
    $argsObj->tcase_id = $tcversion_node['parent_id'];
    $argsObj->show_mode = $guiObj->show_mode;

    $guiObj->template = $this->buildTemplateURLForEdit($argsObj);

    $op = $this->tcaseMgr->delete_step_by_id($argsObj->step_id);
    $this->tcaseMgr->update_last_modified($argsObj->tcversion_id,$argsObj->user_id);

    $this->initTestCaseBasicInfo($argsObj,$guiObj);

    $argsObj->refreshTree = 0;
    $opt= array('updateCFOnDB' => !self::UPDATECFONDB);
    $this->show($argsObj,$request,array('status_ok' => true),$opt);
    exit();
  }

  /**
      * doCopyStep
     *
     */
  function doCopyStep(&$argsObj,$request) {
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
  function doInsertStep(&$argsObj,$request) {
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
   */
  function doResequenceSteps(&$argsObj,$request) {
    $guiObj = $this->initGuiBean($argsObj);
    $guiObj->user_feedback = '';
    $guiObj->step_exec_type = $argsObj->exec_type;
    $guiObj->tcversion_id = $argsObj->tcversion_id;

    $this->initTestCaseBasicInfo($argsObj,$guiObj);

    if (property_exists($argsObj,'stepSeq') && $argsObj->stepSeq != '') {
      $xx = explode('&', $argsObj->stepSeq);
      $point = 1;
      foreach($xx as $step_id) {
        $renumbered[$step_id] = $point++; 
      }
    } else {
      // Get all existent steps - info needed to do renumbering
      $stepNumberSet = array();
      $stepSet = $this->tcaseMgr->get_steps($argsObj->tcversion_id);
      $stepsQty = count($stepSet);
      for($idx=0; $idx < $stepsQty; $idx++) {
        $renumbered[$stepSet[$idx]['id']] = $idx+1; 
      }
    }

    $this->tcaseMgr->set_step_number($renumbered);
    $this->tcaseMgr->update_last_modified($argsObj->tcversion_id,$argsObj->user_id);

    $guiObj->template = $this->buildTemplateURLForEdit($guiObj);

    return $guiObj;
  }


  /**
   *
   *
   *
   */
  function setImportance(&$argsObj,$request)
  {
    $guiObj = $this->initGuiBean($argsObj);
    $guiObj->user_feedback = '';
    $guiObj->step_exec_type = $argsObj->exec_type;
    $guiObj->tcversion_id = $argsObj->tcversion_id;

    $this->initTestCaseBasicInfo($argsObj,$guiObj);

    $this->tcaseMgr->setImportance($argsObj->tcversion_id,$argsObj->importance);
    $this->tcaseMgr->update_last_modified($argsObj->tcversion_id,$argsObj->user_id);

    // set up for rendering
    $guiObj->template = $this->buildTemplateURLForEdit($guiObj);

    return $guiObj;
  }

  /**
   *
   *
   *
   */
  function setStatus(&$argsObj,$request)
  {
    $guiObj = $this->initGuiBean($argsObj);
    $guiObj->user_feedback = '';
    $guiObj->step_exec_type = $argsObj->exec_type;
    $guiObj->tcversion_id = $argsObj->tcversion_id;

    $this->initTestCaseBasicInfo($argsObj,$guiObj);

    $this->tcaseMgr->setStatus($argsObj->tcversion_id,$argsObj->status);
    $this->tcaseMgr->update_last_modified($argsObj->tcversion_id,$argsObj->user_id);

    // set up for rendering
    $guiObj->template = $this->buildTemplateURLForEdit($guiObj);

    return $guiObj;
  }

  /**
   *
   *
   *
   */
  function setExecutionType(&$argsObj,$request)
  {
    $guiObj = $this->initGuiBean($argsObj);
    $guiObj->user_feedback = '';
    $guiObj->step_exec_type = $argsObj->exec_type;
    $guiObj->tcversion_id = $argsObj->tcversion_id;

    $this->initTestCaseBasicInfo($argsObj,$guiObj);

    $opx = array('updSteps' => $argsObj->applyExecTypeChangeToAllSteps);
    $this->tcaseMgr->setExecutionType($argsObj->tcversion_id,$argsObj->exec_type,$opx);



    $this->tcaseMgr->update_last_modified($argsObj->tcversion_id,$argsObj->user_id);

    // set up for rendering
    $guiObj->template = $this->buildTemplateURLForEdit($guiObj);
    return $guiObj;
  }

  /**
   *
   *
   *
   */
  function setEstimatedExecDuration(&$argsObj,$request)
  {
    $guiObj = $this->initGuiBean($argsObj);
    $guiObj->user_feedback = '';
    $guiObj->step_exec_type = $argsObj->exec_type;
    $guiObj->tcversion_id = $argsObj->tcversion_id;
    $guiObj->tproject_id = 

    $this->initTestCaseBasicInfo($argsObj,$guiObj);

    $this->tcaseMgr->setEstimatedExecDuration($argsObj->tcversion_id,$argsObj->estimatedExecDuration);
    $this->tcaseMgr->update_last_modified($argsObj->tcversion_id,$argsObj->user_id);

    // set up for rendering
    $guiObj->template = $this->buildTemplateURLForEdit($guiObj);

    return $guiObj;
  }


  
  /**
   * 
   *
   */
  function show(&$argsObj,$request,$userFeedback,$opt=null) {
    $smartyObj = new TLSmarty();

    $options = [
      'updateCFOnDB' => true, 
      'updateTPlanLinkToTCV' => false
    ];
    $options = array_merge($options,(array)$opt);

    $updateCFOnDB = $options['updateCFOnDB'];

    $guiObj = $this->initGuiBean($argsObj);
    $identity = $this->buildIdentity($argsObj);

    $guiObj->uploadOp = property_exists($argsObj,'uploadOp') ? $argsObj->uploadOp : '';

    $guiObj->viewerArgs=array();
    $guiObj->refreshTree = ($argsObj->refreshTree && $userFeedback['status_ok']) ? 1 : 0;
    $guiObj->has_been_executed = $argsObj->has_been_executed;
    $guiObj->steps_results_layout = config_get('spec_cfg')->steps_results_layout;
    $guiObj->user_feedback = '';
    
    $webLinkContext = new stdClass();
    $webLinkContext->basehref = $_SESSION['basehref'];
    $webLinkContext->id = $argsObj->tcase_id;
    $webLinkContext->tproject_id =  $argsObj->testproject_id;
    $guiObj->direct_link = $this->tcaseMgr->buildDirectWebLink($webLinkContext);

    if($userFeedback['status_ok']) {
      if( $options['updateTPlanLinkToTCV'] ) {
        $guiObj->updateTPlanLinkToTCV = true; 
        $guiObj->show_mode = 'editOnExec'; 

        // @20190127 the only useful thing there may be is the Rabbit
        $guiObj->additionalURLPar = "&updateTCVToThis=" . $identity->version_id . "&followTheWhiteRabbit=1";  
        $guiObj->closeMyWindow = 1;  
      }

      $guiObj->user_feedback = '';
      if($updateCFOnDB) {  
        $cfCtx = [
          'tproject_id' => $argsObj->testproject_id, 
          'enabled' => 1,
          'node_type' => 'testcase'
        ];
        $cf_map = $this->tcaseMgr->cfield_mgr->getLinkedCfieldsAtDesign($cfCtx);
        $this->tcaseMgr->cfield_mgr->design_values_to_db($request,$identity->version_id,$cf_map);
      }
      
      $guiObj->attachments[$identity->version_id] = 
        getAttachmentInfosFrom($this->tcaseMgr,$identity->version_id);
    }
    else {
      $guiObj->viewerArgs['user_feedback'] = $guiObj->user_feedback = $userFeedback['msg'];
    }

    $guiObj->viewerArgs['refreshTree'] = $guiObj->refreshTree;
    $guiObj->viewerArgs['user_feedback'] = $guiObj->user_feedback;

    $this->tcaseMgr->show($smartyObj,$guiObj,$identity,$this->grants); 
    exit();  
  }


  /**
   *
   */
  private function buildIdentity($cred) {
    $idy= new stdClass();
    if( property_exists($cred, 'tproject_id') ) {
      $idy->tproject_id = $cred->tproject_id;
    }
    else if( property_exists($cred, 'testproject_id')) {
      $idy->tproject_id = $cred->testproject_id;
    }  
    else {
      throw new Exception(__METHOD__ . ' EXCEPTION: test project ID, is mandatory');  
    }  
    $idy->tproject_id = intval($idy->tproject_id);
    $idy->id = intval($cred->tcase_id);
    $idy->version_id = $cred->tcversion_id;
    return $idy;
  }
  

  /**
   * 
   *
   */
  function doAddRelation(&$argsObj,&$request) {
    $guiObj = $this->initGuiBean($argsObj);
    $guiObj->user_feedback = '';

    $this->initTestCaseBasicInfo($argsObj,$guiObj,array('accessByStepID' => false));

    if($argsObj->destination_tcase_id >0) {
      $relTypeInfo = explode('_',$argsObj->relation_type);
      $source_id = $argsObj->tcase_id;
      $destination_id = $argsObj->destination_tcase_id;
      if( $relTypeInfo[1] == "destination" ) {
        $source_id = $argsObj->destination_tcase_id;
        $destination_id = $argsObj->tcase_id;
      }

      $ret = $this->tcaseMgr->addRelation($source_id, $destination_id,$relTypeInfo[0], $argsObj->user_id);
	    $guiObj->user_feedback = sprintf(lang_get($ret['msg']), $argsObj->relation_destination_tcase);	  
    } 
    else {
      $guiObj->user_feedback = sprintf(lang_get('testcase_doesnot_exists'), $argsObj->relation_destination_tcase);
    } 

    // set up for rendering
    // It's OK put fixed 0 on version_id other functions on the chain 
    // to do the display know how to manage this
    $guiObj->template = $this->buildTemplateURLForEdit($guiObj);

    if($guiObj->user_feedback != '') {
      $guiObj->template .= "&add_relation_feedback_msg=" . urlencode($guiObj->user_feedback);
    }  
    return $guiObj;
  }

  /**
   * 
   *
   */
  function doDeleteRelation(&$argsObj,&$request) {
    $guiObj = $this->initGuiBean($argsObj);
    $guiObj->user_feedback = '';

    $this->initTestCaseBasicInfo($argsObj,$guiObj,array('accessByStepID' => false));

    if($argsObj->relation_id >0) {
      $this->tcaseMgr->deleteRelationByID($argsObj->relation_id);
    } 

    // set up for rendering
    $guiObj->template = $this->buildTemplateURLForEdit($guiObj) . "&caller=delRel";

    return $guiObj;
  }



  /**
   * doUpdateStepAndExit
   *
   */
  function doUpdateStepAndInsert(&$argsObj,$request) {
    $this->doUpdateStep($argsObj,$request);
    return $this->doInsertStep($argsObj,$request);
  }


  /**
   * 
   *
   */
  function removeKeyword(&$argsObj,&$request) {
    $guiObj = $this->initGuiBean($argsObj);
    $guiObj->user_feedback = '';

    $this->initTestCaseBasicInfo($argsObj,$guiObj,array('accessByStepID' => false));

    if($argsObj->tckw_link_id > 0) {
      $this->tcaseMgr->deleteKeywordsByLink(
        $guiObj->tcase_id, $argsObj->tckw_link_id,testcase::AUDIT_ON);
    } 

    // set up for rendering
    $guiObj->template = sprintf($guiObj->tcaseMgrURL,$guiObj->tcase_id,__FUNCTION__);

    if( property_exists($guiObj, 'tplan_id') ) {
      $guiObj->template .= "&tplan_id={$guiObj->tplan_id}";
    }

    return $guiObj;
  }

  /**
   * 
   *
   */
  function freeze(&$argsObj,$request) 
  {
    $argsObj->isOpen = 0;
    return $this->setIsOpen($argsObj,$request);
  }

  /**
   * 
   *
   */
  function unfreeze(&$argsObj,$request) {
    $argsObj->isOpen = 1;
    return $this->setIsOpen($argsObj,$request);
  }

  /**
   *
   */
  function setIsOpen(&$argsObj,$request) {
    $guiObj = $this->initGuiBean($argsObj);
    $guiObj->user_feedback = '';
    $guiObj->step_exec_type = $argsObj->exec_type;
    $guiObj->tcversion_id = $argsObj->tcversion_id;

    $this->initTestCaseBasicInfo($argsObj,$guiObj);

    $this->tcaseMgr->setIsOpen(null,$argsObj->tcversion_id,$argsObj->isOpen);
    $this->tcaseMgr->update_last_modified($argsObj->tcversion_id,$argsObj->user_id);

    // set up for rendering
    $guiObj->template = $this->buildTemplateURLForEdit($guiObj);
    return $guiObj;
  }


  /**
   *
   */
  function setActiveAttr(&$argsObj,$request) {
    $guiObj = $this->initGuiBean($argsObj);
    $guiObj->user_feedback = '';
    $guiObj->tcversion_id = $argsObj->tcversion_id;

    $this->initTestCaseBasicInfo($argsObj,$guiObj);

    $this->tcaseMgr->update_active_status($argsObj->tcase_id, 
      $argsObj->tcversion_id, $argsObj->activeAttr);

    $this->tcaseMgr->update_last_modified($argsObj->tcversion_id,$argsObj->user_id);

    $lk = 'audit_tcversion_deactivated';
    $pre = 'DE';
    if( $argsObj->activeAttr ) {
      $pre = '';
      $lk = 'audit_tcversion_activated';
    }

    logAuditEvent(TLS($lk,$guiObj->testcase['name'],
                          $guiObj->testcase['version']),
                  "{$pre}ACTIVATE","testcases");

    $this->show($argsObj,$request,['status_ok' => 1],['updateCFOnDB' => false]);
    exit();
  }


  /**
   * 
   *
   */
  function addKeyword(&$argsObj,&$request) {
    $guiObj = $this->initGuiBean($argsObj);
    $guiObj->user_feedback = '';

    $this->initTestCaseBasicInfo($argsObj,$guiObj,array('accessByStepID' => false));

    $tcExternalID = $guiObj->testcase['tc_external_id'];
    if( (null != $argsObj->free_keywords) && count($argsObj->free_keywords) > 0) {
      $this->tcaseMgr->addKeywords($guiObj->tcase_id,$guiObj->tcversion_id,$argsObj->free_keywords);

      $info = $this->tprojectMgr->get_by_id($this->tproject_id);
      $cfx = config_get('keywords')->byTestProject;
      if( isset($cfx[$info['prefix']]) && 
        $cfx[$info['prefix']]['addTCLinkIntoITS'] &&
        $info['issue_tracker_enabled'] ) {

        $it_mgr = new tlIssueTracker($this->db);
        $argsObj->itsCfg = $it_mgr->getLinkedTo($this->tproject_id);
        $its = $it_mgr->getInterfaceObject($this->tproject_id);
        if( method_exists($its,'addNote') ) {
          $dl = sprintf(lang_get('dlToTCSpecPVCode'), 
                        $tcExternalID)  . ' ' . 
                          lang_get('dlToTCSpecPV') . ' ' . 
                          $this->tcaseMgr->buildDirectWebLink($argsObj);

          // Get keyword for human beins
          $tbl = tlObject::getDBTables(array('keywords'));
          $inClause = "'" . implode("','",$argsObj->free_keywords) . 
                      "'";
          $sql = "SELECT id,keyword FROM {$tbl['keywords']} 
                  WHERE id IN($inClause) ";
          $kwSet = $this->db->fetchRowsIntoMap($sql,'id'); 
          
          $strToDel = isset($cfx[$info['prefix']]['prefix']) ?
                      $cfx[$info['prefix']]['prefix'] : '';
          $strToDel = trim($strToDel);
          foreach( $argsObj->free_keywords as $kw ) {
            if( '' == $strToDel ) {
              $kwv = $kwSet[$kw]['keyword'];
            } else {
              $kwv = str_replace($strToDel,'',
                                 $kwSet[$kw]['keyword']);
            }           
            try {
              $opStatus = $its->addNote($kwv,$dl);
            } catch(Exception $e) {
              echo 'Silent Failure?';
            }
          }            
        }  
      }    
    } 

    // set up for rendering
    $guiObj->template = sprintf($guiObj->tcaseMgrURL,$guiObj->tcase_id,__FUNCTION__);

    if( property_exists($guiObj, 'tplan_id') ) {
      $guiObj->template .= "&tplan_id={$guiObj->tplan_id}";
    }

    if( property_exists($guiObj, 'tplan_id') ) {
      $guiObj->template .= "&tproject_id={$guiObj->tproject_id}";
    }
   
    return $guiObj;
  }

  /**
   *
   * @used by tcEdit.php
   * @use tcaseMgr->updateLatestTPlanLinkToTCV()
   */
  function updateTPlanLinkToTCV($argsObj,$request) {

    $this->tcaseMgr->updateLatestTPlanLinkToTCV($argsObj->tcversion_id,$argsObj->tplan_id);

    $opt = array('updateTPlanLinkToTCV' => true);

    $this->show($argsObj,$request, array('status_ok' => 1),$opt);
  }

  /**
   * doStepOperationExit
   *
   */
  function doStepOperationExit(&$argsObj,$request) {
    $guiObj = $this->initGuiBean($argsObj);
    $guiObj->user_feedback = '';
    $guiObj->step_exec_type = $argsObj->exec_type;
    $guiObj->tcversion_id = $argsObj->tcversion_id;

    $this->initTestCaseBasicInfo($argsObj,$guiObj);
    $guiObj->main_descr = sprintf(lang_get('create_step'), $guiObj->testcase['tc_external_id'] . ':' . 
                                  $guiObj->testcase['name'], $guiObj->testcase['version']); 
    $guiObj->cleanUpWebEditor = true;
    $this->initTestCaseBasicInfo($argsObj,$guiObj);
    
    // when working on step, refreshing tree is nonsense
    $argsObj->refreshTree = 0;

    $opt= array('updateCFOnDB' => !self::UPDATECFONDB);
    $this->show($argsObj,$request,array('status_ok' => true),$opt);
    exit();
  }

  /**
   * 
   *
   */
  function addPlatform(&$argsObj,&$request) {
    $guiObj = $this->initGuiBean($argsObj);
    $guiObj->user_feedback = '';

    $this->initTestCaseBasicInfo($argsObj,$guiObj,array('accessByStepID' => false));

    if( null != $argsObj->free_platforms ) {
      $this->tcaseMgr->addPlatforms($guiObj->tcase_id,
        $guiObj->tcversion_id, $argsObj->free_platforms);
    }

    // set up for rendering
    $guiObj->template = 
      sprintf($guiObj->tcaseMgrURL,$guiObj->tcase_id,__FUNCTION__);

    if( property_exists($guiObj, 'tplan_id') ) {
      $guiObj->template .= "&tplan_id={$guiObj->tplan_id}";
    }
   
    return $guiObj;
  }

  /**
   * 
   *
   */
  function removePlatform(&$argsObj,&$request) {
    $guiObj = $this->initGuiBean($argsObj);
    $guiObj->user_feedback = '';

    $this->initTestCaseBasicInfo($argsObj,$guiObj,array('accessByStepID' => false));

    if($argsObj->tcplat_link_id > 0) {
      $this->tcaseMgr->deletePlatformsByLink(
        $guiObj->tcase_id, $argsObj->tcplat_link_id,testcase::AUDIT_ON);
    } 

    // set up for rendering
    $guiObj->template = 
      sprintf($guiObj->tcaseMgrURL,$guiObj->tcase_id,__FUNCTION__);

    if( property_exists($guiObj, 'tplan_id') ) {
      $guiObj->template .= "&tplan_id={$guiObj->tplan_id}";
    }
    return $guiObj;
  }

  /**
   * 
   *
   */
  function addAlien(&$argsObj,&$request) 
  {
    $guiObj = $this->initGuiBean($argsObj);
    $guiObj->user_feedback = '';

    $this->initTestCaseBasicInfo($argsObj,$guiObj, ['accessByStepID' => false]);

    if (null != $argsObj->free_aliens) {
      $this->tcaseMgr->addAliens($argsObj,$argsObj->free_aliens,$argsObj->alien_relation_type);
    }

    // set up for rendering
    $guiObj->template = 
      sprintf($guiObj->tcaseMgrURL,$guiObj->tcase_id,'addAlien');

    if( property_exists($guiObj, 'tplan_id') ) {
      $guiObj->template .= "&tplan_id={$guiObj->tplan_id}";
    }
   
    return $guiObj;
  }

  /**
   * 
   *
   */
  function removeAlien(&$argsObj,&$request) {
    $guiObj = $this->initGuiBean($argsObj);
    $guiObj->user_feedback = '';

    $this->initTestCaseBasicInfo($argsObj,$guiObj,array('accessByStepID' => false));

    if($argsObj->tcalien_link_id > 0) {
      $this->tcaseMgr->deleteAliensByLink(
        $guiObj->tcase_id, $argsObj->tcalien_link_id,testcase::AUDIT_ON);
    } 

    // set up for rendering
    $guiObj->template = 
      sprintf($guiObj->tcaseMgrURL,$guiObj->tcase_id,'removeAlien');

    if( property_exists($guiObj, 'tplan_id') ) {
      $guiObj->template .= "&tplan_id={$guiObj->tplan_id}";
    }
    return $guiObj;
  }

 /**
  *  
  */ 
 function buildTemplateURLForEdit($context) {
  $tplURL = 
    "archiveData.php?" . 
    "edit=testcase&" .
    "show_mode={$context->show_mode}&" .
    "id={$context->tcase_id}&" . 
    "version_id={$context->tcversion_id}&" . 
    "tcversion_id={$context->tcversion_id}&" .
    "tproject_id={$context->tproject_id}";

    if( property_exists($context, 'tplan_id') ) {
      $tplURL .= "&tplan_id={$context->tplan_id}";
    }
  return $tplURL;
 }



} // end class  
