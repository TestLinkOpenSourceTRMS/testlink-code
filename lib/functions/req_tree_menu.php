<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: req_tree_menu.php,v $
 *
 * @version $Revision: 1.7 $
 * @modified $Date: 2008/05/05 09:11:43 $ by $Author: franciscom $
 *
 * Rev :
 *      20071125 - franciscom - added dtree_render_req_node_open
 *
 **/
require_once(dirname(__FILE__)."/../../config.inc.php");

/**
 * generate data for tree menu of Test Specification
 *
 * 20071014 - franciscom - $bForPrinting
 *                         used to choose Javascript function
 *                         to call when clicking on a tree node
 *
 *
 * 20070922 - franciscom - interface changes added $tplan_id,
 * 20070217 - franciscom - added $exclude_branches
 *
 * 20061105 - franciscom - added $ignore_inactive_testcases
 *
 * ignore_inactive_testcases: if all test case versions are inactive,
 *                            the test case will ignored.
 *
 * exclude_branches: map key=node_id
 *
*/
function gen_req_tree_menu(&$db,$tproject_id, $tproject_name)
{
	$menustring = null;
	$map_node_req_count=array();

	$tproject_mgr = new testproject($db);
	$tree_manager = &$tproject_mgr->tree_manager;


	$tcase_node_type = $tree_manager->node_descr_id['testcase'];
	$hash_descr_id = $tree_manager->get_available_node_types();
	$hash_id_descr = array_flip($hash_descr_id);
  $status_descr_code=config_get('tc_status');
  $status_code_descr=array_flip($status_descr_code);

  $decoding_hash=array('node_id_descr' => $hash_id_descr,
                       'status_descr_code' =>  $status_descr_code,
                       'status_code_descr' =>  $status_code_descr);


	$nt2exclude=array('testplan' => 'exclude_me','testsuite' => 'exclude_me');
  $nt2exclude_children=array('testcase' => 'exclude_my_children');

  $exclude_branches=null;
	$req_tree = $tree_manager->get_subtree($tproject_id,
	                                       $nt2exclude,$nt2exclude_children,
	                                       $exclude_branches,
	                                       NO_NODE_TYPE_TO_FILTER,
	                                       RECURSIVE_MODE);


	// Added root node for requirement specs -> testproject
  $req_tree['name'] = $tproject_name;
	$req_tree['id'] = $tproject_id;
	$req_tree['node_type_id'] = $hash_descr_id['testproject'];
  $req_tree['node_type'] = 'testproject';

	$getArguments='';

	if($req_tree)
	{
  	$req_counters = prepare_req_node($db,$req_tree,$decoding_hash,$map_node_req_count);

		foreach($req_counters as $key => $value)
		{
		  $test_tree[$key]=$value;
		}

	  $menustring = render_req_tree_node(1,$req_tree,$getArguments,$hash_id_descr);

  } // if($req_tree)

  return $menustring;
}


/*
  function:

  args:

  returns:

*/
function prepare_req_node(&$db,&$node,&$decoding_info,&$map_node_req_count,$status = null)
{
  // ------------------------------------------------------------------------------
  $hash_id_descr=$decoding_info['node_id_descr'];
  $status_descr_code=$decoding_info['status_descr_code'];
  $status_code_descr=$decoding_info['status_code_descr'];

  $my_counters=array('requirement_count' => 0);
  foreach($status_descr_code as $status_descr => $status_code)
  {
    $my_counters[$status_descr]=0;
  }
  // ------------------------------------------------------------------------------

	$node_type = $hash_id_descr[$node['node_type_id']];
  $my_counters['requirement_count']=0;

	if ($node_type == 'requirement')
	{
		foreach($my_counters as $key => $value)
		{
		  $my_counters[$key]=0;
		}

		$tc_status_descr="not_run";
    $init_value=$node ? 1 : 0;
		$my_counters[$tc_status_descr]=$init_value;
		$my_counters['requirement_count']=$init_value;


	}

	if (isset($node['childNodes']) && $node['childNodes'])
	{
		$childNodes = &$node['childNodes'];
		for($i = 0;$i < sizeof($childNodes);$i++)
		{
			$current = &$childNodes[$i];
			// I use set an element to null to filter out leaf menu items
			if(is_null($current))
				continue;

			$counters_map = prepare_req_node($db,$current,$decoding_info,$map_node_req_count,
			                                 $status);
      foreach($counters_map as $key => $value)
      {
        $my_counters[$key] += $counters_map[$key];
      }
		}
    foreach($my_counters as $key => $value)
    {
        $node[$key] = $my_counters[$key];
    }


		if (isset($node['id']))
		{
			$map_node_req_count[$node['id']] = array(	'req_count' => $node['requirement_count'],
		                                       		  'name'      => $node['name']);
		}
	}
 	else if ($node_type == 'requirement_spec')
	{
		$map_node_req_count[$node['id']] = array(	'req_count' => 0,
								                              'name' => $node['name']	  );
	}

	return $my_counters;
}

