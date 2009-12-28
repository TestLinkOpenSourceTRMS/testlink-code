<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: reqSpecCommands.class.php,v $
 * @version $Revision: 1.17 $
 * @modified $Date: 2009/12/28 17:35:18 $ by $Author: franciscom $
 * @author Francisco Mancardi
 * web command experiment
 *
 * 
 *	@internal revisions
 *	20091223 - franciscom - new feature copy requirements
 *	20091207 - franciscom - logic to get order when creating new item 
 *	20090324 - franciscom - added logic to avoid losing user work if title already exists.
 *                            - fixed minor errors due to missing variables
 */

class reqSpecCommands
{
	private $db;
	private $reqSpecMgr;
	private $reqMgr;
	private $reqStatus;
	private $defaultTemplate='reqSpecEdit.tpl';
	private $submit_button_label;
	private $auditContext;
    private $getRequirementsOptions;

	function __construct(&$db)
	{
	    $this->db=$db;
	    $this->reqSpecMgr = new requirement_spec_mgr($db);
	    $this->reqMgr = new requirement_mgr($db);
	    $this->reqStatus=init_labels(config_get('req_status'));
		$this->submit_button_label=lang_get('btn_save');
		$this->getRequirementsOptions = array('order_by' => " ORDER BY NH_REQ.node_order ");
		
	}

	function setAuditContext($auditContext)
	{
	    $this->auditContext=$auditContext;
	}


