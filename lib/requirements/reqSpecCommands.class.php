<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource  reqSpecCommands.class.php
 * @author      Francisco Mancardi
 * 
 * @internal revisions
 * @since 1.9.15
 *
 */
class reqSpecCommands
{
  private $db;
  private $reqSpecMgr;
  private $reqMgr;
  private $treeMgr;
  private $commandMgr;
  private $defaultTemplate='reqSpecEdit.tpl';
  private $submit_button_label;
  private $auditContext;
  private $getRequirementsOptions;
  private $reqSpecTypeDomain;
  private $reqMgrSystem;

  const OVERWRITESCOPE=true;

  function __construct(&$db,$tproject_id)
  {
    $this->db=$db;
    $this->reqSpecMgr = new requirement_spec_mgr($db);
    $this->reqMgr = new requirement_mgr($db);
    $this->treeMgr = $this->reqMgr->tree_mgr;

    $req_spec_cfg = config_get('req_spec_cfg');
    $this->reqSpecTypeDomain = init_labels($req_spec_cfg->type_labels);
    $this->commandMgr = new reqCommands($db);
    $this->submit_button_label=lang_get('btn_save');
    $this->getRequirementsOptions = array('order_by' => " ORDER BY NH_REQ.node_order ");

    $tproject_mgr = new testproject($this->db);
    $info = $tproject_mgr->get_by_id($tproject_id);
    if($info['reqmgr_integration_enabled'])
    {
      $sysmgr = new tlReqMgrSystem($this->db);
      $rms = $sysmgr->getInterfaceObject($tproject_id);
      $this->reqMgrSystem = $sysmgr->getLinkedTo($tproject_id);
      unset($sysmgr);
    }
  }

  function setAuditContext($auditContext)
  {
    $this->auditContext=$auditContext;
  }

  function getReqMgrSystem()
  {
    return $this->reqMgrSystem;
  }


  /**
   * common properties needed on gui
   *
   */
  function initGuiBean($options=null)
  {
    $obj = new stdClass();
    $obj->pageTitle = '';
    $obj->bodyOnLoad = '';
    $obj->bodyOnUnload = '';
    $obj->hilite_item_name = false;
    $obj->display_path = false;
    $obj->show_match_count = false;
    $obj->main_descr = '';
    $obj->action_descr = '';
    $obj->cfields = null;
    $obj->template = '';
    $obj->submit_button_label = '';
    $obj->action_status_ok = true;
    
    $obj->req_spec_id = null;
    $obj->req_spec_revision_id = null;
    $obj->req_spec = null;
    
    $obj->expected_coverage = null;
    $obj->total_req_counter=null;
    $obj->reqSpecTypeDomain = $this->reqSpecTypeDomain;
    
    $obj->askForRevision = false;
    $obj->askForLog = false;
    $obj->req_spec = null;
    if(!is_null($options))
    {
      if(isset($options['getReqSpec']))
      {
        $ref = &$options['getReqSpec'];
        $obj->req_spec = $this->reqSpecMgr->get_by_id($ref['id'],$ref['options']);
      }
    }
    
    return $obj;
  }




  /*
    function: create

    args:
    
    returns: 

  */
  function create(&$argsObj)
  {
    // echo __CLASS__ . '.' . __FUNCTION__ . '()<br>';
    $guiObj = $this->initGuiBean(); 
    $guiObj->main_descr = lang_get('testproject') . TITLE_SEP . $argsObj->tproject_name;
    $guiObj->action_descr = lang_get('create_req_spec');

    $guiObj->cfields = $this->reqSpecMgr->html_table_of_custom_field_inputs(null,null,$argsObj->tproject_id);
    $guiObj->template = $this->defaultTemplate;
    $guiObj->submit_button_label=$this->submit_button_label;
    $guiObj->req_spec_id=null;
    $guiObj->req_spec_title=null;
    $guiObj->req_spec_doc_id=null;
    $guiObj->total_req_counter=null;

    return $guiObj;  
  }

