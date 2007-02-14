<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: tlsmarty.inc.php,v $
 *
 * @version $Revision: 1.19 $
 * @modified $Date: 2007/02/14 08:17:17 $ $Author: franciscom $
 *
 * @author Martin Havlat
 *
 * TLSmarty class implementation used in all templates
 *
 * 
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
		global $g_tc_status;
		global $g_tc_status_css;
		global $g_bugInterfaceOn;
		global $g_tc_status_for_ui;
		global $g_attachments;
		global $g_locales;
		global $g_gui;


	    $this->Smarty();
	    $this->template_dir = TL_ABS_PATH . 'gui/templates/';
	    $this->compile_dir = TL_TEMP_PATH;
		$this->config_dir = TL_ABS_PATH . 'gui/templates/';
		
		$testprojectColor = TL_BACKGROUND_DEFAULT;
		if (isset($_SESSION['testprojectColor'])) 
        {
			$testprojectColor =  $_SESSION['testprojectColor'];
        	if (!strlen($testprojectColor))
        		$testprojectColor = TL_BACKGROUND_DEFAULT;
		}
		$this->assign('testprojectColor', $testprojectColor);
    
		$my_locale = isset($_SESSION['locale']) ? $_SESSION['locale'] : TL_DEFAULT_LOCALE;
		$basehref = isset($_SESSION['basehref']) ? $_SESSION['basehref'] : TL_BASE_HREF;

		$this->assign('basehref', $basehref);
		$this->assign('helphref', $basehref . 'gui/help/' . $my_locale . "/");
		$this->assign('css', $basehref . TL_TESTLINK_CSS);
		$this->assign('locale', $my_locale);
		
		$this->assign('gsmarty_tc_status',$g_tc_status);
		$this->assign('gsmarty_tc_status_css',$g_tc_status_css);
		$this->assign('gsmarty_tc_status_for_ui',$g_tc_status_for_ui);
		$this->assign('gsmarty_tc_status_verbose_labels',$g_tc_status_verbose_labels);


		$this->assign('g_bugInterfaceOn', $g_bugInterfaceOn);
		$this->assign('gsmarty_attachments',$g_attachments);
		
		$this->assign('gsmarty_gui',$g_gui);
		
		$this->assign('gsmarty_title_sep',TITLE_SEP);
		$this->assign('gsmarty_title_sep_type2',TITLE_SEP_TYPE2);
		$this->assign('gsmarty_title_sep_type3',TITLE_SEP_TYPE3);

		// define a select structure for {html_options ...}
		$this->assign('gsmarty_option_yes_no', array(0 => lang_get('No'), 1 => lang_get('Yes')));
		
		
	
		$this->assign('pageCharset',TL_TPL_CHARSET);
		$this->assign('tlVersion',TL_VERSION);
		
		// this allows unclosed <head> tag to add more information and link; see inc_head.tpl 
		$this->assign('openHead', 'no');
		
		// there are some variables which should not be assigned for template 
		// but must be initialized
		$this->assign('jsValidate', null);
		$this->assign('jsTree', null);
		$this->assign('sqlResult', null);
		
		$this->assign('action', 'updated');
	
		$this->assign('optLocale',$g_locales);

    	$this->assign('gsmarty_href_keywordsView',
                  ' "lib/keywords/keywordsView.php" ' .
				   ' target="mainframe" class="bold" ' .
				   ' title="' . lang_get('menu_manage_keywords') . '"'); 


    // Registered functions
		$this->register_function("lang_get", "lang_get_smarty");
		$this->register_function("localize_date", "localize_date_smarty");
		$this->register_function("localize_timestamp", "localize_timestamp_smarty");
    	$this->register_function("localize_tc_status","translate_tc_status_smarty");
		
	}
}
?>
