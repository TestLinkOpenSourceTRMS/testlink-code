<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: reqCommands.class.php,v $
 * @version $Revision: 1.15 $
 * @modified $Date: 2009/12/07 18:14:39 $ by $Author: franciscom $
 * @author Francisco Mancardi
 * 
 * web command experiment
 * rev: 20081213 - franciscom - fixed minor bug on doCreate()
 */

class reqCommands
{
  private $db;
  private $reqSpecMgr;
  private $reqMgr;
  private $reqStatusDomain;

	function __construct(&$db)
	{
	    $this->db=$db;
	    $this->reqSpecMgr = new requirement_spec_mgr($db);
	    $this->reqMgr = new requirement_mgr($db);
	    $this->reqStatusDomain=init_labels(config_get('req_status'));
	}

  /*
    function: create

    args:
    
    returns: 

  */
	function create(&$argsObj)
	{
		$obj = new stdClass();
		$req_spec = $this->reqSpecMgr->get_by_id($argsObj->req_spec_id);
        
		$obj->main_descr = lang_get('req_spec_short') . TITLE_SEP . $req_spec['title'];
		$obj->action_descr = lang_get('create_req');
		$obj->cfields = $this->reqMgr->html_table_of_custom_field_inputs(null,$argsObj->tproject_id);
      	$obj->template = 'reqEdit.tpl';
		$obj->submit_button_label = lang_get('btn_save');
		$obj->reqStatusDomain = $this->reqStatusDomain;
		$obj->req_spec_id = $argsObj->req_spec_id;
		$obj->req_id = null;
		$obj->req = null;
		$obj->expected_coverage = null;
 		return $obj;	
	}

  /*
    function: edit

    args:
    
    returns: 

  */
	function edit(&$argsObj)
	{
		$obj = new stdClass();
		$obj->req = $this->reqMgr->get_by_id($argsObj->req_id);
		$argsObj->scope = $obj->req['scope'];
	
		$obj->main_descr = lang_get('req') . TITLE_SEP . $obj->req['title'];
		$obj->action_descr = lang_get('edit_req');
		$obj->cfields = $this->reqMgr->html_table_of_custom_field_inputs($argsObj->req_id,$argsObj->tproject_id);
		$obj->template = 'reqEdit.tpl';
		$obj->submit_button_label = lang_get('btn_save');
	  	$obj->reqStatusDomain = $this->reqStatusDomain;
		$obj->req_spec_id = $argsObj->req_spec_id;
		$obj->req_id = $argsObj->req_id;
		$obj->expected_coverage = $argsObj->expected_coverage;
		
		return $obj;	
	}

  /*
    function: doCreate

    args:
    
    returns: 

  */
	function doCreate(&$argsObj,$request)
	{
		$req_spec = $this->reqSpecMgr->get_by_id($argsObj->req_spec_id);
		$obj=new stdClass();
      	$obj->req = null;
		$obj->main_descr = lang_get('req_spec_short') . TITLE_SEP . $req_spec['title'];
		$obj->action_descr = lang_get('create_req');
		$obj->cfields = $this->reqMgr->html_table_of_custom_field_inputs(null,$argsObj->tproject_id);
		$obj->submit_button_label=lang_get('btn_save');
		$obj->template = null;
      	$obj->reqStatusDomain=$this->reqStatusDomain;
 		$obj->req_spec_id = $argsObj->req_spec_id;
	    $obj->expected_coverage = $argsObj->expected_coverage;
	
			// manage new order
		$order = 0;
    	$nt2exclude = array('testplan' => 'exclude_me','testsuite'=> 'exclude_me',
    	                    'testcase'=> 'exclude_me');
    	$siblings = $this->reqMgr->tree_mgr->get_children($argsObj->req_spec_id,$nt2exclude);
    	if( !is_null($siblings) )
    	{
    		$dummy = end($siblings);
    		$order = $dummy['node_order']+1;
    	}
	    // 20090719 - franciscom - seems that TYPE has been removed from user interface
		$ret = $this->reqMgr->create($argsObj->req_spec_id,$argsObj->reqDocId,$argsObj->title,
		                             $argsObj->scope,$argsObj->user_id,$argsObj->reqStatus,
		                             $argsObj->reqType,$argsObj->expected_coverage,$order);

		$obj->user_feedback = $ret['msg'];
		if($ret['status_ok'])
		{
			logAuditEvent(TLS("audit_requirement_created",$argsObj->reqDocId),"CREATE",$ret['id'],"requirements");
			$obj->user_feedback = sprintf(lang_get('req_created'), $argsObj->reqDocId);
			$cf_map = $this->reqMgr->get_linked_cfields(null,$argsObj->tproject_id) ;
			$this->reqMgr->values_to_db($request,$ret['id'],$cf_map);
  			$obj->template = 'reqEdit.tpl';
  			$obj->req_id = $ret['id'];
		}
		$argsObj->scope = '';
		return $obj;	
  }


