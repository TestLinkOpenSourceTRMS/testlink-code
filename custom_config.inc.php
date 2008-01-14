<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: custom_config.inc.php,v $
 *
 * @version $Revision: 1.8 $
 * @modified $Date: 2008/01/14 21:43:23 $ by $Author: franciscom $
 *
 * SCOPE:
 * Constants and configuration parameters used throughout TestLink 
 * DEFINED BY USERS.
 *
 * Use this page to overwrite configuration parameters (variables and defines)
 * presente in:
 *
 *             config.inc.php
 *             cfg\const.inc.php
 *-----------------------------------------------------------------------------
*/
//$g_tree_type='LAYERSMENU';
//$g_tree_type='DTREE';
$g_tree_type='JTREE';
$g_tree_show_testcase_id=1;
$g_exec_cfg->enable_tree_testcase_counters=1;
$g_exec_cfg->enable_tree_colouring=1;
?>