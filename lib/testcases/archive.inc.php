<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: archive.inc.php,v $
 *
 * @version $Revision: 1.28 $
 * @modified $Date: 2006/01/09 08:25:44 $ by $Author: franciscom $
 *
 * @author Martin Havlat
 * Purpose:  functions for test specification management have three parts:
 *		1. grab data from db
 *		2. show test specification
 *		3. copy/move data within test specification         
 *
 * @author Francisco Mancardi - 20051201 - BUGID 258 
 * Management of Component Duplicate Name - block on copy gives no message to user
 *
 * @author Francisco Mancardi - 20051129 - 
 * BUGID 0000259
 * added logic to check for existent name in moveComponentToProduct()
 *
 * @author Francisco Mancardi - 20051121 - fm - autogoal mgtid instead oif mgtcatid
 * BUGID 0000236: unable to re-order categories in component
 *
 * @author Francisco Mancardi - 20051112
 * BUGID 000218
 *
 * @author Francisco Mancardi - 20050910
 * bug on insertProductC
 * 
 * @author Francisco Mancardi - 20050820
 * $data -> container_data (to avoid problems with field data table mgmtcategory)
 *
 * @author Francisco Mancardi - 20050820
 * refactoring getTestcase(), getTestcaseTitle()
**//////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////

require_once('requirements.inc.php');

// 20060108 - fm
require_once('../functions/users.inc.php');


/** 1. functions for grab container and test case data from database */ 

function getComponent(&$db,$id)
{
	$sqlCOM = "SELECT id,name,intro,scope,ref,method,lim FROM mgtcomponent " .
			      "WHERE id=" . $id;
	$resultCOM = $db->exec_query($sqlCOM);

	return $db->fetch_array($resultCOM);
}

function getCategory(&$db,$id)
{
	$sql = "SELECT id,name,objective,config,data,tools,compid FROM mgtcategory " .
			   "WHERE id=" . $id;
	$result = $db->exec_query($sql);

	return $db->fetch_array($result);
}


/** 
* function get all TC data and convert for view
*
* @var integer $id
* @var bool $convert TRUE is default
*/
function getTestcase(&$db,$id, $convert = TRUE)
{
	// execute SQL request
	$sql  = " SELECT id,title,summary,steps,exresult,version,keywords,
			       author AS ' ', reviewer AS ' ', author_id, reviewer_id,
			       create_date,
			       modified_date,catid,TCorder 
			       FROM mgttestcase
			       WHERE id=" . $id ;
			     
	$result = $db->exec_query($sql);
	$myrow = $db->fetch_array($result);
	
	if ($convert)
	{
		// 20050820 - fm - refactoring
		$a_keys = array('title','summary','steps','exresult');

		// prepare data
	    foreach($a_keys as $field_name)
	    {
	    	$myrow[$field_name] = stripslashes($myrow[$field_name]);
		}
			
		//Chop the trailing comma off of the end of field
		$myrow['keywords'] = substr($myrow['keywords'], 0, -1);
	} 

  // need to assign identity to author and reviewer, and may be they
  // are deleted users.
  $users_to_seek=array('author_id' => 'author' , 'reviewer_id' => 'reviewer');
  foreach($users_to_seek as $user_id => $user_for_humans)
  {
   	$myrow[$user_for_humans]= "";
  	if( !is_null($myrow[$user_id]) and intval($myrow[$user_id]) > 0 )
  	{
	    $user_data = 	getUserById($db,$myrow[$user_id]);
  		if( !is_null($user_data) )
  		{
  	  	$myrow[$user_for_humans]=$user_data[0]['fullname'];
}
    	else
    	{
      	$myrow[$user_for_humans]= "(" . $myrow[$user_id] . " - deleted user)";
  		}
  	}
  }
	return($myrow);
}












/** 
* function get converted TC title
*
* @var integer $id
* @var boolean [$convert]
* @return string TC Title
*
* 20050820 - fm
* refactoring call to getTestcase
*
*/
function getTestcaseTitle(&$db,$id, $convert = TRUE)
{
	$tc_data=getTestcase($db,$id, $convert);
	return ($tc_data['title']);
}

////////////////////////////////////////////////////////////////////////////////

/** 2. Functions to show test specification */

