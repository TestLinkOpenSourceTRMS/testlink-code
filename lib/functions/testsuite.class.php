<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: testsuite.class.php,v $
 * @version $Revision: 1.51 $
 * @modified $Date: 2009/01/06 15:34:06 $ - $Author: franciscom $
 * @author franciscom
 *
 * 20090106 - franciscom - BUGID - exportTestSuiteDataToXML()
 *                         export custom field values
 *  
 * 20080106 - franciscom - viewer_edit_new() changes to use user templates
 *                         to fill details when creating a new test suites.
 *                         new private method related to this feature:
 *                         _initializeWebEditors(), read_file()
 *
 * 20080105 - franciscom - copy_to() changed return type
 *                         minor bug on copy_to. (tcversion nodes were not excluded).
 *
 * 20071111 - franciscom - new method get_subtree();
 * 20071101 - franciscom - import_file_types, export_file_types
 * 
 * 20070826 - franciscom - minor fix html_table_of_custom_field_values()
 * 20070602 - franciscom - added  nt copy on copy_to() method
 *                         using testcase copy_attachment() method.
 *                         added delete attachments. 
 *                         added remove of custom field values 
 *                         (design) when removing test suite.
 *
 * 20070501 - franciscom - added localization of custom field labels
 *                         added use of htmlspecialchars() on labels
 *
 * 20070324 - franciscom - create() interface changes
 *                         get_by_id()changes in result set
 *
 * 20070204 - franciscom - fixed minor GUI bug on html_table_of_custom_field_inputs()
 *
 * 20070116 - franciscom - BUGID 543
 * 20070102 - franciscom - changes to delete_deep() to support custom fields
 * 20061230 - franciscom - custom field management
 * 20061119 - franciscom - changes in create()
 *
 * 20060805 - franciscom - changes in viewer_edit_new()
 *                         keywords related functions
 * 
 * 20060425 - franciscom - changes in show() following Andreas Morsing advice (schlundus)
 *
 */
require_once( dirname(__FILE__) . '/attachments.inc.php');

class testsuite extends tlObjectWithAttachments
{
  const NODE_TYPE_FILTER_OFF=null;
  
 	var $db;
	var $tree_manager;
	var $node_types_descr_id;
	var $node_types_id_descr;
	var $my_node_type;
  var $cfield_mgr;

  var $import_file_types = array("XML" => "XML");
  var $export_file_types = array("XML" => "XML");
 
  // Node Types (NT)
  var $nt2exclude=array('testplan' => 'exclude_me',
	                      'requirement_spec'=> 'exclude_me',
	                      'requirement'=> 'exclude_me');
													                        

  var $nt2exclude_children=array('testcase' => 'exclude_my_children',
													       'requirement_spec'=> 'exclude_my_children');


  /*
    function: testsuite
              constructor

    args:
    
    returns: 

  */
  function testsuite(&$db)
  {
	$this->db = &$db;	
	
	$this->tree_manager =  new tree($this->db);
	$this->node_types_descr_id=$this->tree_manager->get_available_node_types();
	$this->node_types_id_descr=array_flip($this->node_types_descr_id);
	$this->my_node_type=$this->node_types_descr_id['testsuite'];
	
	$this->cfield_mgr=new cfield_mgr($this->db);
	
	tlObjectWithAttachments::__construct($this->db,'nodes_hierarchy');
  }

  /*
    function: get_export_file_types
              getter

    args: -
    
    returns: map  
             key: export file type code
             value: export file type verbose description 

  */
	function get_export_file_types()
	{
     return $this->export_file_types;
  }