  /*
    function: edit

    args:
    
    returns: 

  */
  // following req command model
  function edit(&$argsObj,$request,$overwriteArgs=true)
  {
    // echo __CLASS__ . '.' . __FUNCTION__ . '()<br>';

    $guiObj = $this->initGuiBean(); 

    $guiObj->req_spec = $this->reqSpecMgr->get_by_id($argsObj->req_spec_id);
    $guiObj->main_descr = lang_get('req_spec_short') . TITLE_SEP . $guiObj->req_spec['title'];

    $guiObj->req_spec_doc_id = $guiObj->req_spec['doc_id'];
    $guiObj->req_spec_title = $guiObj->req_spec['title'];
    $guiObj->total_req_counter = $guiObj->req_spec['total_req'];

    $guiObj->req_spec_id = $argsObj->req_spec_id;
    $guiObj->req_spec_revision_id = $argsObj->req_spec_revision_id;

    $guiObj->action_descr = lang_get('edit_req_spec');
    $guiObj->template = $this->defaultTemplate;
    $guiObj->submit_button_label=$this->submit_button_label;
      
    $guiObj->cfields = $this->reqSpecMgr->html_table_of_custom_field_inputs($argsObj->req_spec_id,
                                        $argsObj->req_spec_revision_id,
                                        $argsObj->tproject_id);
    
    // not really clear    
    if( $overwriteArgs )
    {
      $argsObj->scope = $guiObj->req_spec['scope'];
    }
    
    return $guiObj;  
  }

  /*
    function: doCreate

    args:
    
    returns: 

  */
  function doCreate(&$argsObj,$request)
  {
    // echo __CLASS__ . '.' . __FUNCTION__ . '()<br>';
  
    $guiObj = $this->initGuiBean(); 
    $guiObj->main_descr = lang_get('testproject') . TITLE_SEP . $argsObj->tproject_name;
    $guiObj->action_descr = lang_get('create_req_spec');
    $guiObj->submit_button_label=$this->submit_button_label;
    $guiObj->template = $this->defaultTemplate;
    $guiObj->req_spec_id=null;
    $guiObj->req_spec_doc_id=null;
    $guiObj->req_spec_title=null;
    $guiObj->total_req_counter=null;

    $guiObj->cfields = $this->reqSpecMgr->html_table_of_custom_field_inputs(null,null,$argsObj->tproject_id,
                                        $request);
    // manage new order
    $order = 0;
    $nt2exclude = array('testplan' => 'exclude_me','testsuite'=> 'exclude_me',
                        'testcase'=> 'exclude_me');
    $siblings = $this->treeMgr->get_children($argsObj->parentID,$nt2exclude);
    if( !is_null($siblings) )
    {
      $dummy = end($siblings);
      $order = $dummy['node_order']+1;
    }
      
    $ret = $this->reqSpecMgr->create($argsObj->tproject_id,$argsObj->parentID,
                                     $argsObj->doc_id,$argsObj->title,$argsObj->scope,
                                     $argsObj->countReq,$argsObj->user_id,$argsObj->reqSpecType,$order);

    $guiObj->user_feedback = $ret['msg'];
    if($ret['status_ok'])
    {
      $argsObj->scope = "";
      $guiObj->user_feedback = sprintf(lang_get('req_spec_created'),$argsObj->title);
      $idCard = array('tproject_id' => $argsObj->tproject_id);
      $cf_map = $this->reqSpecMgr->get_linked_cfields($idCard);
      
      $this->reqSpecMgr->values_to_db($request,$ret['revision_id'],$cf_map);
      logAuditEvent(TLS("audit_req_spec_created",$this->auditContext->tproject,$argsObj->title),
                    "CREATE",$ret['id'],"req_specs");
    }
    else
    {
      $guiObj->req_spec_doc_id=$argsObj->doc_id;
      $guiObj->req_spec_title=$argsObj->title;
      $guiObj->total_req_counter=$argsObj->countReq;
    }
    return $guiObj;  
  }