/*
  function:

  args:

  returns:

*/
function render_req_tree_node($level,&$node,$getArguments,$hash_id_descr,$show_node_id=0)
{
	$node_type = $hash_id_descr[$node['node_type_id']];

	if (TL_TREE_KIND == 'JTREE')
		$menustring = jtree_render_req_node_open($node,$node_type,$show_node_id);
	else if (TL_TREE_KIND == 'DTREE')
		$menustring = dtree_render_req_node_open($node,$node_type,$getArguments,$show_node_id);
	else
		$menustring = layersmenu_render_req_node_open($node,$node_type,$linkto,$getArguments,$level,$show_node_id);

	if (isset($node['childNodes']) && $node['childNodes'])
	{
		$childNodes = $node['childNodes'];
		$nChildren = sizeof($childNodes);
		for($idx = 0;$idx < $nChildren;$idx++)
		{
			$current = $childNodes[$idx];
			if(is_null($current))
				continue;

			$menustring .= render_req_tree_node($level+1,$current,$getArguments,$hash_id_descr,$show_node_id);
		}
	}
	if (TL_TREE_KIND == 'JTREE')
		$menustring .= jtree_render_req_node_close($node,$node_type);

	return $menustring;
}


/*
  function: jtree_render_req_node_open

  args:

  returns:

*/
function jtree_render_req_node_open($node,$node_type,$show_node_id=0)
{
	$menustring = "['";
	$name = filterString($node['name']);
	$buildLinkTo = 1;
	$pfn = "ET";
	$label=null;
	$item_count = isset($node['requirement_count']) ? $node['requirement_count'] : 0;

  switch($node_type)
  {
	  case 'testproject':
		$pfn = 'TPROJECT_REQ_SPEC_MGMT';
		$label =  $name . " (" . $item_count . ")";
	  break;

	  case 'requirement_spec':
		$pfn = 'REQ_SPEC_MGMT';
		$label =  $name . " (" . $item_count . ")";
	  break;

	  case 'requirement':
	  $pfn = "REQ_MGMT";

	  $label = $name;
	  if($show_node_id)
	  {
		  $label = "<b>" . $node['id'] . "</b>: " . $label;
	  }
	  break;
  } // switch
  
	$menustring = "['{$label}','{$pfn}({$node['id']})',\n";

	return $menustring;
}


function jtree_render_req_node_close($node,$node_type)
{
	$menustring =  "],";

	return $menustring;
}

/*
  function:

  args:

  returns:

*/
function dtree_render_req_node_open($node,$node_type,$getArguments,$show_node_id)
{
	$dtreeCounter = $node['id'];

	$parent_id = isset($node['parent_id']) ? $node['parent_id'] : -1;
	$name = filterString($node['name']);
	$item_count = isset($node['requirement_count']) ? $node['requirement_count'] : 0;

  switch($node_type)
  {
	  case 'testproject':
		$pfn = 'TPROJECT_REQ_SPEC_MGMT';
		$label =  $name . " (" . $item_count . ")";
	  break;

	  case 'requirement_spec':
		$pfn = 'REQ_SPEC_MGMT';
		$label =  $name . " (" . $item_count . ")";
	  break;

	  case 'requirement':
	  $pfn = "REQ_MGMT";

	  $label = $name;
	  if($show_node_id)
	  {
		  $label = "<b>" . $node['id'] . "</b>: " . $label;
	  }
	  break;
  } // switch

	$myLinkTo = "javascript:{$pfn}({$node['id']})";
	$menustring = "tlTree.add(" . $dtreeCounter . ",{$parent_id},'" ;
	$menustring .= $label. "','{$myLinkTo}');\n";

	return $menustring;
}

/*
  function:

  args:

  returns:

*/
function layersmenu_render_req_node_open($node,$node_type,$linkto,$getArguments,$level,$show_node_id)
{
	$cfg=config_get('testcase_cfg');
	$name = filterString($node['name']);
	$icon = "";
	$dots  = str_repeat('.',$level);
	$item_count = isset($node['requirement_count']) ? $node['requirement_count'] : 0;

	
  switch($node_type)
  {
	  case 'testproject':
		$pfn = 'TPROJECT_REQ_SPEC_MGMT';
		$label = $name . " ({$item_count})";
		$dots = ".";
    break;

	  case 'requirement_spec':
		$pfn = 'REQ_SPEC_MGMT';
		$label = $name . " ({$item_count})";
	  break;

	  case 'requirement':
		$icon = "gnome-starthere-mini.png";
	  $pfn = "REQ_MGMT";

	  $label = $name;
	  if($show_node_id)
	  {
		  $label = "<b>" . $node['id'] . "</b>: " . $label;
	  }
	  break;
	}	
	
	$myLinkTo = "javascript:{$pfn}({$node['id']})";
	$menustring = "{$dots}|{$label}|{$myLinkTo}|{$node_type}". 
		           "|{$icon}||\n";
		
	return $menustring;				
}

?>