<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: show_help.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2007/05/19 21:08:15 $  $Author: schlundus $
 *
 * manage launch of help pages.
**/
require('../../config.inc.php');
require_once("common.php");
// start session, need to get right basehref
testlinkInitPage($db);

$smarty = new TLSmarty();
$td = TL_ABS_PATH . TL_HELP_RPATH . $_REQUEST['locale'];
$smarty->template_dir = $td;
$smarty->display($_REQUEST['help'] . ".html");
?>
