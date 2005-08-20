<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: archive.inc.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2005/08/20 18:39:13 $
 *
 * @author Martin Havlat
 * Purpose:  functions for test specification management have three parts:
 *		1. grab data from db
 *		2. show test specification
 *		3. copy/move data within test specification         
 *
 * @todo deactive users instead of delete
 *
**/

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
	$sqlTC = "select id,title,summary,steps,exresult,version,keywords," .
			"author,create_date,reviewer,modified_date,catid,TCorder from mgttestcase " .
			"where id=" . $id ;
	$resultTC = do_mysql_query($sqlTC);
	$myrowTC = mysql_fetch_array($resultTC);
	
	if ($convert)
	{
		// prepare data
		for ($i = 2; $i <= 4; $i++)
			$myrowTC[$i] = stripslashes($myrowTC[$i]);

		//Chop the trailing comma off of the end of the keywords field
		$myrowTC[6] = substr($myrowTC[6], 0, -1);
	} 

	return $myrowTC;
}

/** 
* function get converted TC title
*
* @var integer $id
* @return string TC Title
*/
function getTestcaseTitle($id, $convert = TRUE)
{
	// execute SQL request
	$sqlTC = "SELECT title FROM mgttestcase WHERE id=" . $id ;
	$resultTC = do_mysql_query($sqlTC);
	$myrowTC = mysql_fetch_array($resultTC);
	
	return stripslashes($myrowTC[0]);
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
		// something was updated
		$smarty->assign('sqlResult', $sqlResult);
		$smarty->assign('sqlAction', $sqlAction);
	}
	$moddedItem = ($moddedItem  ? getProduct($moddedItem) : $product);
	$smarty->assign('moddedItem',$moddedItem);
	$smarty->assign('level', 'product');
	$smarty->assign('data', $product);
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
	// get data
	$smarty->assign('data', $component);
	$smarty->display('containerView.tpl');
}

function showCategory($id, $sqlResult = '', $sqlAction = 'update',$moddedItem = 0)
{
	$smarty = new TLSmarty;
	$smarty->assign('modify_tc_rights', has_rights("mgt_modify_tc"));

	if($sqlResult)
	{ 	
		// something was updated
		$smarty->assign('sqlResult', $sqlResult);
		$smarty->assign('sqlAction', $sqlAction);
	}
	$category = getCategory($id);
	$moddedItem = ($moddedItem  ? getCategory($moddedItem) : $category);
	$smarty->assign('moddedItem',$moddedItem);
	
	$smarty->assign('level', 'category');
	$smarty->assign('data', $category);
	$smarty->display('containerView.tpl');
}


/** function display testcase data include possibility of edit */
function showTestcase($id)
{
	$myrowTC = getTestcase($id,false);

	$len = strlen($myrowTC[6])-1;
	if (strrpos($myrowTC[6],',') === $len)
		$myrowTC[6] = substr($myrowTC[6],0,$len);
	
	$smarty = new TLSmarty;
	$smarty->assign('modify_tc_rights', has_rights("mgt_modify_tc"));
	$smarty->assign('testcase', $myrowTC);
	$smarty->display('tcView.tpl');
}


/////////////////////////////////////////////////////////////////////////
/** 3. Functions for copy/move test specification */


function moveTc($newCat, $id)
{
	$sql = "UPDATE mgttestcase SET catid=" . $newCat . " WHERE id=" . $id;
	$result = do_mysql_query($sql);

	return $result ?'ok' : mysql_error();
}

function copyTc($newCat, $id)
{
	$tc = getTestcase($id,false);

	$sqlTC = "SELECT id,title,summary,steps,exresult,version,keywords," .
			"author,create_date,reviewer,modified_date,catid,TCorder FROM mgttestcase " .
			"WHERE id=" . $id ;
	if (insertTestcase($newCat,$tc[1],$tc[2],$tc[3],$tc[4],$_SESSION['user'],$tc[12],$tc[6]))
		return 'ok';
   	else
		return mysql_error();
}

function copyCategoryToComponent($newParent, $id, $nested)
{
	//Select the category info so that we can copy it
	$sqlCopyCat = "select name,objective,config,data,tools,compid,CATorder,id " .
			"from mgtcategory where id='" . $id . "'";
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
		//grab the category id so that we can use it as the foreign key
		$catID =  mysql_insert_id(); //Grab the id of the category just entered

		$sqlMoveCopy= "select id from mgttestcase where catid='" . 
				$myrowCopyCat[7] . "'";
		$resultMoveCopy = do_mysql_query($sqlMoveCopy);
	
		//Insert nested test cases 
		while($myrowMoveCopy = mysql_fetch_row($resultMoveCopy)) {
			copyTc($catID, $myrowMoveCopy[0]);
		}
	}

	if ($resultInsertCAT) {
		return 'ok';
   	} else { 
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

function copyComponentToProduct($newParent, $id, $nested)
{
	$component = getComponent($id);
	$comID = insertProductComponent($newParent,$component[1],$component[2],$component[3],$component[4],$component[5],$component[6]);
	// copy also categories
	if ($nested == 'yes')
	{
		// Select the categories for copy
		$catIDs = null;
		getComponentCategoryIDs($id,$catIDs);
		for($i = 0;$i < sizeof($catIDs);$i++)
			copyCategoryToComponent($comID, $catIDs[$i], $nested);
	}
	return $comID ? 'ok' : mysql_error();
}

function insertProductComponent($prodID,$name,$intro,$scope,$ref,$method,$lim)
{
	$sql = "INSERT INTO mgtcomponent (name,intro,scope,ref,method,lim,prodid) " .
			"VALUES ('" . mysql_escape_string($name) . "','" . mysql_escape_string($intro) . "','" . 
			mysql_escape_string($scope) . "','" . mysql_escape_string($ref) . "','" . mysql_escape_string($method) . 
			"','" . mysql_escape_string($lim) . "'," . $prodID . ")";
			
	$result = do_mysql_query($sql);
	
	return $result ? mysql_insert_id() : 0;
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
// 20050819 - am - fix for bug Mantis 59 Use of term "created by" is not enforced---
function updateTestcase($tcID,$title,$summary,$steps,$outcome,$user,$keywords,$version)
{
	$sql = "UPDATE mgttestcase SET keywords='" . mysql_escape_string($keywords) . "', version='" . 
		mysql_escape_string($version) . "', title='" . mysql_escape_string($title) . "'".
		",summary='" . mysql_escape_string($summary) . "', steps='" . 
		mysql_escape_string($steps) . "', exresult='" . mysql_escape_string($outcome) . 
		"', reviewer='" . mysql_escape_string($user) . "', modified_date=CURRENT_DATE()" .
		" WHERE id=" . $tcID;
	$result = do_mysql_query($sql); //Execute query
	
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
