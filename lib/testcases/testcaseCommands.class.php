<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * Filename $RCSfile: testcaseCommands.class.php,v $
 *
 * @version $Revision: 1.10 $
 * @modified $Date: 2010/01/03 11:07:21 $  by $Author: franciscom $
 * testcases commands
 *
 * rev:
 *	20090831 - franciscom - preconditions 
 *	BUGID 2364 - changes in show() calls
 *  BUGID - doAdd2testplan() - added user id, con call to link_tcversions()
 *
**/
class testcaseCommands
{
	private $db;
	private $tcaseMgr;
	private $templateCfg;
	private $execution_types;

	function __construct(&$db)
	{
	    $this->db=$db;
	    $this->tcaseMgr = new testcase($db);
        $this->execution_types = $this->tcaseMgr->get_execution_types();
	}

	function setTemplateCfg($cfg)
	{
	    $this->templateCfg=$cfg;
	}

	/**
	 * 
	 *
	 */
	function initGuiBean()
	{
	    $obj = new stdClass();
	    $obj->loadOnCancelURL = '';
		$obj->attachments = null;
		$obj->direct_link = null;
	    $obj->execution_types = $this->execution_types;
	    return $obj;
	}
	 



	/*
	  function: edit (Test Case)
	
	  args:
	  
	  returns: 
	
	*/
	function edit(&$argsObj,&$otCfg,$oWebEditorKeys)
	{
    	$guiObj = $this->initGuiBean();
    	$otCfg->to->map = $this->tcaseMgr->get_keywords_map($argsObj->tcase_id," ORDER BY keyword ASC ");
    	keywords_opt_transf_cfg($otCfg, $argsObj->assigned_keywords_list);
  		$tc_data = $this->tcaseMgr->get_by_id($argsObj->tcase_id,$argsObj->tcversion_id);

  		foreach($oWebEditorKeys as $key)
   		{
  		  	$guiObj->$key = $tc_data[0][$key];
  		  	$argsObj->$key = $tc_data[0][$key];
  		}
  		
  		$cf_smarty = null;
		$cfPlaces = $this->tcaseMgr->buildCFLocationMap();
		foreach($cfPlaces as $locationKey => $locationFilter)
		{ 
			$cf_smarty[$locationKey] = 
				$this->tcaseMgr->html_table_of_custom_field_inputs($argsObj->tcase_id,null,'design','',
				                                                   null,null,null,$locationFilter);
		}	
   		$templateCfg = templateConfiguration('tcEdit');
		$guiObj->cf = $cf_smarty;
    	$guiObj->tc=$tc_data[0];
    	$guiObj->opt_cfg=$otCfg;
		$tpl_cfg=config_get('tpl');
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
        $smartyObj = new TLSmarty();
        $viewer_args=array();

    	$guiObj = $this->initGuiBean();
   	    $guiObj->refresh_tree=$argsObj->do_refresh?"yes":"no";
        $guiObj->has_been_executed = $argsObj->has_been_executed;

		  // to get the name before the user operation
        $tc_old = $this->tcaseMgr->get_by_id($argsObj->tcase_id,$argsObj->tcversion_id);

        $ret=$this->tcaseMgr->update($argsObj->tcase_id, $argsObj->tcversion_id, $argsObj->name, 
		                             $argsObj->summary, $argsObj->preconditions, $argsObj->steps, 
		                             $argsObj->expected_results, $argsObj->user_id, 
		                             $argsObj->assigned_keywords_list,
		                             TC_DEFAULT_ORDER, $argsObj->exec_type, $argsObj->importance);

        if($ret['status_ok'])
		{
		    $refresh_tree='yes';
		    $msg = '';
  			$ENABLED = 1;
	  		$NO_FILTERS = null;
		  	$cf_map=$this->tcaseMgr->cfield_mgr->get_linked_cfields_at_design($argsObj->testproject_id,
		                                                                      $ENABLED,$NO_FILTERS,'testcase') ;
			$this->tcaseMgr->cfield_mgr->design_values_to_db($request,$argsObj->tcase_id);
         
            $guiObj->attachments[$argsObj->tcase_id] = getAttachmentInfosFrom($this->tcaseMgr,$argsObj->tcase_id);
		}
		else
		{
		    $refresh_tree='no';
		    $msg = $ret['msg'];
		}
	
	    $viewer_args['refresh_tree'] = $refresh_tree;
 	    $viewer_args['user_feedback'] = $msg;
      
	    $this->tcaseMgr->show($smartyObj,$guiObj, $this->templateCfg->template_dir,
	                          $argsObj->tcase_id,$argsObj->tcversion_id,$viewer_args,null,$argsObj->show_mode);
 
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
    	$guiObj = $this->initGuiBean();

      	$viewer_args=array();
      	$tplan_mgr = new testplan($this->db);
      	
   	  	$guiObj->refresh_tree=$argsObj->do_refresh?"yes":"no";
      	$item2link[$argsObj->tcase_id]=$argsObj->tcversion_id;
      	
      	if( isset($request['add2tplanid']) )
      	{
      	    foreach($request['add2tplanid'] as $tplan_id => $value)
      	    {
      	        $tplan_mgr->link_tcversions($tplan_id,$item2link,$argsObj->user_id);  
      	    }
      	    $this->tcaseMgr->show($smartyObj,$guiObj,$this->templateCfg->template_dir,
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

} // end class  
?>