  /*
    function: doUpdate

    args:
    
    returns: 

  */
  function doUpdate(&$argsObj,$request)
  {
    $descr_prefix = lang_get('req_spec_short') . TITLE_SEP;

    $guiObj = $this->initGuiBean(); 
    $guiObj->submit_button_label=$this->submit_button_label;
    $guiObj->template = null;
    $guiObj->req_spec_id = $argsObj->req_spec_id;
  
    $guiObj = $this->edit($argsObj,null,!self::OVERWRITESCOPE);
    $guiObj->user_feedback = '';
    $guiObj->template = null;
    $guiObj->askForRevision = false;      
    
    // why can not do the check now ? 20110730 
    $chk = $this->reqSpecMgr->check_main_data($argsObj->title,$argsObj->doc_id,
                          $argsObj->tproject_id,$argsObj->parentID,
                          $argsObj->req_spec_id);
  
    if( $chk['status_ok'] )
    {
      $guiObj = $this->process_revision($guiObj,$argsObj,$request);
    }
    else
    {
      // need to manage things in order to NOT LOOSE user input
      $user_inputs = array('title' => array('prefix' => 'req_spec_'),
                 'scope' => array('prefix' => ''),
                 'doc_id' => array('prefix' => 'req_spec_'),
                 'reqSpecType' => array(prefix => '', 'item_key' => 'type') );

      foreach($user_inputs as $from => $convert_to)
      {
        $prefix_to = isset($convert_to['prefix_to']) ? $convert_to['prefix_to'] : '';
        $to = $prefix_to . $from;
        $guiObj->$to = $argsObj->$from;

        $item_key = isset($convert_to['item_key']) ? $convert_to['item_key'] : $from;
        $guiObj->req_spec[$item_key] = $argsObj->$from;
      }
    
      $guiObj->action_status_ok = false; 
      $guiObj->user_feedback = $chk['msg'];
      $guiObj->cfields = $this->reqSpecMgr->html_table_of_custom_field_inputs(null,null,$argsObj->tproject_id, 
                                              null, null,$request);
    }
    
    return $guiObj;  
  }


  /*
    function: doDelete

    args:
    
    returns: 

  */
  function doDelete(&$argsObj)
  {
    $guiObj = $this->initGuiBean(); 

    $req_spec = $this->reqSpecMgr->get_by_id($argsObj->req_spec_id);
    $this->reqSpecMgr->delete_deep($argsObj->req_spec_id);
    logAuditEvent(TLS("audit_req_spec_deleted",$this->auditContext->tproject,$req_spec['title']),
                   "DELETE",$argsObj->req_spec_id,"req_specs");
      
    $guiObj->template = 'show_message.tpl';
    $guiObj->template_dir = '';
        $guiObj->main_descr = lang_get('testproject') . TITLE_SEP . $argsObj->tproject_name;
    $guiObj->title=lang_get('delete_req_spec');

    $guiObj->user_feedback = sprintf(lang_get('req_spec_deleted'),$req_spec['title']);
    $guiObj->refreshTree = 1; // needed to enable refresh_tree logic
    $guiObj->result = 'ok';  
        
    return $guiObj;  
  }
  
  
  /*
    function: reorder

    args:
    
    returns: 

  */
  function reorder(&$argsObj)
  {
    $guiObj = $this->initGuiBean(); 
    $guiObj->template = 'reqSpecReorder.tpl';
    $guiObj->main_descr = lang_get('testproject') . TITLE_SEP . $argsObj->tproject_name;
    $guiObj->action_descr = lang_get('title_change_req_spec_order');

    $order_by = ' ORDER BY NH.node_order,REQ_SPEC.id ';
    $guiObj->all_req_spec = $this->reqSpecMgr->get_all_in_testproject($argsObj->tproject_id,$order_by);
    $guiObj->tproject_name=$argsObj->tproject_name;
    $guiObj->tproject_id=$argsObj->tproject_id;
    return $guiObj;
  }



  /*
    function: doReorder

    args:
    
    returns: 

  */
  function doReorder(&$argsObj)
  {
    $guiObj = $this->initGuiBean(); 
    $guiObj->tproject_name=$argsObj->tproject_name;
    $guiObj->tproject_id=$argsObj->tproject_id;
    $guiObj->template = 'project_req_spec_mgmt.tpl';
    $guiObj->main_descr = lang_get('testproject') . TITLE_SEP . $argsObj->tproject_name;
      
    $nodes_in_order = transform_nodes_order($argsObj->nodes_order);

    // need to remove first element, is testproject
    array_shift($nodes_in_order);
    $this->reqSpecMgr->set_order($nodes_in_order);
    $guiObj->refreshTree=1;
    return $guiObj;
  }


