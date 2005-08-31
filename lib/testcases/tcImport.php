<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: tcImport.php,v $
 *
 * @version $Revision: 1.4 $
 * @modified $Date: 2005/08/31 11:35:12 $
 *
 * @author	Martin Havlat
 * @author	Chad Rosen
 *
 * This page manages the importation of product data from a csv file.
 * 20050828 - scs - changes for importing tc to a specific category
 * 20050831 - scs - import limits are now define in config.inc.php
*/
require('../../config.inc.php');
require_once('common.php');
require_once('import.inc.php');
testlinkInitPage();

// Contains the full path and filename of the uploaded file as stored on the server.
$source = isset($HTTP_POST_FILES['uploadedFile']['tmp_name']) ? $HTTP_POST_FILES['uploadedFile']['tmp_name'] : null;
//20050831 - scs - import now import not to a single file only
$dest = TL_TEMP_PATH . session_id()."-importTc.csv";
$catIDForImport = isset($_POST['catID']) ? intval($_POST['catID']) : 0;

$uploadedFile = null;
$overview = null;
$imported = null;

// check the uploaded file
if ( ($source != 'none') && ($source != '' ))
{ 
	// store the file
	if (move_uploaded_file($source, $dest))
	{
		$uploadedFile = $dest;
		$overview = showTcImport($dest,$catIDForImport); //create overview table
	}
} 

if(isset($_POST['import']))
	$imported = exeTcImport($_POST['location'],$catIDForImport);

$fileFormatString = lang_get('the_format');
if ($catIDForImport)
	$fileFormatString = lang_get('the_format_by_cat');
	
$smarty = new TLSmarty;
$smarty->assign('fileFormatString',$fileFormatString);
$smarty->assign('productName', $_SESSION['productName']);
$smarty->assign('uploadedFile', $uploadedFile);
$smarty->assign('overview', $overview);
$smarty->assign('catIDForImport', $catIDForImport);
$smarty->assign('imported', $imported);
$smarty->assign('import_limit',TL_IMPORT_LIMIT);
$smarty->display('tcImport.tpl');
?>