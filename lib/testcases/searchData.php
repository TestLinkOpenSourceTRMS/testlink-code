<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: searchData.php,v 1.10 2006/01/09 08:35:00 franciscom Exp $
 * Purpose:  This page presents the search results. 
 *
 * 20050821 - fm - changes to use template customization (trying to reduce code redundancy)
 * 20050810 - fm - removed deprecated $_SESSION['product'] 
**/
require('../../config.inc.php');
require("../functions/common.php");
testlinkInitPage($db);

$_POST = strings_stripSlashes($_POST);
//Assign the values of the posts to variables
$title = isset($_POST['title']) ? $db->prepare_string($_POST['title']) : null;
$summary = isset($_POST['summary']) ? $db->prepare_string($_POST['summary']) : null;
$steps = isset($_POST['steps']) ? $db->prepare_string($_POST['steps']) : null;
$exresult = isset($_POST['exresult']) ? $db->prepare_string($_POST['exresult']) : null;
$key = isset($_POST['key']) ? $db->prepare_string($_POST['key']) : null;
$TCID = isset($_POST['TCID']) ? $db->prepare_string($_POST['TCID']) : 0;

$arrTc = null;
$product = isset($_SESSION['productID']) ? $_SESSION['productID'] : 0;
if ($product)
{
	$sqlTC = " SELECT mgttestcase.id,title,summary,steps,exresult,keywords,version," .
	         " author,create_date,reviewer,modified_date,catid,TCorder " .
	         " FROM mgttestcase, mgtcategory,	mgtcomponent " .
	         " WHERE prodid = ".$product.
 			     " AND mgtcategory.compID = mgtcomponent.id " .
 			     " AND mgttestcase.catID = mgtcategory.id " .
 			     " AND mgttestcase.id like '%" . 	$TCID . "%' " .
 			     " AND title like '%" . $title . "%' " .
 			     " AND summary like '%" . $summary . "%' " . 
 			     " AND steps like '%" . $steps . "%' " .
 			     " AND exresult like '%" . $exresult."%'";

	//keywordlist always have a trailing comma, so there are only two cases 
	//to consider the keyword is the first in the list, or its in the middle of list 		 
	if($key != 'none')
		$sqlTC .= " AND (keywords LIKE '%,{$key},%' OR keywords like '{$key},%')";

	$sqlTC .= " ORDER BY title";
	$result = $db->exec_query($sqlTC);
	while ($row = $db->fetch_array($result))
	{
		$row['keywords'] = substr($row['keywords'], 0, -1);
		$arrTc[] = $row;
	}
}
$smarty = new TLSmarty();
$smarty->assign('modify_tc_rights', has_rights($db,"mgt_modify_tc"));
$smarty->assign('testcase', $arrTc);
$smarty->display($g_tpl['tcSearchView']);
?>
