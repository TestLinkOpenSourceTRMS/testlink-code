<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: archive.inc.php,v $
 *
 * @version $Revision: 1.18 $
 * @modified $Date: 2005/10/10 19:18:25 $ by $Author: schlundus $
 *
 * @author Martin Havlat
 * Purpose:  functions for test specification management have three parts:
 *		1. grab data from db
 *		2. show test specification
 *		3. copy/move data within test specification         
 *
 * @todo deactive users???? instead of delete
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

/** 1. functions for grab container and test case data from database */ 

function getComponent($id)
{
	$sqlCOM = "SELECT id,name,intro,scope,ref,method,lim FROM mgtcomponent " .
			      "WHERE id=" . $id;
	$resultCOM = do_mysql_query($sqlCOM);

	return mysql_fetch_array($resultCOM);
}

function getCategory($id)
{
	$sql = "SELECT id,name,objective,config,data,tools,compid FROM mgtcategory " .
			   "WHERE id=" . $id;
	$result = do_mysql_query($sql);

	return mysql_fetch_array($result);
}


/** 
* function get all TC data and convert for view
*
* @var integer $id
* @var bool $convert TRUE is default
*/
function getTestcase($id, $convert = TRUE)
{
	// execute SQL request
	$sqlTC = " select id,title,summary,steps,exresult,version,keywords," .
			     " author,create_date,reviewer,modified_date,catid,TCorder " .
			     " from mgttestcase" .
			     " where id=" . $id ;
			     
	$resultTC = do_mysql_query($sqlTC);
	$myrowTC = mysql_fetch_array($resultTC);
	
	if ($convert)
	{
		// 20050820 - fm - refactoring
		$a_keys = array('title','summary','steps','exresult');

		// prepare data
	    foreach($a_keys as $field_name)
	    {
	    	$myrowTC[$field_name] = stripslashes($myrowTC[$field_name]);
		}
			
		//Chop the trailing comma off of the end of field
		$myrowTC['keywords'] = substr($myrowTC['keywords'], 0, -1);
	} 

	return $myrowTC;
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
function getTestcaseTitle($id, $convert = TRUE)
{
	$tc_data=getTestcase($id, $convert);
	return ($tc_data['title']);
}

////////////////////////////////////////////////////////////////////////////////

/** 2. Functions to show test specification */

function showProduct($id, $sqlResult = '', $sqlAction = 'update',$moddedItem = 0)
{
	$product = getProduct($id);

	$smarty = new TLSmarty;
	$smarty->assign('modify_tc_rights', has_rights("mgt_modify_tc"));

	if($sqlResult)
	{ 
		$smarty->assign('sqlResult', $sqlResult);
		$smarty->assign('sqlAction', $sqlAction);
	}
	$moddedItem = ($moddedItem  ? getProduct($moddedItem) : $product);
	$smarty->assign('moddedItem',$moddedItem);
	$smarty->assign('level', 'product');
	$smarty->assign('container_data', $product);
	$smarty->display('containerView.tpl');
}


function showComponent($id, $sqlResult = '', $sqlAction = 'update',$moddedItem = 0)
{
	// init smarty
	$smarty = new TLSmarty;
	$smarty->assign('modify_tc_rights', has_rights("mgt_modify_tc"));

	if ($sqlResult)
	{ 
		$smarty->assign('sqlResult', $sqlResult);
		$smarty->assign('sqlAction', $sqlAction);
	}
	$smarty->assign('level', 'component');
	
	$component = getComponent($id);
	
	$moddedItem = ($moddedItem  ? getComponent($moddedItem) : $component);
	$smarty->assign('moddedItem',$moddedItem);
	
	$smarty->assign('container_data', $component);
	$smarty->display('containerView.tpl');
}


function showCategory($id, $sqlResult = '', $sqlAction = 'update',$moddedItem = 0)
{
	$smarty = new TLSmarty;
	$smarty->assign('modify_tc_rights', has_rights("mgt_modify_tc"));

	if($sqlResult)
	{ 	
		$smarty->assign('sqlResult', $sqlResult);
		$smarty->assign('sqlAction', $sqlAction);
	}
	$category = getCategory($id);
	$moddedItem = ($moddedItem  ? getCategory($moddedItem) : $category);
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
function showTestcase ($id,$allow_edit = 1)
{
	define('DO_NOT_CONVERT',false);
	global $g_tpl;
	
	$can_edit = 'no';
	if ($allow_edit)
	{
		$can_edit = has_rights("mgt_modify_tc");
	}
	$myrowTC = getTestcase($id,DO_NOT_CONVERT);
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
	$arrReqs = getReq4Tc($id);
	// 20050820 - fm
	$tc_array = array($myrowTC);
	
	$smarty = new TLSmarty;
	$smarty->assign('modify_tc_rights', $can_edit);
	$smarty->assign('testcase',$tc_array);
	$smarty->assign('arrReqs',$arrReqs);
	$smarty->assign('view_req_rights', has_rights("mgt_view_req")); 
	$smarty->assign('opt_requirements', $_SESSION['productOptReqs']); 	
	// 20050821 - fm
	$smarty->display($g_tpl['tcView']);
}

/////////////////////////////////////////////////////////////////////////
/** 3. Functions for copy/move test specification */

function moveTc($newCat, $id)
{
	$sql = "UPDATE mgttestcase SET catid=" . $newCat . " WHERE id=" . $id;
	$result = do_mysql_query($sql);

	return $result ?'ok' : mysql_error();
}

//20050821 - fm - inteface changes, added $user to reduce global coupling 
function copyTc($newCat, $id, $user)
{
	$msg_status = 'ok';
	
	$tc = getTestcase($id,false);
	
	if (!insertTestcase($newCat,$tc['title'],$tc['summary'],
						$tc['steps'],$tc['exresult'],
						$user,$tc['TCorder'],$tc['keywords']))
	{
		$msq_status=mysql_error();
	}	
	
	return ($msg_status);
}

function copyCategoryToComponent($newParent, $id, $nested, $login_name)
{
	//Select the category info so that we can copy it
	$sqlCopyCat = " SELECT name,objective,config,data,tools,compid,CATorder,id " .
			          " FROM mgtcategory WHERE id=" . $id;
	$resultCopyCat = do_mysql_query($sqlCopyCat);
	$myrowCopyCat = mysql_fetch_row($resultCopyCat);

	//Insert the category info
	$sqlInsertCat = "insert into mgtcategory (name,objective,config,data,tools," .
			"compid,CATorder) values ('" . mysql_escape_string($myrowCopyCat[0]) . 
			"','" . mysql_escape_string($myrowCopyCat[1]) . "','" . 
			mysql_escape_string($myrowCopyCat[2]) . "','" . 
			mysql_escape_string($myrowCopyCat[3]) . "','" . 
			mysql_escape_string($myrowCopyCat[4]) . "','" . $newParent . 
			"','" . mysql_escape_string($myrowCopyCat[6]) . "')";
	$resultInsertCAT = do_mysql_query($sqlInsertCat);
	
	// copy also test cases
	if ($nested == 'yes')
	{
		$catID =  mysql_insert_id();

		$sqlMoveCopy= "select id from mgttestcase where catid='" . 
				$myrowCopyCat[7] . "'";
		$resultMoveCopy = do_mysql_query($sqlMoveCopy);
	
		//Insert nested test cases 
		while($myrowMoveCopy = mysql_fetch_row($resultMoveCopy)) {
			
			// 20050821 - fm - interface changes
			copyTc($catID, $myrowMoveCopy[0], $login_name);
		}
	}

	if ($resultInsertCAT)
	{
		return 'ok';
   	}
	else
	{ 
		return mysql_error();
	}
}

function moveCategoryToComponent($newParent, $id)
{
	$sql = "UPDATE mgtcategory SET compid=".$newParent." WHERE id=".$id;
	$result = do_mysql_query($sql);

	return $result ? 'ok': mysql_error();
}

function moveComponentToProduct($newParent, $id)
{
	$sql = "UPDATE mgtcomponent SET prodid=".$newParent." WHERE id=".$id;
	$result = do_mysql_query($sql);

	return $result ? 'ok' : mysql_error();
}

// 20050908 - fm due to changes in insertProductComponent()
function copyComponentToProduct($newParent, $id, $nested, $login_name)
{
	$component = getComponent($id);

	$ret = insertProductComponent($newParent,$component[1],$component[2],$component[3],
	                              $component[4],$component[5],$component[6]);
	
	$comID = $ret['id'];
	if ($ret['status_ok'])
	{	
	  	// copy also categories
	  	if ($nested == 'yes')
	  	{
	  		// Select the categories for copy
	  		$catIDs = null;
	  		getComponentCategoryIDs($id,$catIDs);
	  		for($i = 0;$i < sizeof($catIDs);$i++)
	  		{
	  			copyCategoryToComponent($comID, $catIDs[$i], $nested, $login_name);
	  		}	
	  	}
	}	
	return $comID ? 'ok' : mysql_error();
}


/*
 20050910 - fm - correct (my) bug
 20050908 - fm - added possibility to check for existent name and refuse to insert
*/
function insertProductComponent($prodID,$name,$intro,$scope,$ref,$method,$lim,
                                $check_duplicate_name=0,
                                $action_on_duplicate_name='allow_repeat')
{
	global $g_prefix_name_for_copy;
	
	$name = trim($name);
	$ret = array('status_ok' => 1, 'id' => 0, 'msg' => 'ok');
	if ($check_duplicate_name)
	{
		$sql = " SELECT count(*) AS qty FROM mgtcomponent " .
			" WHERE name = '" . mysql_escape_string($name) . "'" . 
			" AND prodid={$prodID} "; 
		$result = do_mysql_query($sql);
		$myrow = mysql_fetch_assoc($result);
		
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
		     	 "VALUES ('" . mysql_escape_string($name) . "','" . 
				 mysql_escape_string($intro) . "','" . 
			     mysql_escape_string($scope) . "','" . 
				 mysql_escape_string($ref) . "','" . mysql_escape_string($method) . 
			     "','" . mysql_escape_string($lim) . "'," . $prodID . ")";
			
		$result = do_mysql_query($sql);
		if ($result)
		{
			$ret['id'] = mysql_insert_id();
		}
		else
		{
			$ret['msg'] = mysql_error();
		}
	}
	return($ret);
}



function insertComponentCategory($compID,$name,$objective,$config,$testdata,$tools)
{
	$sql = "INSERT INTO mgtcategory (name,objective,config,data,tools,compid) " .
			"VALUES ('" . mysql_escape_string($name) . "','" . mysql_escape_string($objective). 
			"','" . mysql_escape_string($config) . "','" . mysql_escape_string($testdata) . "','" . 
			mysql_escape_string($tools) . "'," . mysql_escape_string($compID) . ")";
			
	$result = do_mysql_query($sql); 

	return $result ? mysql_insert_id() : 0;
}

function updateComponent($id,$name,$intro,$scope,$ref,$method,$lim)
{
	$sql = "UPDATE mgtcomponent set name ='" . mysql_escape_string($name) . "', intro ='" . 
		mysql_escape_string($intro) . "', scope='" . mysql_escape_string($scope) . "', ref='" . 
		mysql_escape_string($ref) . "', method='" . mysql_escape_string($method) . "', lim='" . 
		mysql_escape_string($lim) . "' where id=" . $id;
	
	$result = do_mysql_query($sql); //Execute query
	
	return $result ? 1 : 0;
}

function deleteComponent($compID)
{
	$sql = "DELETE FROM mgtcomponent WHERE id=" . $compID;
	$result = do_mysql_query($sql);
	 
	return $result ? 1 : 0;
}

function getComponentCategoryIDs($compID,&$cats)
{
	$sql = "SELECT id FROM mgtcategory WHERE compid=" . $compID;
	$result = do_mysql_query($sql);
	$cats = null;
	if ($result)
	{
		while($cat = mysql_fetch_row($result))
			$cats[] = $cat[0];
	}
	return $result ? 1: 0;
}

function deleteComponentCategories($compID)
{
	$sql = "DELETE FROM mgtcategory WHERE compID=".$compID;
	$result = do_mysql_query($sql);
	
	return $result ? 1 : 0;
}

function deleteCategoriesTestcases($catIDs)
{
	$sql = "DELETE FROM mgttestcase WHERE mgttestcase.catid IN ({$catIDs})";
	$result = do_mysql_query($sql);

	return $result ? 1 : 0;
}

function getOrderedComponentCategories($compID,&$cats)
{
	$sql = "SELECT id,name,CATorder FROM mgtcategory WHERE compid=" . 
			$compID . " ORDER BY CATorder,id";
	$result = do_mysql_query($sql);
	$cats = null;
	if ($result)
	{
		while($cat = mysql_fetch_array($result))
			$cats[] = $cat;
	}
	return $result ? 1 : 0;
}

function updateCategoryOrder($catID,$order)
{
	$sql = "UPDATE mgtcategory SET CATorder=" . $order . " WHERE id=" . $catID;
	$result = do_mysql_query($sql);
	
	return $result ? 1 : 0;
}

function deleteCategory($catID)
{
	$sql = "DELETE FROM mgtcategory WHERE id=".$catID;
	$result = do_mysql_query($sql);
	
	return $result ? 1 : 0;
}

function updateCategory($catID,$name,$objective,$config,$pdata,$tools)
{
	$sql = "UPDATE mgtcategory SET name ='" .mysql_escape_string($name) . 
		"', objective ='" . mysql_escape_string($objective). "', config='" . 
		mysql_escape_string($config). "', data='" . mysql_escape_string($pdata) . "', tools='" . 
		mysql_escape_string($tools). "' where id=" . $catID;
		
	$result = do_mysql_query($sql); //Execute query
}

function getOrderedCategoryTestcases($catID,&$tcs)
{
	$sql = "SELECT id,title,TCorder FROM mgttestcase WHERE catid=".$catID." ORDER BY TCorder,id";
	$result = do_mysql_query($sql);
	$tcs = null;
	if ($result)
	{
		while($tc = mysql_fetch_array($result))
			$tcs[] = $tc;
	}
	return $result ? 1 : 0;
}

function updateTestCaseOrder($tcID,$order)
{
	$sql = "UPDATE mgttestcase SET TCorder=".$order." WHERE id=".$tcID;
	$result = do_mysql_query($sql); //Execute query
	
	return $result ? 1 : 0;
}

function getCategoryComponentAndProduct($catID,&$compID,&$prodID)
{
	$sql = "SELECT compid, prodid FROM mgtcategory,mgtcomponent WHERE mgtcategory.id=" . $catID . " AND mgtcategory.compid=mgtcomponent.id";
	$result = do_mysql_query($sql);
	$compID = 0;
	$prodID = 0;
	if ($result)
	{
		if ($row = mysql_fetch_row($result))
		{
			$compID = $row[0];
			$prodID = $row[1];
		}
	}
	return $result ? 1 : 0;
}
function getAllProductComponentsBut($compID,$prodID,&$comps)
{
	$sql = "SELECT id, name FROM mgtcomponent WHERE prodid=" . $prodID . 
			" and id != " . $compID;
	$result = do_mysql_query($sql);
	
	$comps = null;
	if ($result)
	{
		while($row = mysql_fetch_row($result))
			$comps[] = $row;
	}
	return $result ? 1 : 0;
}

function deleteTestcase($tcID)
{
	$sql = "DELETE FROM mgttestcase WHERE id=" . $tcID;
	$result= do_mysql_query($sql);
	
	return $result ? 1 : 0;
}

function insertTestcase($catID,$title,$summary,$steps,$outcome,$user,$tcOrder = null,$keywords = null)
{
	if(!strlen($user))
		$user = 'n/a';
	
	$sql = "INSERT INTO mgttestcase (title,author,summary,steps,exresult," .
			"version,catid,create_date";
	if (!is_null($tcOrder))
		$sql .= ",TCorder";
	if (!is_null($keywords) && strlen($keywords))
		$sql .= ",keywords";
			
	$sql .=	 ") values ('" . mysql_escape_string($title) . "','" . 
			mysql_escape_string($user) . "','" . mysql_escape_string($summary) . "','" . mysql_escape_string($steps) . 
			"','" .	mysql_escape_string($outcome) . "',1," . $catID . 
			", CURRENT_DATE()";
			
	if (!is_null($tcOrder))
		$sql .= ",".$tcOrder;
	if (!is_null($keywords) && strlen($keywords))
		$sql .= ",'".mysql_escape_string($keywords)."'";
	
	$sql .= ")";
	$result = do_mysql_query($sql);
	
	return $result ? mysql_insert_id() : 0;
}

// 20050819 - scs - fix for bug Mantis 59 Use of term "created by" is not enforced---
function updateTestcase($tcID,$title,$summary,$steps,$outcome,$user,$keywords,$version)
{
	$sql = "UPDATE mgttestcase SET keywords='" . mysql_escape_string($keywords) . "', version='" . 
		mysql_escape_string($version) . "', title='" . mysql_escape_string($title) . "'".
		",summary='" . mysql_escape_string($summary) . "', steps='" . 
		mysql_escape_string($steps) . "', exresult='" . mysql_escape_string($outcome) . 
		"', reviewer='" . mysql_escape_string($user) . "', modified_date=CURRENT_DATE()" .
		" WHERE id=" . $tcID;
	$result = do_mysql_query($sql);
	
	return $result ? 1: 0;
}

function getTestCaseCategoryAndComponent($tcID,&$catID,&$compID)
{
	$sql = "SELECT catid, compid FROM mgttestcase,mgtcategory WHERE mgttestcase.id=" . $tcID . " AND mgttestcase.catid=mgtcategory.id";
	$result = do_mysql_query($sql);
	$catID = 0;
	$compID = 0;
	if ($result)
	{
		if ($row = mysql_fetch_row($result))
		{
			$catID = $row[0];
			$compID = $row[1];
		}
	}
	return $result ? 1 : 0;
}
function getOptionCategoriesOfComponent($compID,&$comps)
{
	$sql = "SELECT id, name FROM mgtcategory WHERE compid=".$compID;	
	
	$result = do_mysql_query($sql);
	$comps = null;
	if ($result)
	{
		while($row = mysql_fetch_row($result))
			$comps[$row[0]] = $row[1];
	}		
	
	return $result ? 1 : 0;
}
?>