  /*
    function: create

    args:
    
    returns: 

  */
	function create(&$argsObj)
	{
      	$guiObj=new stdClass();
      	$guiObj->main_descr = lang_get('testproject') . TITLE_SEP . $argsObj->tproject_name;
      	$guiObj->action_descr = lang_get('create_req_spec');

		$guiObj->cfields = $this->reqSpecMgr->html_table_of_custom_field_inputs(null,$argsObj->tproject_id);
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
	function edit(&$argsObj)
	{
		$guiObj=new stdClass();

		$guiObj->req_spec = $this->reqSpecMgr->get_by_id($argsObj->req_spec_id);
		$guiObj->main_descr = lang_get('req_spec_short') . TITLE_SEP . $guiObj->req_spec['title'];
		$guiObj->action_descr = lang_get('edit_req_spec');
      	$guiObj->template = $this->defaultTemplate;
		$guiObj->submit_button_label=$this->submit_button_label;
      
		$guiObj->req_spec_id=$argsObj->req_spec_id;
		$guiObj->req_spec_doc_id=$guiObj->req_spec['doc_id'];
		$guiObj->req_spec_title=$guiObj->req_spec['title'];
		$guiObj->total_req_counter=$guiObj->req_spec['total_req'];
		$guiObj->cfields = $this->reqSpecMgr->html_table_of_custom_field_inputs($argsObj->req_spec_id,$argsObj->tproject_id);
		
		$argsObj->scope = $guiObj->req_spec['scope'];
      	return $guiObj;	
	}

  /*
    function: doCreate

    args:
    
    returns: 

  */
	function doCreate(&$argsObj,$request)
	{
		$guiObj=new stdClass();

		$guiObj->main_descr = lang_get('testproject') . TITLE_SEP . $argsObj->tproject_name;
		$guiObj->action_descr = lang_get('create_req_spec');
		$guiObj->submit_button_label=$this->submit_button_label;
      	$guiObj->template = $this->defaultTemplate;
      	$guiObj->req_spec_id=null;
      	$guiObj->req_spec_doc_id=null;
		$guiObj->req_spec_title=null;
		$guiObj->total_req_counter=null;

		$guiObj->cfields = $this->reqSpecMgr->html_table_of_custom_field_inputs(null,$argsObj->tproject_id);
		
		// manage new order
		$order = 0;
    	$nt2exclude = array('testplan' => 'exclude_me','testsuite'=> 'exclude_me',
    	                    'testcase'=> 'exclude_me');
    	$siblings = $this->reqSpecMgr->tree_mgr->get_children($argsObj->reqParentID,$nt2exclude);
    	if( !is_null($siblings) )
    	{
    		$dummy = end($siblings);
    		$order = $dummy['node_order']+1;
    	}
		$ret = $this->reqSpecMgr->create($argsObj->tproject_id,$argsObj->reqParentID,
		                                 $argsObj->doc_id,$argsObj->title,$argsObj->scope,
		                                 $argsObj->countReq,$argsObj->user_id,'n',$order);

		$guiObj->user_feedback = $ret['msg'];
		if($ret['status_ok'])
		{
		  	$argsObj->scope = "";
			$guiObj->user_feedback = sprintf(lang_get('req_spec_created'),$argsObj->title);
			$cf_map = $this->reqSpecMgr->get_linked_cfields(null,$argsObj->tproject_id) ;
			$this->reqSpecMgr->values_to_db($request,$ret['id'],$cf_map);
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

		$guiObj=new stdClass();
 		$guiObj->submit_button_label=$this->submit_button_label;
	    $guiObj->template = null;
		$guiObj->req_spec_id = $argsObj->req_spec_id;

		$guiObj->req_spec = $this->reqSpecMgr->get_by_id($argsObj->req_spec_id);

		$ret = $this->reqSpecMgr->update($argsObj->req_spec_id,$argsObj->doc_id,$argsObj->title,
		                                 $argsObj->scope,$argsObj->countReq,$argsObj->user_id);
		$guiObj->user_feedback = $ret['msg'];
        
		if($ret['status_ok'])
		{
			$guiObj->main_descr = '';
			$guiObj->action_descr='';
			$guiObj->template = "reqSpecView.php?req_spec_id={$guiObj->req_spec_id}";
			$cf_map = $this->reqSpecMgr->get_linked_cfields($argsObj->req_spec_id);
			$this->reqSpecMgr->values_to_db($request,$argsObj->req_spec_id,$cf_map);
		
			if( $argsObj->title == $guiObj->req_spec['title'] )
			{
			    $audit_msg= TLS("audit_req_spec_saved",$this->auditContext->tproject,$argsObj->title);
			}    
			else
			{
			    $audit_msg= TLS("audit_req_spec_renamed",$this->auditContext->tproject,
			                                             $guiObj->req_spec['title'],$argsObj->title);
			}
			logAuditEvent($audit_msg,"SAVE",$argsObj->req_spec_id,"req_specs");
		}
		else
		{
		   // Action has failed => no change done on DB.
		   $guiObj->main_descr = $descr_prefix . $guiObj->req_spec['title'];
		   $guiObj->action_descr = lang_get('edit_req_spec');
		   $guiObj->cfields = $this->reqSpecMgr->html_table_of_custom_field_values($argsObj->req_spec_id,$argsObj->tproject_id);

           // Do not loose user's input
		   $guiObj->req_spec_doc_id=$argsObj->doc_id;
		   $guiObj->req_spec_title=$argsObj->title;
		   $guiObj->total_req_counter=$argsObj->countReq;
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
		$guiObj = new stdClass();

		$req_spec = $this->reqSpecMgr->get_by_id($argsObj->req_spec_id);
		$this->reqSpecMgr->delete_deep($argsObj->req_spec_id);
		logAuditEvent(TLS("audit_req_spec_deleted",$this->auditContext->tproject,$req_spec['title']),
		               "DELETE",$argsObj->req_spec_id,"req_specs");
		  
		$guiObj->template = 'show_message.tpl';
		$guiObj->template_dir = '';
      	$guiObj->main_descr = lang_get('testproject') . TITLE_SEP . $argsObj->tproject_name;
		$guiObj->title=lang_get('delete_req_spec');

		$guiObj->user_feedback = sprintf(lang_get('req_spec_deleted'),$req_spec['title']);
		$guiObj->refresh_tree = 'yes'; // needed to enable refresh_tree logic
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
		$guiObj=new stdClass();
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
      	$guiObj=new stdClass();
      	$guiObj->tproject_name=$argsObj->tproject_name;
      	$guiObj->tproject_id=$argsObj->tproject_id;
  		$guiObj->template = 'project_req_spec_mgmt.tpl';
  		$guiObj->main_descr = lang_get('testproject') . TITLE_SEP . $argsObj->tproject_name;
  		
		$nodes_in_order = transform_nodes_order($argsObj->nodes_order);

		// need to remove first element, is testproject
		array_shift($nodes_in_order);
		$this->reqSpecMgr->set_order($nodes_in_order);
      	$guiObj->refresh_tree='yes';
	    return $guiObj;
  }


  /*
    function: createChild

    args:
    
    returns: 

  */
	function createChild(&$argsObj)
	{
		$reqParent=$this->reqSpecMgr->get_by_id($argsObj->reqParentID);
		$guiObj=new stdClass();
		$guiObj->main_descr = lang_get('req_spec_short') . TITLE_SEP . $reqParent['title'];
		$guiObj->action_descr = lang_get('create_child_req_spec');

		$guiObj->cfields = $this->reqSpecMgr->html_table_of_custom_field_inputs(null,$argsObj->tproject_id);
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
   		$obj = new stdClass();
		$req_spec = $this->reqSpecMgr->get_by_id($argsObj->req_spec_id);
		
	    $my['options'] = array( 'get_items' => true);
	    $my['options'] = array_merge($my['options'], (array)$options);
	    if( $my['options']['get_items'] )
	    {
	    	$obj->items = $this->reqSpecMgr->get_requirements($argsObj->req_spec_id,'all',null,
	    	                                                  $this->getRequirementsOptions);
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
	                              'testcase'=> 'exclude_me','requirement' => 'exclude_me');
        
 		$my['filters'] = array('exclude_node_types' => $exclude_node_types);
	  	$subtree = $this->reqMgr->tree_mgr->get_subtree($argsObj->tproject_id,$my['filters']);
 		if(count($subtree))
		{
		  $obj->containers = $this->reqMgr->tree_mgr->createHierarchyMap($subtree);
        }
		return $obj;
	}

    /**
     * 
     *
     */
	function doCopyRequirements(&$argsObj)
	{
		// $target_req_spec = $this->reqSpecMgr->get_by_id($argsObj->containerID);
		$obj = new stdClass();
 		$obj = $this->copyRequirements($argsObj, array( 'get_items' => false));
      	$obj->req = null;
   		$obj->req_spec_id = $argsObj->req_spec_id;
     	
      	$copyOptions = array('copy_also' => 
      	                     array('testcase_assignment' => $argsObj->copy_testcase_assignment));
      	
    	$obj->array_of_msg = '';
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
   		$obj = new stdClass();
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
	  	$root = $this->reqMgr->tree_mgr->get_node_hierachy_info($argsObj->tproject_id);
	  	$subtree = array_merge(array($root),$this->reqMgr->tree_mgr->get_subtree($argsObj->tproject_id,$my['filters']));

 		if(count($subtree))
		{
		  $obj->containers = $this->reqMgr->tree_mgr->createHierarchyMap($subtree);
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
		$obj = new stdClass();
 		$obj = $this->copy($argsObj);
      	$obj->req = null;
   		$obj->req_spec_id = $argsObj->req_spec_id;
  	    $req_spec = $this->reqSpecMgr->get_by_id($argsObj->req_spec_id);
		
		
	    // $my['options'] = array( 'get_items' => true);
	    // $my['options'] = array_merge($my['options'], (array)$options);

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
	  	$root = $this->reqSpecMgr->tree_mgr->get_node_hierachy_info($argsObj->tproject_id);
	  	$subtree = array_merge(array($root),$this->reqMgr->tree_mgr->get_subtree($argsObj->tproject_id,$my['filters']));

 		if(count($subtree))
		{
		  $obj->containers = $this->reqMgr->tree_mgr->createHierarchyMap($subtree);
        }
		return $obj;
	}




}
?>