  /*
    function: doUpdate

    args:
    
    returns: 

  */
	function doUpdate(&$argsObj,$request)
	{
		$obj = new stdClass();
	    $descr_prefix = lang_get('req') . TITLE_SEP;
		$ret = $this->reqMgr->update($argsObj->req_id,trim($argsObj->reqDocId),$argsObj->title,
	  				                 $argsObj->scope,$argsObj->user_id,$argsObj->reqStatus,
	  				                 $argsObj->reqType,$argsObj->expected_coverage);

      	$obj = $this->edit($argsObj);
      	$obj->user_feedback = $ret['msg'];
		$obj->template = null;

		if($ret['status_ok'])
		{
        	$obj->main_descr = '';
		    $obj->action_descr = '';
          	$obj->template = "reqView.php?requirement_id={$argsObj->req_id}";
          	// $obj->template = "reqView.php";
		  	$cf_map = $this->reqMgr->get_linked_cfields(null,$argsObj->tproject_id);
		  	$this->reqMgr->values_to_db($request,$argsObj->req_id,$cf_map);

		  	logAuditEvent(TLS("audit_requirement_saved",$argsObj->reqDocId),"SAVE",$argsObj->req_id,"requirements");
		}
		else
		{
			// Action has failed => no change done on DB.
	        $old = $this->reqMgr->get_by_id($argsObj->req_id);
	        $obj->main_descr = $descr_prefix . $old['title'];
          	$obj->cfields = $this->reqMgr->html_table_of_custom_field_values($argsObj->req_id,$argsObj->tproject_id);
		}
		return $obj;	
	}

	function doDelete(&$argsObj)
	{
		$obj = new stdClass();
		$req = $this->reqMgr->get_by_id($argsObj->req_id);
		$this->reqMgr->delete($argsObj->req_id);
		logAuditEvent(TLS("audit_requirement_deleted",$req['req_doc_id']),"DELETE",$argsObj->req_id,"requirements");
  
		$obj->template = 'show_message.tpl';
		$obj->template_dir = '';
		$obj->user_feedback = sprintf(lang_get('req_deleted'),$req['title']);
		$obj->main_descr=lang_get('requirement') . TITLE_SEP . $req['title'];
		$obj->title=lang_get('delete_req');
		$obj->refresh_tree = 'yes';
		$obj->result = 'ok';  // needed to enable refresh_tree logic
		return $obj;
  }
  

	function reorder(&$argsObj)
	{
		$obj = new stdClass();
		  
		$req_spec = $this->reqSpecMgr->get_by_id($argsObj->req_spec_id);
		$all_reqs = $this->reqSpecMgr->get_requirements($argsObj->req_spec_id);

  		$obj->template = 'reqReorder.tpl';
  		$obj->req_spec_id = $argsObj->req_spec_id;
  		$obj->req_spec_name = $req_spec['title'];
  		$obj->all_reqs = $all_reqs;		  
		$obj->main_descr = lang_get('req') . TITLE_SEP . $obj->req_spec_name;

	    return $obj;
  }

	function doReorder(&$argsObj)
	{
      	$obj = new stdClass();
  		$obj->template = 'reqSpecView.tpl';
		$nodes_in_order = transform_nodes_order($argsObj->nodes_order);

		// need to remove first element, is req_spec_id
		$req_spec_id = array_shift($nodes_in_order);
		$this->reqMgr->set_order($nodes_in_order);
		  
		$obj->req_spec = $this->reqSpecMgr->get_by_id($req_spec_id);
      	$obj->refresh_tree = 'yes';
	    
      	return $obj;
  	}
  
	function createTestCases(&$argsObj)
	{
		$guiObj = new stdClass();
		$guiObj->template = 'reqCreateTestCases.tpl';
		$req_spec = $this->reqSpecMgr->get_by_id($argsObj->req_spec_id);
		$guiObj->main_descr = lang_get('req_spec_short') . TITLE_SEP . $req_spec['title'];
		$guiObj->action_descr = lang_get('create_testcase_from_req');
      
		$guiObj->all_reqs = $this->reqSpecMgr->get_requirements($argsObj->req_spec_id);
		$guiObj->req_spec_id = $argsObj->req_spec_id;
		$guiObj->req_spec_name = $req_spec['title'];
		$guiObj->array_of_msg = '';
		  
	    return $guiObj;
  }
  
 	function doCreateTestCases(&$argsObj)
	{
		$guiObj = $this->createTestCases($argsObj);
	    $guiObj->array_of_msg = $this->reqMgr->create_tc_from_requirement($argsObj->arrReqIds,$argsObj->req_spec_id,
	                                                                    $argsObj->user_id);
	    return $guiObj;
	}
}
?>