function showProduct(&$db,$id, $sqlResult = '', $sqlAction = 'update',$moddedItem = 0)
{
	$product = getProduct($db,$id);

	$smarty = new TLSmarty;
	$smarty->assign('modify_tc_rights', has_rights($db,"mgt_modify_tc"));

	if($sqlResult)
	{ 
		$smarty->assign('sqlResult', $sqlResult);
		$smarty->assign('sqlAction', $sqlAction);
	}
	$moddedItem = ($moddedItem  ? getProduct($db,$moddedItem) : $product);
	$smarty->assign('moddedItem',$moddedItem);
	$smarty->assign('level', 'product');
	$smarty->assign('container_data', $product);
	$smarty->display('containerView.tpl');
}


function showComponent(&$db,$id, $sqlResult = '', $sqlAction = 'update',$moddedItem = 0)
{
	// init smarty
	$smarty = new TLSmarty;
	$smarty->assign('modify_tc_rights', has_rights($db,"mgt_modify_tc"));

	if ($sqlResult)
	{ 
		$smarty->assign('sqlResult', $sqlResult);
		$smarty->assign('sqlAction', $sqlAction);
	}
	$smarty->assign('level', 'component');
	
	$component = getComponent($db,$id);
	
	$moddedItem = ($moddedItem  ? getComponent($db,$moddedItem) : $component);
	$smarty->assign('moddedItem',$moddedItem);
	
	$smarty->assign('container_data', $component);
	$smarty->display('containerView.tpl');
}


function showCategory(&$db,$id, $sqlResult = '', $sqlAction = 'update',$moddedItem = 0)
{
	$smarty = new TLSmarty;
	$smarty->assign('modify_tc_rights', has_rights($db,"mgt_modify_tc"));

	if($sqlResult)
	{ 	
		$smarty->assign('sqlResult', $sqlResult);
		$smarty->assign('sqlAction', $sqlAction);
	}
	$category = getCategory($db,$id);
	$moddedItem = ($moddedItem  ? getCategory($db,$moddedItem) : $category);
	$smarty->assign('moddedItem',$moddedItem);
	
	$smarty->assign('level', 'category');
	$smarty->assign('container_data', $category);
	$smarty->display('containerView.tpl');
}


/**
 * display testcase data include possibility of edit
 * 
 * @param integer id: test case id
 * @param boolean [allow_edit]: 1 = controls modify_tc_rights to enable/disable editing;
 *                 0 = disables editing;  default: 1 
 *     
 * @modified 20050829 - Martin Havlat - added REQ support            
 */
function showTestcase (&$db,$id,$allow_edit = 1)
{
	define('DO_NOT_CONVERT',false);
	global $g_tpl;
	
	$can_edit = 'no';
	if ($allow_edit)
	{
		$can_edit = has_rights($db,"mgt_modify_tc");
	}
	$myrowTC = getTestcase($db,$id,DO_NOT_CONVERT);
	$len = strlen($myrowTC['keywords'])-1;
	if (strrpos($myrowTC['keywords'],',') === $len)
	{
		//2005 - am - show keywords alphanumerically sorted
		$kwList = substr($myrowTC['keywords'],0,$len);
		if (strlen($kwList))
		{
			$kwList = explode(",",$kwList);
			asort($kwList);
			reset($kwList);
			$kwList = implode(",",$kwList);
		}
		$myrowTC['keywords'] = $kwList;
	}
	
	// get assigned REQs
	$arrReqs = getReq4Tc($db,$id);
	// 20050820 - fm
	$tc_array = array($myrowTC);
	
	$smarty = new TLSmarty;
	$smarty->assign('modify_tc_rights', $can_edit);
	$smarty->assign('testcase',$tc_array);
	$smarty->assign('arrReqs',$arrReqs);
	$smarty->assign('view_req_rights', has_rights($db,"mgt_view_req")); 
	$smarty->assign('opt_requirements', $_SESSION['productOptReqs']); 	
	// 20050821 - fm
	$smarty->display($g_tpl['tcView']);
}

/////////////////////////////////////////////////////////////////////////
/** 3. Functions for copy/move test specification */

