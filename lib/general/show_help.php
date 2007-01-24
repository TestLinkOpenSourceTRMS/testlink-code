<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: show_help.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2007/01/24 08:08:17 $
 *
 * This file manages the navigation bar. 
**/
require('../../config.inc.php');
// require_once("common.php");
//testlinkInitPage($db);

$smarty = new TLSmarty();
$td=TL_ABS_PATH . TL_HELP_RPATH . $_REQUEST['locale'];
$smarty->template_dir=$td;
$smarty->display($_REQUEST['help'] . ".html");
?>