  /*
    function: createChild

    args:
    
    returns: 

  */
  function createChild(&$argsObj)
  {
    $reqParent=$this->reqSpecMgr->get_by_id($argsObj->parentID);
    $guiObj = $this->initGuiBean(); 
    $guiObj->main_descr = lang_get('req_spec_short') . TITLE_SEP . $reqParent['title'];
    $guiObj->action_descr = lang_get('create_child_req_spec');

    $guiObj->cfields = $this->reqSpecMgr->html_table_of_custom_field_inputs(null,null,$argsObj->tproject_id);
    $guiObj->template = $this->defaultTemplate;
    $guiObj->submit_button_label=$this->submit_button_label;
    $guiObj->req_spec_id=null;
    $guiObj->req_spec_doc_id=null;
    $guiObj->req_spec_title=null;
    $guiObj->total_req_counter=null;

    return $guiObj;  
  }


  /*
    function: copyRequirements

    args:
    
    returns: 

  */
  function copyRequirements(&$argsObj,$options=null)
  {
    $obj = $this->initGuiBean(); 
    $req_spec = $this->reqSpecMgr->get_by_id($argsObj->req_spec_id);
    
    $my['options'] = array( 'get_items' => true);
    $my['options'] = array_merge($my['options'], (array)$options);
    if( $my['options']['get_items'] )
    {
      $opt = $this->getRequirementsOptions + array('output' => 'minimal');
      $obj->items = $this->reqSpecMgr->get_requirements($argsObj->req_spec_id,'all',null,$opt);
    }
    $obj->main_descr = lang_get('req_spec') . TITLE_SEP . $req_spec['title'];
    $obj->action_descr = lang_get('copy_several_reqs');
    $obj->template = 'reqCopy.tpl';
    $obj->containers = null;
    $obj->page2call = 'lib/requirements/reqSpecEdit.php';
    $obj->array_of_msg = '';
    $obj->doActionButton = 'doCopyRequirements';
    $obj->req_spec_id = $argsObj->req_spec_id;
  
    $exclude_node_types=array('testplan' => 'exclude_me','testsuite' => 'exclude_me',
                              'testcase' => 'exclude_me','requirement' => 'exclude_me',
                              'requirement_spec_revision' => 'exclude_me');
        
    $my['filters'] = array('exclude_node_types' => $exclude_node_types);
    $my['options']['order_cfg']['type'] = $my['options']['output'] = 'rspec';
    $subtree = $this->reqMgr->tree_mgr->get_subtree($argsObj->tproject_id,$my['filters'],$my['options']);
    if(count($subtree))
    {
      $obj->containers = $this->reqMgr->tree_mgr->createHierarchyMap($subtree,'dotted',array('field' => 'doc_id','format' => '%s:'));
    }
    return $obj;
  }

  /**
   * 
   *
   */
  function doCopyRequirements(&$argsObj)
  {
    $obj = $this->initGuiBean(); 
    $obj = $this->copyRequirements($argsObj, array( 'get_items' => false));
    $obj->req = null;
    $obj->req_spec_id = $argsObj->req_spec_id;
    $obj->array_of_msg = '';
       
    $copyOptions = array('copy_also' => array('testcase_assignment' => $argsObj->copy_testcase_assignment));
        
    foreach($argsObj->itemSet as $itemID)
    {
      $ret = $this->reqMgr->copy_to($itemID,$argsObj->containerID,$argsObj->user_id,
                                    $argsObj->tproject_id,$copyOptions);
      $obj->user_feedback = $ret['msg'];
      if($ret['status_ok'])
      {
        $new_req = $this->reqMgr->get_by_id($ret['id'],requirement_mgr::LATEST_VERSION);
        $source_req = $this->reqMgr->get_by_id($itemID,requirement_mgr::LATEST_VERSION);
        $new_req = $new_req[0];
        $source_req = $source_req[0];

        $logMsg = TLS("audit_requirement_copy",$new_req['req_doc_id'],$source_req['req_doc_id']);
        logAuditEvent($logMsg,"COPY",$ret['id'],"requirements");
        $obj->user_feedback = $logMsg; // sprintf(lang_get('req_created'), $new_req['req_doc_id']);
        $obj->template = 'reqCopy.tpl';
        $obj->req_id = $ret['id'];
        $obj->array_of_msg[] = $logMsg;  
      }
    }
    $obj->items = $this->reqSpecMgr->get_requirements($obj->req_spec_id,
                                                      'all',null,$this->getRequirementsOptions);
    
    return $obj;  
  }


