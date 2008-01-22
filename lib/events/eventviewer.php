<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: eventviewer.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2008/01/22 21:52:19 $ by $Author: schlundus $
**/
require_once("../../config.inc.php");
require_once("common.php");
testlinkInitPage($db);

$template_dir = 'events/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

$events = $g_tlLogger->getEventsFor(null,null,null,null,500);

$smarty = new TLSmarty();
$smarty->assign('events',$events);
$smarty->assign('sqlResult',null);
$smarty->display($template_dir . $default_template);
?>