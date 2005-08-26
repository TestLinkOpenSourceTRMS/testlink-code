<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: archiveData.php,v 1.3 2005/08/26 21:01:27 schlundus Exp $ */
/* Purpose:  This page allows you to show data (test cases, categories, and
 *         components. This is refered by tree.
*/
require_once("../../config.inc.php");
require_once("../functions/common.php");
require('archive.inc.php');
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();

// parse input
$feature = isset($_GET['edit']) ? $_GET['edit'] : null;
$id = isset($_GET['data']) ? intval($_GET['data']) : null;
//20050826 - scs - added input for entering tcid, but we should disable edit...
$allow_edit = isset($_GET['allow_edit']) ? intval($_GET['allow_edit']) : 1;

// load data and show template
switch($feature)
{
	case 'product':
		showProduct($id);
		break;
 	case 'component':
		showComponent($id);
		break;
    case 'category':
		showCategory($id);
		break;
	case 'testcase':
		showTestcase($id,$allow_edit);	
		break;
	default:
		trigger_error($_SESSION['user']."> GET argument 'edit' is wrong.", E_USER_ERROR);
}
?>