function moveTc(&$db,$newCat, $id)
{
	$sql = "UPDATE mgttestcase SET catid=" . $newCat . " WHERE id=" . $id;
	$result = $db->exec_query($sql);

	return $result ?'ok' : $db->error_msg();
}

//20050821 - fm - inteface changes, added $user to reduce global coupling 
function copyTc(&$db,$newCat, $id, $user_id)
{
	$msg_status = 'ok';
	
	$tc = getTestcase($db,$id,false);
	
	if (!insertTestcase(&$db,$newCat,$tc['title'],$tc['summary'],
						$tc['steps'],$tc['exresult'],
						          $user_id,$tc['TCorder'],$tc['keywords']))
	{
		$msq_status=$db->error_msg();
	}	
	
	return ($msg_status);
}




function copyCategoryToComponent(&$db,$newParent, $id, $nested, $user_id)
{
	//Select the category info so that we can copy it
	$sqlCopyCat = " SELECT name,objective,config,data,tools,compid,CATorder,id " .
			          " FROM mgtcategory WHERE id=" . $id;
	$resultCopyCat = $db->exec_query($sqlCopyCat);
	$myrowCopyCat = $db->fetch_array($resultCopyCat);

	//Insert the category info
	$sqlInsertCat = "insert into mgtcategory (name,objective,config,data,tools," .
			"compid,CATorder) values ('" . $db->prepare_string($myrowCopyCat[0]) . 
			"','" . $db->prepare_string($myrowCopyCat[1]) . "','" . 
			$db->prepare_string($myrowCopyCat[2]) . "','" . 
			$db->prepare_string($myrowCopyCat[3]) . "','" . 
			$db->prepare_string($myrowCopyCat[4]) . "','" . $newParent . 
			"','" . $db->prepare_string($myrowCopyCat[6]) . "')";
	$resultInsertCAT = $db->exec_query($sqlInsertCat);
	
	// copy also test cases
	if ($nested == 'yes')
	{
		$catID =  $db->insert_id();

		$sqlMoveCopy= "select id from mgttestcase where catid='" . 
				$myrowCopyCat[7] . "'";
		$resultMoveCopy = $db->exec_query($sqlMoveCopy);
	
		//Insert nested test cases 
		while($myrowMoveCopy = $db->fetch_array($resultMoveCopy)) {
			
			// 20050821 - fm - interface changes
			copyTc($catID, $myrowMoveCopy[0], $user_id);
		}
	}

	if ($resultInsertCAT)
	{
		return 'ok';
   	}
	else
	{ 
		return $db->error_msg();
	}
}

function moveCategoryToComponent(&$db,$newParent, $id)
{
	$sql = "UPDATE mgtcategory SET compid=".$newParent." WHERE id=".$id;
	$result = $db->exec_query($sql);

	return $result ? 'ok': $db->error_msg();
}


// 20051129 - fm - added logic to check for existent name.
function moveComponentToProduct(&$db,$newParent, $comp_id)
{

  // 20051129 - fm
	$check_names_for_duplicates=config_get('check_names_for_duplicates');
  $upd_name_sql=' ';
	$do_update=TRUE;
	
  if( $check_names_for_duplicates )
  {
  	
  	$sql = "SELECT name 
            FROM mgtcomponent
            WHERE id=" . $comp_id;
  	
  	$result = $db->exec_query($sql);
	  $row = $db->fetch_array($result);
	  $my_name=$row['name'];

    $sql = " SELECT count(name) AS QTY_DUP 
             FROM mgtcomponent
             WHERE prodid = " . $newParent .
           " AND name='" . $my_name . "'";
    
    $result = $db->exec_query($sql);
	  $row = $db->fetch_array($result);
	
	  if( $row['QTY_DUP'] > 0 )
	  {
	     $action_on_duplicate_name=config_get('action_on_duplicate_name');
	     if ( $action_on_duplicate_name == "block" )
	     {
	       $do_update=FALSE;	
         $msg = lang_get('component_name_already_exists');
	     }
	     
	     if ( $action_on_duplicate_name == "generate_new" )
	     {
         $prefix_name_for_copy=config_get('prefix_name_for_copy');
         $upd_name_sql = " , name='" . $prefix_name_for_copy . " " . $my_name . "' "; 
	     }
	  }
  }

	if( $do_update )
	{
		$sql = "UPDATE mgtcomponent 
	          SET prodid=" . $newParent . $upd_name_sql .
	         " WHERE id=". $comp_id;
		$result = $db->exec_query($sql);
		
		$msg = $result ? 'ok' : $db->error_msg();
  }


	return ($msg);
}