  /*
    function: get_impor_file_types
              getter

    args: -
    
    returns: map  
             key: import file type code
             value: import file type verbose description 

  */
	function get_import_file_types()
	{
     return $this->import_file_types;
  }





/*
  function: 

  args :
        $parent_id
        $name
        $details
        [$check_duplicate_name]
        [$action_on_duplicate_name]
        [$order]
  
  returns:   hash	
                  $ret['status_ok'] -> 0/1
                  $ret['msg']
                  $ret['id']        -> when status_ok=1, id of the new element


  rev :
       20070324 - BUGID 710

*/
function create($parent_id,$name,$details,$order=null,
                $check_duplicate_name=0,
                $action_on_duplicate_name='allow_repeat')
{
  $ret['status_ok']=0;
  $ret['msg']='ok';
  $ret['id']=-1;
  
    
	$prefix_name_for_copy = config_get('prefix_name_for_copy');
	
	
	if( is_null($order) )
	{
	  $node_order = config_get('treemenu_default_testsuite_order');
	}
	else
	{
	  $node_order = $order;
	}
	
	$name = trim($name);
	$ret = array('status_ok' => 1, 'id' => 0, 'msg' => 'ok');
	if ($check_duplicate_name)
	{
		
    $sql = " SELECT count(*) AS qty FROM testsuites,nodes_hierarchy 
		         WHERE nodes_hierarchy.name = '" . $this->db->prepare_string($name) . "'" . 
		       " AND testsuites.id=nodes_hierarchy.id
		         AND node_type_id = {$this->my_node_type} 
		         AND nodes_hierarchy.parent_id={$parent_id} "; 
		
		$result = $this->db->exec_query($sql);
		$myrow = $this->db->fetch_array($result);
		
		if( $myrow['qty'])
		{
			if ($action_on_duplicate_name == 'block')
			{
				$ret['status_ok'] = 0;
				$ret['msg'] = lang_get('component_name_already_exists');	
			} 
			else
			{
				$ret['status_ok'] = 1;      
				if ($action_on_duplicate_name == 'generate_new')
				{ 
					$ret['status_ok'] = 1;      
					$name = config_get('prefix_name_for_copy') . " " . $name ;      
				}
			}
		}       
	}
	
	if ($ret['status_ok'])
	{
		// get a new id
		$tsuite_id = $this->tree_manager->new_node($parent_id,$this->my_node_type,
		                                           $name,$node_order);
		$sql = "INSERT INTO testsuites (id,details) " .
				   "VALUES ({$tsuite_id},'" . $this->db->prepare_string($details) . "')";
		             
		$result = $this->db->exec_query($sql);
		if ($result)
		{
			$ret['id'] = $tsuite_id;
		}
	}
	return $ret;
}


function update($id, $name, $details)
{
	//TODO - check for existent name
	$sql = " UPDATE testsuites
	         SET details = '" . $this->db->prepare_string($details) . "'" .
	       " WHERE id = {$id}";
	$result = $this->db->exec_query($sql);
  
	if ($result)
	{
		$sql = " UPDATE nodes_hierarchy SET name='" . 
				$this->db->prepare_string($name) . "' WHERE id= {$id}";
		$result = $this->db->exec_query($sql);
	}

	
	$ret['msg']='ok';
	if (!$result)
	{
		$ret['msg'] = $this->db->error_msg();
	}
	return $ret;
}

                    
/*
  function: get_by_name

  args : name: testsuite name
  
  returns: array where every element is a map with following keys:
           
           id: 	testsuite id (node id)
           details
           name: testsuite name

*/
function get_by_name($name)
{
	$sql = " SELECT testsuites.*, nodes_hierarchy.name " .
		   " FROM testsuites, nodes_hierarchy " .
		   " WHERE nodes_hierarchy.name = '" . 
			$this->db->prepare_string($name) . "'";
	
	$recordset = $this->db->get_recordset($sql);
	return $recordset;
}

/*
  function: get_by_id
            get info for one test suite

  args : id: testsuite id
  
  returns: map with following keys:
           
           id: 	testsuite id (node id)
           details
           name: testsuite name
  
  
  rev :
        20070324 - added node_order in result set

*/
function get_by_id($id)
{
	$sql = " SELECT testsuites.*, NH.name, NH.node_type_id, NH.node_order
	         FROM testsuites,nodes_hierarchy NH 
	         WHERE testsuites.id = NH.id
	         AND testsuites.id = {$id}";
  $recordset = $this->db->get_recordset($sql);
  return($recordset ? $recordset[0] : null);
}


/*
  function: get_all()
            get array of info for every test suite without any kind of filter.
            Every array element contains an assoc array with test suite info

  args : -
  
  returns: array 

*/
function get_all()
{
	$sql = " SELECT testsuites.*, nodes_hierarchy.name
	         FROM testsuites,nodes_hierarchy
	         WHERE testsuites.id = nodes_hierarchy.id";
  $recordset = $this->db->get_recordset($sql);
  return($recordset);
}


/**
 * show()
 *
 * args:  smarty [reference]
 *        id 
 *        sqlResult [default = '']
 *        action [default = 'update']
 *        modded_item_id [default = 0]
 * 
 * returns: -
 *
 **/
function show(&$smarty,$template_dir, $id, $sqlResult = '', $action = 'update',$modded_item_id = 0)
{
	$cf_smarty = '';
  
	$smarty->assign('modify_tc_rights', has_rights($this->db,"mgt_modify_tc"));

	if($sqlResult)
	{ 
		$smarty->assign('sqlResult', $sqlResult);
		$smarty->assign('sqlAction', $action);
	}
	
	$item = $this->get_by_id($id);
	$modded_item = $item;
	if ($modded_item_id)
	{
		$modded_item = $this->get_by_id($modded_item_id);
	}
  
	$cf_smarty = $this->html_table_of_custom_field_values($id);
  
	$keywords_map = $this->get_keywords_map($id,' ORDER BY KEYWORD ASC ');
	$attachmentInfos = getAttachmentInfosFrom($this,$id);
	
	$smarty->assign('refreshTree',false);
	$smarty->assign('attachmentInfos',$attachmentInfos);
	$smarty->assign('id',$id);
 	$smarty->assign('page_title',lang_get('testsuite'));
	$smarty->assign('cf',$cf_smarty);
	$smarty->assign('keywords_map',$keywords_map);
	$smarty->assign('moddedItem',$modded_item);
	$smarty->assign('level', 'testsuite');
	$smarty->assign('container_data', $item);
	$smarty->assign('sqlResult',$sqlResult);
	$smarty->display($template_dir . 'containerView.tpl');
}


/*
  function: viewer_edit_new
            Implements user interface (UI) for edit testuite and 
            new/create testsuite operations.
            

  args : smarty [reference]
         amy_keys
         oWebEditor: rich editor object (today is FCK editor)
         action
         parent_id: testsuite parent id on tree.
         [id]
         [result_msg]: default: null
                       used to give information to user
                       
         [user_feedback]: default: null              
                          used to give information to user
  returns: -

  rev :
       20080105 - franciscom - added $userTemplateCfg
       20071202 - franciscom - interface changes -> template_dir
*/
function viewer_edit_new(&$smarty,$template_dir,$amy_keys, $oWebEditor, $action, $parent_id, 
                         $id=null, $result_msg=null, $user_feedback=null, $userTemplateCfg=null)
{
  $cf_smarty=-2;

  $pnode_info=$this->tree_manager->get_node_hierachy_info($parent_id);
  $parent_info['description']=lang_get($this->node_types_id_descr[$pnode_info['node_type_id']]);
  $parent_info['name']=$pnode_info['name'];
  

	$a_tpl = array( 'edit_testsuite' => 'containerEdit.tpl',
					        'new_testsuite'  => 'containerNew.tpl',
					        'add_testsuite'  => 'containerNew.tpl');
	
	$the_tpl = $a_tpl[$action];
	$smarty->assign('sqlResult', $result_msg);
	$smarty->assign('containerID',$parent_id);	 
	$smarty->assign('user_feedback', $user_feedback);
	
	$the_data = null;
	
	$name = '';
	if ($action == 'edit_testsuite')
	{
		$the_data = $this->get_by_id($id);
		$name=$the_data['name'];
		$smarty->assign('containerID',$id);	
  }
  $webEditorData = $the_data;
	
  // Custom fields
  $cf_smarty = $this->html_table_of_custom_field_inputs($id,$parent_id);
  $smarty->assign('cf',$cf_smarty);	
	
	// webeditor
	if( $action == 'new_testsuite' && !is_null($userTemplateCfg) )
	{
	   // need to understand if need to use templates
	   $webEditorData=$this->_initializeWebEditors($amy_keys,$userTemplateCfg);
	   
	} 
	foreach ($amy_keys as $key)
	{
		// Warning:
		// the data assignment will work while the keys in $the_data are identical
		// to the keys used on $oWebEditor.
		$of = &$oWebEditor[$key];
		$of->Value = isset($webEditorData[$key]) ? $webEditorData[$key] : null;
		$smarty->assign($key, $of->CreateHTML());
	}
	
	$smarty->assign('parent_info', $parent_info);
	$smarty->assign('level', 'testsuite');
	$smarty->assign('name',$name);
	$smarty->assign('container_data',$the_data);
	$smarty->display($template_dir . $the_tpl);
}


/*
  function: copy_to
            deep copy one testsuite to another parent (testsuite or testproject).
            

  args : id: testsuite id (source or copy)
         parent_id:
         user_id: who is requesting copy operation
         [check_duplicate_name]: default: 0 -> do not check
                                          1 -> check for duplicate when doing copy
                                               What to do if duplicate exists, is controlled
                                               by action_on_duplicate_name argument.
                                               
         [action_on_duplicate_name argument]: default: 'allow_repeat'.
                                              Used when check_duplicate_name=1.
                                              Specifies how to react if duplicate name exists.
                                              
                                               
                                               
  
  returns: map with foloowing keys:
           status_ok: 0 / 1
           msg: 'ok' if status_ok == 1
           id: new created if everything OK, -1 if problems.

  rev :
       20080105 - franciscom - changed return type
       20070324 - BUGID 710
*/
function copy_to($id, $parent_id, $user_id,
                 $check_duplicate_name = 0,
				         $action_on_duplicate_name = 'allow_repeat',
				         $copyKeywords = 0 )
{
  $exclude_children_of=array('testcase' => 'exclude my children');
	$tcase_mgr = new testcase($this->db);
	
	$tsuite_info = $this->get_by_id($id);
	$op = $this->create($parent_id,$tsuite_info['name'],$tsuite_info['details'],
	                    $tsuite_info['node_order'], 
					           $check_duplicate_name,$action_on_duplicate_name);
	
	
	$new_tsuite_id = $op['id'];
  $tcase_mgr->copy_attachments($id,$new_tsuite_id);
	
	$subtree = $this->tree_manager->get_subtree($id,self::NODE_TYPE_FILTER_OFF,$exclude_children_of);
	if (!is_null($subtree))
	{
	  
	  $parent_decode=array();
    // key: original parent id
	  // value: new parent id
	  $parent_decode[$id]=$new_tsuite_id;
		
		foreach($subtree as $the_key => $elem)
		{
		  $the_parent_id=$parent_decode[$elem['parent_id']];
			switch ($elem['node_type_id'])
			{
				case $this->node_types_descr_id['testcase']:
					$tcase_mgr->copy_to($elem['id'],$the_parent_id,$user_id,$copyKeywords);
					break;
					
				case $this->node_types_descr_id['testsuite']:
					$tsuite_info = $this->get_by_id($elem['id']);
					$ret = $this->create($the_parent_id,$tsuite_info['name'],
					                     $tsuite_info['details'],$tsuite_info['node_order']);      
				  
			    $parent_decode[$elem['id']]=$ret['id'];
		      $tcase_mgr->copy_attachments($elem['id'],$ret['id']);
					break;
			}
		}
	}
	return $op;
}


/*
  function: get_subtree
            Get subtree that has choosen testsuite as root.
            Only nodes of type: 
            testsuite and testcase are explored and retrieved.

  args: id: testsuite id
        [recursive_mode]: default false
        
  
  returns: map
           see tree->get_subtree() for details.

*/
function get_subtree($id,$recursive_mode=false)
{
  $exclude_branches=null; 
  $and_not_in_clause='';
  
	$subtree = $this->tree_manager->get_subtree($id,$this->nt2exclude,
	                                                $this->nt2exclude_children,
	                                                $exclude_branches,
	                                                $and_not_in_clause,
	                                                $recursive_mode);
  return $subtree;
}



/*
  function: get_testcases_deep
            get all test cases in the test suite and all children test suites
            no info about tcversions is returned.

  args : id: testsuite id
         [bIdsOnly]: default false
                     Structure of elements in returned array, changes according to
                     this argument:
          
                     bIdsOnly=true
                     Array that contains ONLY testcase id, no other info.
                     
                     bIdsOnly=false
                     Array where each element is a map with following keys.
                     
                     id: testcase id
                     parent_id: testcase parent (a test suite id).
                     node_type_id: type id, for a testcase node
                     node_order
                     node_table: node table, for a testcase.
                     name: testcase name
  
  returns: array

*/
function get_testcases_deep($id,$bIdsOnly = false)
{
	$subtree = $this->get_subtree($id);
	             					      
	$testcases = null;
	if(!is_null($subtree))
	{
		$testcases = array();
		$tcNodeType = $this->node_types_descr_id['testcase'];
		foreach ($subtree as $the_key => $elem)
		{
			if($elem['node_type_id'] == $tcNodeType)
			{
				if ($bIdsOnly)
					$testcases[] = $elem['id'];
				else
					$testcases[]= $elem;
			}
		}
	}
	
	return $testcases; 
}



/*
  function: delete_deep

  args : $id
  
  returns: 

  rev :
       20070602 - franciscom
       added delete attachments
*/
function delete_deep($id)
{
  $tcase_mgr = New testcase($this->db);
	$tsuite_info = $this->get_by_id($id);
	
	// 20071111 - franciscom
  $subtree = $this->tree_manager->get_subtree($id);
	
	// add me, to delete me 
	$subtree[]=array('id' => $id);
	$testcases = $this->get_testcases_deep($id);

  if (!is_null($subtree))
	{
    // -------------------------------------------------------------------
    // First delete dependent objects
    if (!is_null($testcases))
	  {
	    foreach($testcases as $the_key => $elem)
	    {
        $tcase_mgr->delete($elem['id']);
	    }
	  }  
    // -------------------------------------------------------------------

    // -------------------------------------------------------------------
		$node_list = array();
		$node_list[]=$id;
	  foreach($subtree as $the_key => $elem)
	  {
      $node_list[]= $elem['id'];
      
      // 20070602 - franciscom
      $tcase_mgr->deleteAttachments($elem['id']);
      $this->cfield_mgr->remove_all_design_values_from_node($elem['id']);

      $this->deleteKeywords($elem['id']);
	  }
    $tsuites_id_list=implode(",",$node_list);    
	
	  $sql = "DELETE FROM testsuites WHERE id IN ({$tsuites_id_list})";
		$result = $this->db->exec_query($sql);
    // -------------------------------------------------------------------

    // 20070102 - franciscom
    $this->cfield_mgr->remove_all_design_values_from_node($node_list);
    
    // Delete tree structure (from node_hierarchy)
    $this->tree_manager->delete_subtree($id);
	}
} // end function


/*
  function: initializeWebEditors

  args:
  
  returns: 

*/
private function _initializeWebEditors($WebEditors,$templateCfg)
{
  $wdata=array();
  foreach ($WebEditors as $key => $html_name)
  {
    switch($templateCfg->$html_name->type)
    {
      case 'string':
    	$wdata[$html_name] = $templateCfg->$html_name->value;
      break;
      
      case 'string_id':
    	$wdata[$html_name] = lang_get($templateCfg->$html_name->value);
      break;
      
      
      case 'file':
    	$wdata[$html_name] = $this->read_file($templateCfg->$html_name->value);
      break;
      
      default:
      $wdata[$html_name] = '';
      break;
    }
  } // foreach  
  return $wdata;
}


/*
  function: read_file

  args: file_name 
  
  returns: if file exist and can be read -> file contents
           else error message

*/
private function read_file($file_name)
{
	$fContents = null;
	@$fd = fopen($file_name,"rb");
	if ($fd)
	{
		$fContents = fread($fd,filesize($file_name));
		fclose($fd);
	}
	else
	{
	  $fContents= lang_get('problems_trying_to_access_template') . " {$file_name} ";  
	}
	return $fContents;
}



/*
  function: getKeywords
            Get keyword assigned to a testsuite.
            Uses table object_keywords.
            
            Attention:
            probably write on obejct_keywords has not been implemented yet,
            then right now thie method can be useless.
             

	args:	id: testsuite id
        kw_id: [default = null] the optional keyword id
  
  returns: null if nothing found.
           array, every elemen is map with following structure:
	         id
	         keyword
	         notes
  
  rev : 
        20070116 - franciscom - BUGID 543

*/
function getKeywords($id,$kw_id = null)
{
	$sql = "SELECT keyword_id,keywords.keyword, notes " .
	       " FROM object_keywords,keywords " .
	       " WHERE keyword_id = keywords.id AND fk_id = {$id}";
	if (!is_null($kw_id))
	{
		$sql .= " AND keyword_id = {$kw_id}";
	}	
	$map_keywords = $this->db->fetchRowsIntoMap($sql,'keyword_id');
	
	return($map_keywords);
} 


/*
  function: get_keywords_map
            All keywords for a choosen testsuite

            Attention:
            probably write on obejct_keywords has not been implemented yet,
            then right now thie method can be useless.


  args :id: testsuite id
        [order_by_clause]: default: '' -> no order choosen
                           must be an string with complete clause, i.e.
                           'ORDER BY keyword'

  
  
  returns: map: key: keyword_id
                value: keyword
  

*/
function get_keywords_map($id,$order_by_clause='')
{
	$sql = "SELECT keyword_id,keywords.keyword 
	        FROM object_keywords,keywords 
	        WHERE keyword_id = keywords.id ";
	if (is_array($id))
		$sql .= " AND fk_id IN (".implode(",",$id).") ";
	else
		$sql .= " AND fk_id = {$id} ";
		
	$sql .= $order_by_clause;

	$map_keywords = $this->db->fetchColumnsIntoMap($sql,'keyword_id','keyword');
	return($map_keywords);
} 



function addKeyword($id,$kw_id)
{
	$kw = $this->getKeywords($id,$kw_id);
	if (sizeof($kw))
	{
		return 1;
	}	
	$sql = " INSERT INTO object_keywords (fk_id,fk_table,keyword_id) " .
		     " VALUES ($id,'nodes_hierarchy',$kw_id)";

	return ($this->db->exec_query($sql) ? 1 : 0);
}


/*
  function: addKeywords

  args :
  
  returns: 

*/
function addKeywords($id,$kw_ids)
{
	$status = 1;
	$num_kws = sizeof($kw_ids);
	for($idx = 0; $idx < $num_kws; $idx++)
	{
		$status = $status && $this->addKeyword($id,$kw_ids[$idx]);
	}
	return($status);
}


/*
  function: deleteKeywords

  args :
  
  returns: 

*/
function deleteKeywords($id,$kw_id = null)
{
	$sql = " DELETE FROM object_keywords WHERE fk_id = {$id} ";
	if (!is_null($kw_id))
	{
		$sql .= " AND keyword_id = {$kw_id}";
	}	
	return($this->db->exec_query($sql));
}

/*
  function: exportTestSuiteDataToXML

  args :
  
  returns: 

*/
function exportTestSuiteDataToXML($container_id,$tproject_id,$optExport = array())
{
  $USE_RECURSIVE_MODE=true;
	$xmlTC = null;
	$bRecursive = @$optExport['RECURSIVE'];
	if ($bRecursive)
	{
	  $cfXML = null;
		$kwXML = null;
		$tsuiteData = $this->get_by_id($container_id);
		if (@$optExport['KEYWORDS'])
		{
			$kwMap = $this->getKeywords($container_id);
			if ($kwMap)
			{
				$kwXML = exportKeywordDataToXML($kwMap,true);
			}	
		}
		
		// 20090106 - franciscom - custom fields
    $cfMap=$this->get_linked_cfields_at_design($container_id,null,null,$tproject_id);
		if( !is_null($cfMap) && count($cfMap) > 0 )
	  {
        $cfRootElem = "<custom_fields>{{XMLCODE}}</custom_fields>";
	      $cfElemTemplate = "\t" . '<custom_field><name><![CDATA[' . "\n||NAME||\n]]>" . "</name>" .
	      	                       '<value><![CDATA['."\n||VALUE||\n]]>".'</value></custom_field>'."\n";
	      $cfDecode = array ("||NAME||" => "name","||VALUE||" => "value");
	      $cfXML = exportDataToXML($cfMap,$cfRootElem,$cfElemTemplate,$cfDecode,true);
	  } 
	
		$xmlTC = "<testsuite name=\"".htmlspecialchars($tsuiteData['name']).
		         "\"><details><![CDATA[\n{$tsuiteData['details']}\n]]>{$kwXML}{$cfXML}</details>";
	}
	else
	{
		$xmlTC = "<testcases>";
  }
  
	$test_spec = $this->get_subtree($container_id,$USE_RECURSIVE_MODE);

	$childNodes = @$test_spec['childNodes'];
	$tcase_mgr=null;
	for($idx = 0;$idx < sizeof($childNodes);$idx++)
	{
		$cNode = $childNodes[$idx];
		$nTable = $cNode['node_table'];
		if ($bRecursive && $nTable == 'testsuites')
		{
			$xmlTC .= $this->exportTestSuiteDataToXML($cNode['id'],$tproject_id,$optExport);
		}
		else if ($nTable == 'testcases')
		{
		  if( is_null($tcase_mgr) )
		  {
			    $tcase_mgr = new testcase($this->db);
			}
			$xmlTC .= $tcase_mgr->exportTestCaseDataToXML($cNode['id'],TC_LATEST_VERSION,$tproject_id,true,$optExport);
		}
	}
	if ($bRecursive)
		$xmlTC .= "</testsuite>";
	else
		$xmlTC .= "</testcases>";
		
	return $xmlTC;
}


// -------------------------------------------------------------------------------
// Custom field related methods
// -------------------------------------------------------------------------------
/*
  function: get_linked_cfields_at_design
            
            
  args: $id
        [$parent_id]:
        [$show_on_execution]: default: null
                              1 -> filter on field show_on_execution=1
                              0 or null -> don't filter
        
        
  returns: hash
  
  rev :
        20061231 - franciscom - added $parent_id
*/
	function get_linked_cfields_at_design($id,$parent_id=null,$show_on_execution=null,$tproject_id = null) 
	{
		if (!$tproject_id)
		{
			$tproject_id = $this->getTestProjectFromTestSuite($id,$parent_id);
		}
		$enabled = 1;
		$cf_map = $this->cfield_mgr->get_linked_cfields_at_design($tproject_id,$enabled,
	                                                            $show_on_execution,'testsuite',$id);
		return $cf_map;
	}
	
