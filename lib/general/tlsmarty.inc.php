<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: tlsmarty.inc.php,v $
 *
 * @version $Revision: 1.4 $
 * @modified $Date: 2005/08/31 19:21:38 $
 *
 * @author Martin Havlat
 *
 * TLSmarty class implementation used in all templates
 *
**/
class TLSmarty extends Smarty
{
    function TLSmarty()
	{
        $this->Smarty();
        $this->template_dir = TL_ABS_PATH . 'gui/templates/';
        $this->compile_dir = TL_TEMP_PATH;
        if (isset($_SESSION['productColor'])) 
        	$productColor =  $_SESSION['productColor'];
        else
        	$productColor = TL_BACKGROUND_DEFAULT;
        	
		$this->assign('productColor', $productColor);
    
		$my_locale = isset($_SESSION['locale']) ? $_SESSION['locale'] : TL_DEFAULT_LOCALE;
		$basehref = isset($_SESSION['basehref']) ? $_SESSION['basehref'] : TL_BASE_HREF;

		$this->assign('basehref', $basehref);
		$this->assign('helphref', $basehref . 'gui/help/' . $my_locale . "/");
		$this->assign('css', $basehref . 'gui/css/testlink.css');
		$this->assign('locale', $my_locale);
		global $g_tc_status;
		$this->assign('g_tc_status',$g_tc_status);
	
		global $g_bugInterfaceOn;
		$this->assign('g_bugInterfaceOn', $g_bugInterfaceOn);
	
		$this->assign('pageCharset',TL_TPL_CHARSET);
		$this->assign('tlVersion',TL_VERSION);
		// this allows unclosed <head> tag to add more information and link; see inc_head.tpl 
		$this->assign('openHead', 'no');
		// there are some variables which should not be assigned for template 
		// but must be initialized
		$this->assign('jsValidate', null);
		$this->assign('jsTree', null);
		$this->assign('sqlResult', null);
		//20050831 - scs - changed default action to updated
		$this->assign('action', 'updated');
	
		global $g_locales;
		$this->assign('optLocale',$g_locales);
		$this->register_function("lang_get", "lang_get_smarty");
		
		// 20050828 - fm
		$this->register_function("localize_date", "localize_date_smarty");

		
		// define a select structure for {html_options ...}
		$this->assign('option_yes_no', array(0 => lang_get('No'), 1 => lang_get('Yes')));
	}
}
?>