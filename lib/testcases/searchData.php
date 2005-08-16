<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: searchData.php,v 1.2 2005/08/16 18:00:59 franciscom Exp $ */
/* Purpose:  This page presents the search results. */
/* 
 * @ author: francisco mancardi - 20050810
 * deprecated $_SESSION['product'] removed
 */
require('../../config.inc.php');
require("../functions/common.php");
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();

$arrTc = array();
if(!$_POST['submit'])
	tlog('searchData.php requires a submit data');

//Assign the values of the posts to variables
$title = isset($_POST['title']) ? mysql_escape_string(strings_stripSlashes($_POST['title'])) : null;
$summary = isset($_POST['summary']) ? mysql_escape_string(strings_stripSlashes($_POST['summary'])) : null;
$steps = isset($_POST['steps']) ? mysql_escape_string(strings_stripSlashes($_POST['steps'])) : null;
$exresult = isset($_POST['exresult']) ? mysql_escape_string(strings_stripSlashes($_POST['exresult'])) : null;
$key = isset($_POST['key']) ? mysql_escape_string(strings_stripSlashes($_POST['key'])) : null;
$TCID = isset($_POST['TCID']) ? mysql_escape_string(strings_stripSlashes($_POST['TCID'])) : 0;

$product = isset($_SESSION['productID']) ? $_SESSION['productID'] : 0;
if ($product)
{
	$sqlTC = "SELECT mgttestcase.id,title,summary,steps,exresult,keywords,version FROM mgttestcase,mgtcategory,		mgtcomponent WHERE prodid = ".$product.
 			 " AND mgtcategory.compID = mgtcomponent.id AND mgttestcase.catID = mgtcategory.id AND mgttestcase.id like '%" . 	$TCID . 
			 "%' AND title like '%" . $title . "%' AND summary like '%" . $summary . "%' AND steps like '%" . $steps . 
			 "%' AND exresult like '%" . $exresult."%'";
	//keywordlist always have a trailing slash, so there are only two cases to consider the keyword is the first in the 	list
	//or its in the middle of list 		 
	if($key != 'none')
		$sqlTC .= " AND (keywords LIKE '%,{$key},%' OR keywords like '{$key},%')";
	$sqlTC .= " ORDER BY title";

	$result = do_mysql_query($sqlTC);
	while ($row = mysql_fetch_row($result)) //loop through all categories
	{
		array_push($arrTc, array( 	'id' => $row[0],
									'title' => $row[1], 
									'summary' => $row[2],
									'steps' => $row[3], 
									'expected' => $row[4],
									'keys' => substr($row[5], 0, -1)));
	}
}
if (!sizeof($arrTc))
	$arrTc = null;

$smarty = new TLSmarty;
if(has_rights("mgt_modify_tc"))
	$smarty->assign('modify_tc_rights', 'yes');
$smarty->assign('arrTc', $arrTc);
$smarty->display('tcSearchView.tpl');
?>