  /*
    function: copy
              copy req. spec

    args:
    
    returns: 

  */
  function copy(&$argsObj,$options=null)
  {
    $obj = $this->initGuiBean(); 
    $req_spec = $this->reqSpecMgr->get_by_id($argsObj->req_spec_id);
    
    $my['options'] = array( 'get_items' => true);
    $my['options'] = array_merge($my['options'], (array)$options);

    $obj->main_descr = lang_get('req_spec') . TITLE_SEP . $req_spec['title'];
    $obj->action_descr = lang_get('copy_req_spec');
    $obj->template = 'reqSpecCopy.tpl';
    $obj->containers = null;
    $obj->page2call = 'lib/requirements/reqSpecEdit.php';
    $obj->array_of_msg = '';
    $obj->doActionButton = 'doCopy';
    $obj->req_spec_id = $argsObj->req_spec_id;
    $obj->top_checked = ' checked = "checked" ';
    $obj->bottom_checked = ' ';
  
  
    $exclude_node_types=array('testplan' => 'exclude_me','testsuite' => 'exclude_me',
                              'testcase'=> 'exclude_me','requirement' => 'exclude_me');
        
    $my['filters'] = array('exclude_node_types' => $exclude_node_types);
    $root = $this->treeMgr->get_node_hierarchy_info($argsObj->tproject_id);
    $subtree = array_merge(array($root),$this->treeMgr->get_subtree($argsObj->tproject_id,$my['filters']));

    if(count($subtree))
    {
      $obj->containers = $this->treeMgr->createHierarchyMap($subtree);
    }
    return $obj;
  }



  /*
    function: doCopy
              copy req. spec

    args:
    
    returns: 

  */
  function doCopy(&$argsObj)
  {
    $obj = $this->initGuiBean(); 
    $obj = $this->copy($argsObj);
    $obj->req = null;
    $obj->req_spec_id = $argsObj->req_spec_id;
    $req_spec = $this->reqSpecMgr->get_by_id($argsObj->req_spec_id);
    
    
    $obj->main_descr = lang_get('req_spec') . TITLE_SEP . $req_spec['title'];
    $obj->action_descr = lang_get('copy_req_spec');
    $obj->template = 'reqSpecCopy.tpl';
    $obj->containers = null;
    $obj->page2call = 'lib/requirements/reqSpecEdit.php';
    $obj->array_of_msg = '';
    $obj->doActionButton = 'doCopy';
    $obj->req_spec_id = $argsObj->req_spec_id;
    $obj->top_checked = ' checked = "checked" ';
    $obj->bottom_checked = ' ';
  
    $op = $this->reqSpecMgr->copy_to($argsObj->req_spec_id,$argsObj->containerID, 
                                     $argsObj->tproject_id, $argsObj->user_id);

    if( $op['status_ok'] )
    {
      $new_req_spec = $this->reqSpecMgr->get_by_id($op['id']);
      $obj->array_of_msg[] = sprintf(lang_get('req_spec_copy_done'),$req_spec['doc_id'],
                                     $req_spec['title'],$new_req_spec['doc_id']);
    }
        
    $exclude_node_types=array('testplan' => 'exclude_me','testsuite' => 'exclude_me',
                              'testcase'=> 'exclude_me','requirement' => 'exclude_me');
        
    $my['filters'] = array('exclude_node_types' => $exclude_node_types);
    $root = $this->treeMgr->get_node_hierarchy_info($argsObj->tproject_id);
    $subtree = array_merge(array($root),$this->treeMgr->get_subtree($argsObj->tproject_id,$my['filters']));

    if(count($subtree))
    {
      $obj->containers = $this->treeMgr->createHierarchyMap($subtree);
    }
    return $obj;
  }
  