// 20051208 - fm -
// 20051201 - fm - 
// BUGID 258 Management of Component Duplicate Name - block on copy gives no message to user
// 20051129 - fm - added logic to manage duplicate names
// 20050908 - fm due to changes in insertProductComponent()
//

function copyComponentToProduct(&$db,$newParent, $id, $nested, $user_id)

{
	// 20051129 - fm
	$check_names_for_duplicates=config_get('check_names_for_duplicates');
	$action_on_duplicate_name=config_get('action_on_duplicate_name');
  
	$component = getComponent($db,$id);

	$ret = insertProductComponent($db,$newParent,$component[1],$component[2],$component[3],
	                              $component[4],$component[5],$component[6],
	                              $check_names_for_duplicates,
	                              $action_on_duplicate_name);
	
	$comID = $ret['id'];
	if ($ret['status_ok'])
	{	
	  	// copy also categories
	  	if ($nested == 'yes')
	  	{
	  		// Select the categories for copy
	  		$catIDs = getComponentCategoryIDs($db,$id);
	  		$num_cats = sizeof($catIDs);
	  		for($i = 0; $i < $num_cats; $i++)
	  		{
	  			copyCategoryToComponent($db,$comID, $catIDs[$i], $nested, $user_id);
	  		}	
	  	}
	}	
	// 20051201 - fm
	return $ret['msg'];
}


/*
 20050910 - fm - correct (my) bug
 20050908 - fm - added possibility to check for existent name and refuse to insert
*/
function insertProductComponent(&$db,$prodID,$name,$intro,$scope,$ref,$method,$lim,
                                $check_duplicate_name=0,
                                $action_on_duplicate_name='allow_repeat')
{
	global $g_prefix_name_for_copy;
	
	$name = trim($name);
	$ret = array('status_ok' => 1, 'id' => 0, 'msg' => 'ok');
	if ($check_duplicate_name)
	{
		$sql = " SELECT count(*) AS qty FROM mgtcomponent " .
			" WHERE name = '" . $db->prepare_string($name) . "'" . 
			" AND prodid={$prodID} "; 
		$result = $db->exec_query($sql);
		$myrow = $db->fetch_array($result);
		
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
					$name = $g_prefix_name_for_copy . " " . $name ;      
				}
			}
		}       
	}
	
	if ($ret['status_ok'])
	{
		$sql = "INSERT INTO mgtcomponent (name,intro,scope,ref,method,lim,prodid) " .
		     	 "VALUES ('" . $db->prepare_string($name) . "','" . 
				 $db->prepare_string($intro) . "','" . 
			     $db->prepare_string($scope) . "','" . 
				 $db->prepare_string($ref) . "','" . $db->prepare_string($method) . 
			     "','" . $db->prepare_string($lim) . "'," . $prodID . ")";
			
		$result = $db->exec_query($sql);
		if ($result)
		{
			$ret['id'] = $db->insert_id();
		}
		else
		{
			$ret['msg'] = $db->error_msg();
		}
	}
	return($ret);
}



function insertComponentCategory(&$db,$compID,$name,$objective,$config,$testdata,$tools)
{
	$sql = "INSERT INTO mgtcategory (name,objective,config,data,tools,compid) " .
			"VALUES ('" . $db->prepare_string($name) . "','" . $db->prepare_string($objective). 
			"','" . $db->prepare_string($config) . "','" . $db->prepare_string($testdata) . "','" . 
			$db->prepare_string($tools) . "'," . $db->prepare_string($compID) . ")";
			
	$result = $db->exec_query($sql); 

	return $result ? $db->insert_id() : 0;
}

function updateComponent(&$db,$id,$name,$intro,$scope,$ref,$method,$lim)
{
	$sql = "UPDATE mgtcomponent set name ='" . $db->prepare_string($name) . "', intro ='" . 
		$db->prepare_string($intro) . "', scope='" . $db->prepare_string($scope) . "', ref='" . 
		$db->prepare_string($ref) . "', method='" . $db->prepare_string($method) . "', lim='" . 
		$db->prepare_string($lim) . "' where id=" . $id;
	
	$result = $db->exec_query($sql); //Execute query
	
	return $result ? 1 : 0;
}

