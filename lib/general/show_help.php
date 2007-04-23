<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: show_help.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2007/04/23 17:05:00 $  $Author: franciscom $
 *
 * manage launch of help pages.
**/
require('../../config.inc.php');
require_once("common.php");
testlinkInitPage($db);    // start session, need to get right basehref

$smarty = new TLSmarty();
$td=TL_ABS_PATH . TL_HELP_RPATH . $_REQUEST['locale'];
$smarty->template_dir=$td;
$smarty->display($_REQUEST['help'] . ".html");
?>