	//@TODO: schlundus, same function as in Testcase.class / TestPlan.class / Testsuite => refactor as it's always the same principle
	// get the project Node from one of different node types
	function getTestProjectFromTestSuite($id,$parent_id)
	{
		$the_path = $this->tree_manager->get_path( (!is_null($id) && $id > 0) ? $id : $parent_id);
		$path_len = count($the_path);
		$tproject_id = ($path_len > 0)? $the_path[0]['parent_id'] : $parent_id;
		return $tproject_id;
	}
/*
  function: get_linked_cfields_at_execution
            
            
  args: $id
        [$parent_id]
        [$show_on_execution]: default: null
                              1 -> filter on field show_on_execution=1
                              0 or null -> don't filter
        
        
  returns: hash
  
  rev :
        20061231 - franciscom - added $parent_id
*/
function get_linked_cfields_at_execution($id,$parent_id=null,$show_on_execution=null) 
{
  $enabled=1;
  $the_path=$this->tree_manager->get_path(!is_null($id) ? $id : $parent_id);
  $path_len=count($the_path);
  $tproject_id=($path_len > 0)? $the_path[$path_len-1]['parent_id'] : $parent_id;

  $cf_map=$this->cfield_mgr->get_linked_cfields_at_design($tproject_id,$enabled,
                                                          $show_on_execution,'testsuite',$id);
  return($cf_map);
}



/*
  function: html_table_of_custom_field_inputs
            
            
  args: $id
        [$parent_id]: need when you call this method during the creation
                      of a test suite, because the $id will be 0 or null.
                      
        [$scope]: 'design','execution'
        
  returns: html string
  
*/
function html_table_of_custom_field_inputs($id,$parent_id=null,$scope='design') 
{
  $cf_smarty='';
  
  if( $scope=='design' )
  {
    $cf_map=$this->get_linked_cfields_at_design($id,$parent_id);
  }
  else
  {
    $cf_map=$this->get_linked_cfields_at_execution($id,$parent_id);
  }
  
  if( !is_null($cf_map) )
  {
    foreach($cf_map as $cf_id => $cf_info)
    {
      // 20070501 - franciscom 
      $label=str_replace(TL_LOCALIZE_TAG,'',lang_get($cf_info['label']));
      $cf_smarty .= '<tr><td class="labelHolder">' . htmlspecialchars($label) . "</td><td>" .
                    $this->cfield_mgr->string_custom_field_input($cf_info) .
                    "</td></tr>\n";
    } //foreach($cf_map
  }
  
  if( strlen(trim($cf_smarty)) > 0 )
  {
    $cf_smarty = "<table>" . $cf_smarty . "</table>";
  }
  
  return($cf_smarty);
}


/*
  function: html_table_of_custom_field_values
            
            
  args: $id
        [$scope]: 'design','execution'
        [$show_on_execution]: default: null
                              1 -> filter on field show_on_execution=1
                              0 or null -> don't filter
  
  returns: html string
  
*/
function html_table_of_custom_field_values($id,$scope='design',$show_on_execution=null,$tproject_id = null) 
{
  $cf_smarty='';
  $parent_id=null;
  
  if( $scope=='design' )
  {
    $cf_map = $this->get_linked_cfields_at_design($id,$parent_id,$show_on_execution,$tproject_id);
  }
  else 
  {
  	//@TODO: schlundus, can this be speed up with tprojectID?
    $cf_map=$this->get_linked_cfields_at_execution($id);
  }
    
  if( !is_null($cf_map) )
  {
    foreach($cf_map as $cf_id => $cf_info)
    {
      // if user has assigned a value, then node_id is not null
      if($cf_info['node_id'])
      {
        // 20070501 - franciscom
        $label=str_replace(TL_LOCALIZE_TAG,'',lang_get($cf_info['label']));
        $cf_smarty .= '<tr><td class="labelHolder">' . htmlspecialchars($label) . "</td><td>" .
                      $this->cfield_mgr->string_custom_field_value($cf_info,$id) .
                      "</td></tr>\n";
      }
    }
  }
  // 20070826 - to avoid returning empty table
  if( strlen(trim($cf_smarty)) > 0 )
  {
    $cf_smarty = "<table>" . $cf_smarty . "</table>";
  }
  return($cf_smarty);
} // function end



} // end class

?>