function deleteComponent(&$db,$compID)
{
	$sql = "DELETE FROM mgtcomponent WHERE id=" . $compID;
	$result = $db->exec_query($sql);
	 
	return $result ? 1 : 0;
}


// 20051208 - fm 
// returns array with categories id
function getComponentCategoryIDs(&$db,$compID)
{
	$sql = "SELECT id FROM mgtcategory WHERE compid=" . $compID;
	$result = $db->exec_query($sql);
	$cat_ids = null;
	if ($result)
	{
		while($row = $db->fetch_array($result))
		{
			$cat_ids[] = $row['id'];
		}	
	}
	return($cat_ids);
}



function deleteComponentCategories(&$db,$compID)
{
	$sql = "DELETE FROM mgtcategory WHERE compID=".$compID;
	$result = $db->exec_query($sql);
	
	return $result ? 1 : 0;
}

function deleteCategoriesTestcases(&$db,$catIDs)
{
	$sql = "DELETE FROM mgttestcase WHERE mgttestcase.catid IN ({$catIDs})";
	$result = $db->exec_query($sql);

	return $result ? 1 : 0;
}






function getOrderedComponentCategories(&$db,$compID,&$cats)
{
	$sql = "SELECT id,name,CATorder FROM mgtcategory WHERE compid=" . 
			$compID . " ORDER BY CATorder,id";
	$result = $db->exec_query($sql);
	$cats = null;
	if ($result)
	{
		while($cat = $db->fetch_array($result))
			$cats[] = $cat;
	}
	return $result ? 1 : 0;
}


// 20051121 - fm - autogoal mgtid instead oif mgtcatid
// BUGID 0000236: unable to re-order categories in component
//
// 20051112 - fm
// to solve BUGID 000218 the CATOrder will be updated on category (testplan) also
//
function updateCategoryOrder(&$db,$catID,$order)
{
	
  /*  20051121 - fm - on 20051112 wrong logic   */   
	$sql = " UPDATE mgtcategory " .
	       " SET mgtcategory.CATorder=" . $order . 
	       " WHERE mgtcategory.id=" . $catID;
	$result = $db->exec_query($sql);
	       
	
	$sql = " UPDATE category, mgtcategory" .
	       " SET category.CATorder=" . $order . 
	       " WHERE mgtcategory.id=category.mgtcatid" .
	       " AND   mgtcategory.id=" . $catID;
	$result = $db->exec_query($sql);
	
	       
	
	
	return $result ? 1 : 0;
}

function deleteCategory(&$db,$catID)
{
	$sql = "DELETE FROM mgtcategory WHERE id=".$catID;
	$result = $db->exec_query($sql);
	
	return $result ? 1 : 0;
}

function updateCategory(&$db,$catID,$name,$objective,$config,$pdata,$tools)
{
	$sql = "UPDATE mgtcategory SET name ='" .$db->prepare_string($name) . 
		"', objective ='" . $db->prepare_string($objective). "', config='" . 
		$db->prepare_string($config). "', data='" . $db->prepare_string($pdata) . "', tools='" . 
		$db->prepare_string($tools). "' where id=" . $catID;
		
	$result = $db->exec_query($sql); //Execute query
}

function getOrderedCategoryTestcases(&$db,$catID,&$tcs)
{
	$sql = "SELECT id,title,TCorder FROM mgttestcase WHERE catid=".$catID." ORDER BY TCorder,id";
	$result = $db->exec_query($sql);
	$tcs = null;
	if ($result)
	{
		while($tc = $db->fetch_array($result))
			$tcs[] = $tc;
	}
	return $result ? 1 : 0;
}

function updateTestCaseOrder(&$db,$tcID,$order)
{
	$sql = "UPDATE mgttestcase SET TCorder=".$order." WHERE id=".$tcID;
	$result = $db->exec_query($sql); //Execute query
	
	return $result ? 1 : 0;
}

