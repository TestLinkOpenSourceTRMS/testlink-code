<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: opt_transfer.php,v $
 *
 * @version $Revision: 1.7 $
 * @modified $Date: 2009/08/07 06:58:10 $
 *
 * Manage Option Transfer (double select box)
 *
 * Author: franciscom
**/
function opt_transf_cfg(&$opt_cfg, $right_list, $js_ot_name = 'ot')
{
	$opt_cfg->js_events->all_right_click="window.setTimeout('$js_ot_name.transferAllRight()',20);";
	$opt_cfg->js_events->left2right_click="window.setTimeout('$js_ot_name.transferRight()',20);";
	$opt_cfg->js_events->right2left_click="window.setTimeout('$js_ot_name.transferLeft()',20);";
	$opt_cfg->js_events->all_left_click="window.setTimeout('$js_ot_name.transferAllLeft()',20);";


	$a_right = array();
	$a_left = array();

	if(trim($right_list) == "")
	{
		if(!is_null($opt_cfg->to->map))
		{
			$a_right = $opt_cfg->to->map;
		}
	} 
	else
	{
		$a_k = explode(",",trim($right_list));
		foreach($a_k as $key => $code)
		{
			$a_right[$code] = $opt_cfg->from->map[$code];
		}
	}

	if(!is_null($opt_cfg->from->map))
	{
		$a_left = array_diff_assoc($opt_cfg->from->map,$a_right);
	}

	$opt_cfg->from->map = $a_left;
	$opt_cfg->to->map = $a_right;
}


function keywords_opt_transf_cfg(&$opt_cfg, $right_list)
{
	$opt_cfg->size = 8;
	$opt_cfg->style = "width: 98%;";

	$opt_cfg->js_events = new stdClass();
	$opt_cfg->js_events->all_right_click = "";
	$opt_cfg->js_events->left2right_click = "";
	$opt_cfg->js_events->right2left_click = "";
	$opt_cfg->js_events->all_left_click = "";

	if( is_null($opt_cfg->from))
	{
		$opt_cfg->from = new stdClass();
	}	
	$opt_cfg->from->name = "from_select_box";
	$opt_cfg->from->id_field = 'id';
	$opt_cfg->from->desc_field = 'keyword';
	$opt_cfg->from->desc_glue = " ";
	$opt_cfg->from->desc_html_content = true;
	$opt_cfg->from->required = false;
	$opt_cfg->from->show_id_in_desc = true;
	$opt_cfg->from->js_events->ondblclick = "";
	
	if( is_null($opt_cfg->to))
	{
		$opt_cfg->to = new stdClass();
	}	
	$opt_cfg->to->name = "to_select_box";
	$opt_cfg->to->show_id_in_desc = true;
	$opt_cfg->to->id_field = 'id';
	$opt_cfg->to->desc_field = 'keyword';
	$opt_cfg->to->desc_glue = " ";
	$opt_cfg->to->desc_html_content = true;
	$opt_cfg->to->required = false;
	$opt_cfg->to->show_id_in_desc = true;
	$opt_cfg->to->js_events->ondblclick = "";

	opt_transf_cfg($opt_cfg, $right_list,$opt_cfg->js_ot_name);  
}

function opt_transf_empty_cfg()
{
	$opt_cfg = new stdClass();
	$opt_cfg->js_ot_name = "";
	$opt_cfg->size = 8;
	$opt_cfg->style = "width: 300px;";

	$opt_cfg->js_events = new stdClass();
	$opt_cfg->js_events->all_right_click = "";
	$opt_cfg->js_events->left2right_click = "";
	$opt_cfg->js_events->right2left_click = "";
	$opt_cfg->js_events->all_left_click = "";
	
	$opt_cfg->global_lbl = 'Option Transfer';
	$opt_cfg->from = new stdClass();
	$opt_cfg->from->lbl = 'from';
	$opt_cfg->from->name = "from_select_box";
	$opt_cfg->from->map = array();
	
	$opt_cfg->from->id_field = '';
	$opt_cfg->from->desc_field = '';
	$opt_cfg->from->desc_glue = " ";
	$opt_cfg->from->desc_html_content = true;
	$opt_cfg->from->required = false;
	$opt_cfg->from->show_id_in_desc = true;
	$opt_cfg->from->js_events = new stdClass;
	$opt_cfg->from->js_events->ondblclick = "";

	$opt_cfg->to = new stdClass();
	$opt_cfg->to->lbl = 'to';
	$opt_cfg->to->name = "to_select_box";
	$opt_cfg->to->map = array();
	$opt_cfg->to->show_id_in_desc = true;
	$opt_cfg->to->id_field = '';
	$opt_cfg->to->desc_field = '';
	$opt_cfg->to->desc_glue = " ";
	$opt_cfg->to->desc_html_content = true;
	$opt_cfg->to->required = false;
	$opt_cfg->to->show_id_in_desc = true;
	$opt_cfg->to->js_events = new stdClass();
	$opt_cfg->to->js_events->ondblclick = "";
	
	return $opt_cfg;
}

/**
 * 
 *
 */
function item_opt_transf_cfg(&$opt_cfg, $right_list)
{
	$opt_cfg->size = 8;
	$opt_cfg->style = "width: 98%;";

	$opt_cfg->js_events->all_right_click = "";
	$opt_cfg->js_events->left2right_click = "";
	$opt_cfg->js_events->right2left_click = "";
	$opt_cfg->js_events->all_left_click = "";
	$opt_cfg->from->name = "from_select_box";
	
	$opt_cfg->from->id_field = 'id';
	// $opt_cfg->from->desc_field = 'keyword';
	$opt_cfg->from->desc_glue = " ";
	$opt_cfg->from->desc_html_content = true;
	$opt_cfg->from->required = false;
	$opt_cfg->from->show_id_in_desc = true;
	$opt_cfg->from->js_events->ondblclick = "";
	
	$opt_cfg->to->name = "to_select_box";
	$opt_cfg->to->show_id_in_desc = true;
	$opt_cfg->to->id_field = 'id';
	//$opt_cfg->to->desc_field = 'keyword';
	$opt_cfg->to->desc_glue = " ";
	$opt_cfg->to->desc_html_content = true;
	$opt_cfg->to->required = false;
	$opt_cfg->to->show_id_in_desc = true;
	$opt_cfg->to->js_events->ondblclick = "";

	opt_transf_cfg($opt_cfg, $right_list,$opt_cfg->js_ot_name);  
}
