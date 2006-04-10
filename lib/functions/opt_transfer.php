<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: opt_transfer.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2006/04/10 09:08:08 $
 *
 * Manage Option Transfer (double select box)
 *
 * Author: franciscom
 *
 * 20060410 - franciscom
 * 
**/
function opt_tranf_cfg(&$opt_cfg, $right_list, $js_ot_name='ot')
{
$opt_cfg->js_events->all_right_click="window.setTimeout('$js_ot_name.transferAllRight()',20);";
$opt_cfg->js_events->left2right_click="window.setTimeout('$js_ot_name.transferRight()',20);";
$opt_cfg->js_events->right2left_click="window.setTimeout('$js_ot_name.transferLeft()',20);";
$opt_cfg->js_events->all_left_click="window.setTimeout('$js_ot_name.transferAllLeft()',20);";


$a_right=array();
if( strlen(trim($right_list)) == 0 )
{
	 $a_right = $opt_cfg->to->map;
}
else
{
  $a_k=explode(",",trim($right_list));
  
  foreach($a_k as $key => $code)
  {
  	$a_right[$code] = $opt_cfg->from->map[$code];
  }
}

$a_left=array_diff_assoc($opt_cfg->from->map,$a_right);


$opt_cfg->from->map=$a_left;
$opt_cfg->to->map=$a_right;

}