function getCategoryComponentAndProduct(&$db,$catID,&$compID,&$prodID)
{
	$sql = "SELECT compid, prodid FROM mgtcategory,mgtcomponent WHERE mgtcategory.id=" . 
	       $catID . " AND mgtcategory.compid=mgtcomponent.id";
	$result = $db->exec_query($sql);
	$compID = 0;
	$prodID = 0;
	if ($result)
	{
		if ($row = $db->fetch_array($result))
		{
			$compID = $row[0];
			$prodID = $row[1];
		}
	}
	return $result ? 1 : 0;
}
function getAllProductComponentsBut(&$db,$compID,$prodID,&$comps)
{
	$sql = "SELECT id, name FROM mgtcomponent WHERE prodid=" . $prodID . 
			" and id != " . $compID;
	$result = $db->exec_query($sql);
	
	$comps = null;
	if ($result)
	{
		while($row = $db->fetch_array($result))
			$comps[] = $row;
	}
	return $result ? 1 : 0;
}

function deleteTestcase(&$db,$tcID)
{
	$sql = "DELETE FROM mgttestcase WHERE id=" . $tcID;
	$result= $db->exec_query($sql);
	
	return $result ? 1 : 0;
}

// 20060108 - fm 
function insertTestcase(&$db,$catID,$title,$summary,$steps,
                        $outcome,$user_id,$tcOrder = null,$keywords = null)
{
	$more_sql = '';
	$more_values = '';
	
	$sql = "INSERT INTO mgttestcase 
	       (title,summary,steps,exresult, version,catid,create_date";

  $values =	") values ('" . $db->prepare_string($title)   . "','" . 
			                      $db->prepare_string($summary) . "','" . 
			                      $db->prepare_string($steps)   . "','" .	
			                      $db->prepare_string($outcome) . "',1," . $catID . 
			                      ", CURRENT_DATE()";
  // ----------------------------------------------------------
	if (!is_null($user_id))
	{
	  $sql .= ",author_id";
		$values .= "," . $user_id;
	}

  if (!is_null($tcOrder))
  {
		$sql    .= ",TCorder";
		$values .= ",".$tcOrder;
  }
  
	if (!is_null($keywords) && strlen($keywords))
	{
		$sql .= ",keywords";
		$values .= ",'".$db->prepare_string($keywords)."'";
	}
	// ----------------------------------------------------------

  
  $sql .= $values . ")";
	$result = $db->exec_query($sql);
	
	return $result ? $db->insert_id() : 0;
}

// 20060108 - fm  - reviewer_id 
// 20050819 - scs - fix for bug Mantis 59 Use of term "created by" is not enforced---
function updateTestcase(&$db,$tcID,$title,$summary,$steps,$outcome,$user_id,$keywords,$version)
{
	
	$sql = "UPDATE mgttestcase SET keywords='" . 
	        $db->prepare_string($keywords) . "', version='" . $db->prepare_string($version) . 
	        "', title='" . $db->prepare_string($title) . "'".
		      ",summary='" . $db->prepare_string($summary) . "', steps='" . 
	      	$db->prepare_string($steps) . "', exresult='" . $db->prepare_string($outcome) . 
		      "', reviewer_id=" . $user_id . ", modified_date=CURRENT_DATE()" .
		      " WHERE id=" . $tcID;
	$result = $db->exec_query($sql);
	
	return $result ? 1: 0;
}


function getTestCaseCategoryAndComponent(&$db,$tcID,&$catID,&$compID)
{
	$sql = "SELECT catid, compid FROM mgttestcase,mgtcategory WHERE mgttestcase.id=" . $tcID . " AND mgttestcase.catid=mgtcategory.id";
	$result = $db->exec_query($sql);
	$catID = 0;
	$compID = 0;
	if ($result)
	{
		if ($row = $db->fetch_array($result))
		{
			$catID = $row[0];
			$compID = $row[1];
		}
	}
	return $result ? 1 : 0;
}
function getOptionCategoriesOfComponent(&$db,$compID,&$comps)
{
	$sql = "SELECT id, name FROM mgtcategory WHERE compid=".$compID;	
	
	$result = $db->exec_query($sql);
	$comps = null;
	if ($result)
	{
		while($row = $db->fetch_array($result))
			$comps[$row[0]] = $row[1];
	}		
	
	return $result ? 1 : 0;
}
?>
