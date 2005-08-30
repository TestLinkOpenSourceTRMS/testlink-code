<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @version $Id: archiveData.php,v 1.4 2005/08/30 09:17:47 havlat Exp $
 * @author Martin Havlat
 *  
 * This page allows you to show data (test cases, categories, and
 * components. This is refered by tree.
 * 
 * 20050830 - MHT - formal update
 */
 
require_once('../../config.inc.php');
require_once('common.php');
require_once('archive.inc.php');

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
		tLog('$_GET["edit"] has invalid value: ' . $feature , 'ERROR');
		trigger_error($_SESSION['user'].'> $_GET["edit"] has invalid value.', E_USER_ERROR);
}

?>