  /**
   *
   */
  public function doFreeze(&$argsObj,$request) 
  {
    $req_spec_id = $request["req_spec_id"];    
    $req_spec = $this->reqSpecMgr->getReqTree($req_spec_id);
    $req_spec_info = $this->reqSpecMgr->get_by_id($req_spec_id);
    
    $childNodes = isset($req_spec['childNodes']) ? $req_spec['childNodes'] : null ;
    if( !is_null($childNodes)) 
    {
      $loop_qty=sizeof($childNodes); 
      for($idx = 0;$idx < $loop_qty;$idx++) 
      {
        $cNode = $childNodes[$idx];
        $nTable = $cNode['node_table'];
        if($cNode['node_table'] == 'req_specs') 
        {
          $request["req_spec_id"]=$cNode['id'];
          $this->doFreeze($argsObj,$request);
        }
        else if ($cNode['node_table'] == 'requirements') 
        {
          $req = $this->reqMgr->get_by_id($cNode['id'],requirement_mgr::LATEST_VERSION);
          $req_freeze_version = new stdClass();
          $req_freeze_version->req_version_id = $req[0]['version_id'];
          $this->commandMgr->doFreezeVersion($req_freeze_version);
        }
      }
    }  
    
    $obj = $this->initGuiBean(); 
    $obj->template = 'show_message.tpl';
    $obj->template_dir = '';
    $obj->user_feedback = lang_get('req_frozen');
    $obj->main_descr=lang_get('req_spec') . TITLE_SEP . $req_spec_info['title'];
    $obj->title=lang_get('freeze_req');
    $obj->refreshTree = 0;
    $obj->result = 'ok';  // needed to enable refresh_tree logic
    return $obj;
  }


  /**
   * THIS METHOD NEED to be moved to common class because is also used
   * on reqCommand.class.php
   *
   */
  function simpleCompare($old,$new,$oldCF,$newCF)
  {
    // - log message is only forced to be entered when a custom field, title or document ID is changed
    // - when only changes where made to scope user is free to create a new revision or 
    //   overwrite the old revision (Cancel -> overwrite)
    $ret = array('force' =>  false, 'suggest' => false, 'nochange' => false, 'changeon' => null);
  
    // key: var name to be used on $old
    // value: var name to be used on $new
    // Then to compare old and new
    // $old[$key] compare to $new[$value]
    //
    $suggest_revision = array('scope' => 'scope'); 
    $force_revision = array('type' => 'reqSpecType', 'doc_id'=> 'doc_id', 'title' => 'title');

    foreach($force_revision as $access_key => $access_prop)
    {
      if( $ret['force'] = ($old[$access_key] != $new->$access_prop) )
      {
        $ret['changeon'] = 'attribute:' . $access_key;
        break;
      }
    }
    
    if( !$ret['force'] )
    {
      if( !is_null($newCF) )
      {
        foreach($newCF as $cf_key => $cf)
        {
          if( $ret['force'] = ($oldCF[$cf_key]['value'] != $cf['cf_value']) )
          {
            $ret['changeon'] = 'custom field:' . $oldCF[$cf_key]['name'];
            break;
          }
        }
      }    
    }
    
    if( !$ret['force'] )
    {
      foreach($suggest_revision as $access_key => $access_prop)
      {
        if( $ret['suggest'] = ($old[$access_key] != $new->$access_prop) )
        {
          $ret['changeon'] = 'attribute:' . $access_key;
          break;
        }
      }
    
    }
    $ret['nochange'] = ($ret['force'] == false && $ret['suggest'] == false);
    return $ret;
  }

  /*
    function: doCreateRevision

    args:
    
    returns: 

     @internal revisions

  */
  function doCreateRevision(&$argsObj,$request)
  {
    $item = array('log_message' => $argsObj->log_message, 'author_id' => $argsObj->user_id);    
    $ret = $this->reqSpecMgr->clone_revision($argsObj->req_spec_id,$item);
    
    $obj = $this->initGuiBean();
    $obj->user_feedback = $ret['msg'];
    $obj->template = "reqSpecView.php?req_spec_id={$argsObj->req_spec_id}";
    $obj->req_spec = null;
    $obj->req_spec_id=$argsObj->req_spec_id;
    $obj->req_spec_revision_id = $ret['id'];
    return $obj;  
  }



