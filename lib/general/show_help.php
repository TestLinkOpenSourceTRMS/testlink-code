<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: show_help.php,v $
 *
 * @version $Revision: 1.5 $
 * @modified $Date: 2009/02/09 20:37:39 $  $Author: schlundus $
 *
 * manage launch of help pages.
 *
 * rev:
 *     20071102 - franciscom - BUGID 1033
**/
require('../../config.inc.php');
require_once("common.php");
// start session, need to get right basehref
testlinkInitPage($db);

$smarty = new TLSmarty();
//@TODO security hole, directory traversal possible
$td = TL_ABS_PATH . TL_HELP_RPATH . $_REQUEST['locale'];
$smarty->template_dir = $td;

// BUGID 1033
$smarty->clear_compiled_tpl($_REQUEST['help'] . ".html"); 
$smarty->display($_REQUEST['help'] . ".html");
?>
