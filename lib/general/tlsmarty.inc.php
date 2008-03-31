<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * Filename $RCSfile: tlsmarty.inc.php,v $
 *
 * @version $Revision: 1.41 $
 * @modified $Date: 2008/03/31 20:08:04 $ $Author: schlundus $
 *
 * @author Martin Havlat
 *
 * TLSmarty class implementation used in all templates
 *
 * 20080424 - havlatm - added $tlCfg
 * 20080303 - franciscom - changed default value for feedback_type
 * 20080109 - franciscom - added some *_img for URL to common used images.
 *
 * 20070624 - franciscom - g_locales_html_select_date_field_order
 *                         g_locales_date_format
 *                         g_locales_timestamp_format
 * 20070218 - franciscom - g_interface_bugs
 * 20070218 - franciscom - gsmarty_spec_cfg
 * 20070214 - franciscom - gsmarty_tc_status_verbose_labels
 * 20061223 - franciscom - added g_gui
 * 20060820 - franciscom - added config_dir
 * 20060602 - franciscom - added new global var $g_attachments
 * 20060528 - franciscom - added new global var $g_tc_status_for_ui
 *
**/
class TLSmarty extends Smarty
{
    function TLSmarty()
	{
		global $tlCfg;
		global $g_attachments;
		global $g_spec_cfg;
		global $g_tc_status;
		global $g_tc_status_css;
		global $g_bugInterfaceOn;
		global $g_interface_bugs;
		global $g_tc_status_for_ui;
		global $g_tc_status_verbose_labels;
		global $g_locales;
	    global $g_locales_html_select_date_field_order;
    	global $g_locales_date_format;
    	global $g_locales_timestamp_format;


	  	$this->Smarty();
	  	$this->template_dir = TL_ABS_PATH . 'gui/templates/';
	  	$this->compile_dir = TL_TEMP_PATH;
		$this->config_dir = TL_ABS_PATH . 'gui/templates/';

		$testproject_coloring=$tlCfg->gui->testproject_coloring;
		$testprojectColor = $tlCfg->gui->background_color ; //TL_BACKGROUND_DEFAULT;
		if (isset($_SESSION['testprojectColor']))
    	{
			$testprojectColor =  $_SESSION['testprojectColor'];
     	if (!strlen($testprojectColor))
        		$testprojectColor = $tlCfg->gui->background_color;
		}
		$this->assign('testprojectColor', $testprojectColor);

		$my_locale = isset($_SESSION['locale']) ? $_SESSION['locale'] : TL_DEFAULT_LOCALE;
		$basehref = isset($_SESSION['basehref']) ? $_SESSION['basehref'] : TL_BASE_HREF;

		if ($tlCfg->smarty_debug)
		{
			$this->debugging = true;
			tLog("Smarty debug window = ON");
		}

		$this->assign('title',null);
		$this->assign('SP_html_help_file',null);
		$this->assign('menuUrl',null);
		$this->assign('args',null);
		$this->assign('css_only',null);
		$this->assign('body_onload',null);
		$this->assign('tplan_name',null);
		$this->assign('name',null);
		$this->assign('basehref', $basehref);
		$this->assign('helphref', $basehref . 'gui/help/' . $my_locale . "/");
		$this->assign('css', $basehref . TL_TESTLINK_CSS);
		$this->assign('locale', $my_locale);
		$this->assign('tableStyles',null);
		$this->assign('tableClassName',null);
		$this->assign('inheritStyle',null);
		$this->assign('show_upload_btn',null);
		$this->assign('show_title',null);

		// -----------------------------------------------------------------------------
		// load configuration
		$this->assign('session',$_SESSION);

		// load configuration
		$this->assign('tlCfg',$tlCfg);
		$this->assign('gsmarty_gui',$tlCfg->gui);
    	$this->assign('gsmarty_spec_cfg',$g_spec_cfg);
		$this->assign('gsmarty_attachments',$g_attachments);

		// obsolete - use gsmarty_gui->title_sep_x in templates
		$this->assign('gsmarty_title_sep', $tlCfg->gui->title_sep_1);
		$this->assign('gsmarty_title_sep_type2', $tlCfg->gui->title_sep_2);
		$this->assign('gsmarty_title_sep_type3', $tlCfg->gui->title_sep_3);

		$this->assign('pageCharset',$tlCfg->charset);
		$this->assign('tlVersion',TL_VERSION);

		$this->assign('gsmarty_tc_status',$g_tc_status);
		$this->assign('gsmarty_tc_status_css',$g_tc_status_css);
		$this->assign('gsmarty_tc_status_for_ui',$g_tc_status_for_ui);
		$this->assign('gsmarty_tc_status_verbose_labels',$g_tc_status_verbose_labels);

		$this->assign('g_bugInterfaceOn', $g_bugInterfaceOn);
		$this->assign('gsmarty_interface_bugs',$g_interface_bugs);
		$this->assign('testproject_coloring',null);
		// -----------------------------------------------------------------------------
		// define a select structure for {html_options ...}
		$this->assign('gsmarty_option_yes_no', array(0 => lang_get('No'), 1 => lang_get('Yes')));
		$this->assign('gsmarty_option_priority', array(3 => lang_get('high_priority'), 2 => lang_get('medium_priority'), 1 => lang_get('low_priority')));
		$this->assign('gsmarty_option_importance', array(3 => lang_get('high_importance'), 2 => lang_get('medium_importance'), 1 => lang_get('low_importance')));
		$this->assign('gsmarty_option_risk', array(3 => lang_get('high_risk'), 2 => lang_get('medium_risk'), 1 => lang_get('low_risk')));

		// this allows unclosed <head> tag to add more information and link; see inc_head.tpl
		$this->assign('openHead', 'no');

		// there are some variables which should not be assigned for template
		// but must be initialized
		$this->assign('jsValidate', null);
		$this->assign('jsTree', null);
		$this->assign('sqlResult', null);
		// user feedback variables (used in inc_update.tpl)
		$this->assign('action', 'updated');
		$this->assign('user_feedback', null);
		// $this->assign('feedback_type', 'soft');
		$this->assign('feedback_type', '');

		$this->assign('refresh', 'no');
		$this->assign('result', null);

		$this->assign('optLocale',$g_locales);

    	$this->assign('gsmarty_href_keywordsView',
                  ' "lib/keywords/keywordsView.php" ' .
		          	  ' target="mainframe" class="bold" ' .
				          ' title="' . lang_get('menu_manage_keywords') . '"');


    	$this->assign('gsmarty_html_select_date_field_order',
                   $g_locales_html_select_date_field_order[$my_locale]);
    	$this->assign('gsmarty_date_format',$g_locales_date_format[$my_locale]);
    	$this->assign('gsmarty_timestamp_format',$g_locales_timestamp_format[$my_locale]);

		// -----------------------------------------------------------------------------
    	// Some common images
    	$sort_img = TL_THEME_IMG_DIR . "/sort_hint.png";
    	$api_info_img = TL_THEME_IMG_DIR . "/brick.png";

    	$this->assign("sort_img",$sort_img);
    	$this->assign("checked_img",TL_THEME_IMG_DIR . "/apply_f2_16.png");
    	$this->assign("delete_img",TL_THEME_IMG_DIR . "/trash.png");

    	$msg = lang_get('show_hide_api_info');
 		$this->assign('api_ui_show', $tlCfg->api_enabled);

	    $toogle_api_info_img="<img title=\"{$msg}\" alt=\"{$msg}\" " .
                         " onclick=\"showHideByClass('span','api_info');event.stopPropagation();\" " .
                         " src=\"{$api_info_img}\" align=\"left\" />";

	    $this->assign("toogle_api_info_img",$toogle_api_info_img);


	    // Some useful values for Sort Table Engine
    	switch (TL_SORT_TABLE_ENGINE)
    	{
        	case 'kryogenix.org':
        		$sort_table_by_column=lang_get('sort_table_by_column');
        		$sortHintIcon="<img title=\"{$sort_table_by_column}\" " .
                      " alt=\"{$sort_table_by_column}\" " .
                      " src=\"{$sort_img}\" align=\"left\" />";

        		$this->assign("sortHintIcon",$sortHintIcon);
        		$this->assign("noSortableColumnClass","sorttable_nosort");
 	       break;

	        default:
 		       $this->assign("sortHintIcon",'');
 		       $this->assign("noSortableColumnClass",'');
 	       break;
	    }

    	// Register functions
	  	$this->register_function("lang_get", "lang_get_smarty");
	  	$this->register_function("localize_date", "localize_date_smarty");
	  	$this->register_function("localize_timestamp", "localize_timestamp_smarty");
    	$this->register_function("localize_tc_status","translate_tc_status_smarty");

    	$this->register_modifier("basename","basename");
    	$this->register_modifier("dirname","dirname");
	}
}
?>