  /**
   *
   */
  function process_revision(&$guiObj,&$argsObj,&$userInput)
  {
  
    // TICKET 4661
    $itemOnDB = $this->reqSpecMgr->get_by_id($argsObj->req_spec_id);
    $who = array('tproject_id' => $argsObj->tproject_id);
    $cf_map = $this->reqSpecMgr->get_linked_cfields($who);
    $newCFields = $this->reqSpecMgr->cfield_mgr->_build_cfield($userInput,$cf_map);

    $who['item_id'] = $argsObj->req_spec_revision_id;
    $oldCFields = $this->reqSpecMgr->get_linked_cfields($who);
    $diff = $this->simpleCompare($itemOnDB,$argsObj,$oldCFields,$newCFields);
  
  
    $createRev = false;
    if($diff['force'] && !$argsObj->do_save)
    {
      $guiObj->askForLog = true;
      $guiObj->refreshTree = false;
      
      // Need Change several values with user input data, to match logic on 
      // edit php page on function renderGui()
      // $map = array('status' => 'reqStatus', 'type' => 'reqSpecType','scope' => 'scope',
      $map = array('type' => 'reqSpecType','scope' => 'scope',
                   'doc_id'=> 'doc_id', 'title' => 'title');

      foreach($map as $k => $w)
      {
        $guiObj->req_spec[$k] = $argsObj->$w;
      }
      $guiObj->cfields = $this->reqSpecMgr->html_table_of_custom_field_inputs(null,null,$argsObj->tproject_id, 
                                              null, null,$userInput);

    }
    else if( $diff['nochange'] || ( ($createRev = $diff['force'] && !$guiObj->askForLog) || $argsObj->do_save ) )
    {
        
      if( $argsObj->do_save == 1)
      {
        $createRev = ($argsObj->save_rev == 1);
      }
      
      $item = array();
      $item['id'] = $argsObj->req_spec_id;
      $item['revision_id'] = $createRev ? -1 : $argsObj->req_spec_revision_id;
      $item['doc_id'] = $argsObj->doc_id;
      $item['name'] = $argsObj->title;
      $item['scope'] = $argsObj->scope;
      $item['countReq'] = $argsObj->countReq;
      $item['type'] = $argsObj->reqSpecType;
    
      $user_key = $createRev ? 'author_id' : 'modifier_id';
      $item[$user_key] = $argsObj->user_id;
      
      $opt = array('skip_controls' => true, 'create_rev' => $createRev, 'log_message' => $argsObj->log_message);                                 
      $ret = $this->reqSpecMgr->update($item,$opt);
    
      $guiObj->user_feedback = $ret['msg'];
      $guiObj->template = null;
        
      if($ret['status_ok'])
      {
        // custom fields update
        $this->reqSpecMgr->values_to_db($userInput,$ret['revision_id'],$cf_map);

        $guiObj->main_descr = '';
        $guiObj->action_descr = '';
        $guiObj->template = "reqSpecView.php?refreshTree={$argsObj->refreshTree}&" .
                            "req_spec_id={$guiObj->req_spec_id}";
  
        // TODO 
        // logAuditEvent(TLS("audit_requirement_saved",$argsObj->reqDocId),"SAVE",$argsObj->req_id,"requirements");
      }
      else
      {
        // Action has failed => no change done on DB.
        $old = $this->reqSpecMgr->get_by_id($argsObj->req_id);
        $guiObj->main_descr = $descr_prefix . $old['title'];
        $guiObj->cfields = $this->reqSpecMgr->html_table_of_custom_field_values($argsObj->req_spec_id,
                                                $argsObj->req_spec_revision_id,
                                                $argsObj->tproject_id);
      }
    }
    else if( $diff['suggest'] )
    {
      $guiObj->askForRevision = true;      
    }

    return $guiObj;
  }


