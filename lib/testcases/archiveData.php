<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: archiveData.php,v 1.2 2005/08/16 18:00:59 franciscom Exp $ */
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
		showTestcase($id);	
		break;
	default:
		trigger_error($_SESSION['user']."> GET argument 'edit' is wrong.", E_USER_ERROR);
}
?>