  /**
   *
   */
  function fileUpload(&$argsObj,$request)
  {
    fileUploadManagement($this->db,$argsObj->req_spec_id,$argsObj->fileTitle,$this->reqSpecMgr->getAttachmentTableName());
    return $this->initGuiObjForAttachmentOperations($argsObj);
  }

  /**
   *
   */
  function deleteFile(&$argsObj)
  {
    deleteAttachment($this->db,$argsObj->file_id);
    return $this->initGuiObjForAttachmentOperations($argsObj);
  }


  /**
   *
   */
  private function initGuiObjForAttachmentOperations($argsObj)
  {
    $guiObj = new stdClass();
    $guiObj->main_descr = '';
    $guiObj->action_descr = '';
    $guiObj->askForRevision = $guiObj->askForLog = false;
    $guiObj->action_status_ok = true;
    $guiObj->req_spec_id = $argsObj->req_spec_id;
    $guiObj->template = "reqSpecView.php?refreshTree=0&req_spec_id={$argsObj->req_spec_id}";
    return $guiObj;    
  }

  /*
    function: copyRequirements

    args:
    
    returns: 

  */
  function bulkReqMon(&$argsObj,$options=null)
  {
    $obj = $this->initGuiBean(); 
    $req_spec = $this->reqSpecMgr->get_by_id($argsObj->req_spec_id);
    
    $my['options'] = array( 'get_items' => true);
    $my['options'] = array_merge($my['options'], (array)$options);


    if( $my['options']['get_items'] )
    {
      $opt = $this->getRequirementsOptions + 
             array('outputLevel' => 'minimal', 'decodeUsers' => false);
      $obj->items = $this->reqSpecMgr->get_requirements($argsObj->req_spec_id,'all',null,$opt);    
    }

    $opx = array('reqSpecID' => $argsObj->req_spec_id);
    $monSet = $this->reqMgr->getMonitoredByUser($argsObj->user_id,$argsObj->tproject_id,$opx);
    
    $obj->enable_start_btn = false;
    $obj->enable_stop_btn = false;
    foreach($obj->items as $xdx => &$itx)
    {
      $onOff = isset($monSet[$itx['id']]) ? true : false;
      $itx['monitor'] = $onOff ? 'On' : 'Off';
      $obj->enable_start_btn |= !$onOff;
      $obj->enable_stop_btn |= $onOff;
    }  

    $obj->main_descr = lang_get('req_spec') . TITLE_SEP . $req_spec['title'];
    $obj->action_descr = lang_get('bulk_monitoring');
    $obj->template = 'reqBulkMon.tpl';
    $obj->containers = null;
    $obj->page2call = 'lib/requirements/reqSpecEdit.php';
    $obj->array_of_msg = '';
    $obj->doActionButton = 'do' . ucfirst(__FUNCTION__);
    $obj->req_spec_id = $argsObj->req_spec_id;
    $obj->refreshTree = 0;
    
    return $obj;
  }

 /**
   * 
   *
   */
  function doBulkReqMon(&$argsObj)
  {
    $obj = $this->initGuiBean(); 
    $obj->req = null;
    $obj->req_spec_id = $argsObj->req_spec_id;
    $obj->array_of_msg = '';
  
    $m2r = null;
    switch($argsObj->op)
    {
      case 'toogleMon':
        $opx = array('reqSpecID' => $argsObj->req_spec_id);
        $monSet = $this->reqMgr->getMonitoredByUser($argsObj->user_id,$argsObj->tproject_id,$opx);

        foreach($argsObj->itemSet as $req_id)
        {
          $f2r = isset($monSet[$req_id]) ? 'monitorOff' : 'monitorOn';
          $this->reqMgr->$f2r($req_id,$argsObj->user_id,$argsObj->tproject_id);
        }  
      break;

      case 'startMon':
        $m2r = 'monitorOn';
      break;

      case 'stopMon':
        $m2r = 'monitorOff';
      break;
    }     

    if( !is_null($m2r) )
    {
      foreach($argsObj->itemSet as $req_id)
      {
        $this->reqMgr->$m2r($req_id,$argsObj->user_id,$argsObj->tproject_id);
      }  
    } 

    return $this->bulkReqMon($argsObj);
  }



}
