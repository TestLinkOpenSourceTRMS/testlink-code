<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * TLSmarty class is TestLink wraper for GUI templates processing. 
 * The class is loaded via common.php to all pages.
 * 
 * @filesource	tlsmarty.inc.php
 * @package 	  TestLink
 * @author 		  Martin Havlat
 * @copyright 	2005-2012, TestLink community 
 * @link 		    http://www.teamst.org/index.php
 * @link 		    http://www.smarty.net/ 
 *
 * @internal revisions
 * @since 2.0
 *
 */

define('SMARTY_DIR', TL_ABS_PATH . 'third_party'. DIRECTORY_SEPARATOR . 'smarty'. DIRECTORY_SEPARATOR . 
       'libs' . DIRECTORY_SEPARATOR);
define('SMARTY_CORE_DIR', SMARTY_DIR . 'internals' . DIRECTORY_SEPARATOR);

/** include parent extrenal component */
require_once( SMARTY_DIR . 'Smarty.class.php');

/** 
in this way you can switch ext js version in easy way,
To use a different version of Sencha (Old EXT-JS) that provided with TL 
*/
if( !defined('TL_EXTJS_RELATIVE_PATH') )
{
    define('TL_EXTJS_RELATIVE_PATH','third_party/ext-js' );
}

/** @TODO martin: refactore + describe 
 * The next two functions was moved here from common.php */
function translate_tc_status($status_code)
{
	$resultsCfg = config_get('results'); 
	$verbose = lang_get('test_status_not_run');
	if( $status_code != '')
	{
		$suffix = $resultsCfg['code_status'][$status_code];
		$verbose = lang_get('test_status_' . $suffix);
	}
	return $verbose;
}

/** 
 * function is registered in tlSmarty class
 * @uses function translate_tc_status
 * @todo should be moved to tlSmarty class
 */
function translate_tc_status_smarty($params, &$smarty)
{
	$the_ret = translate_tc_status($params['s']);
	if(	isset($params['var']) )
	{
		$smarty->assign($params['var'], $the_ret);
	}
	else
	{
		return $the_ret;
	}
}

/**
 * Should be used to prevent certain templates to only get included once per page load. 
 * For example javascript includes, such as ext-js.
 *
 * Usage (in template):
 * <code>
 * {if guard_header_smarty(__FILE__)}
 *     template code
 *     <script src="big-library.js type="text/javascript"></script>
 * {/if}
 * </code>
 */
function guard_header_smarty($file)
{
	static $guarded = array();
	$status_ok = false;
	
	if (!isset($guarded[$file]))
	{
		$guarded[$file] = true;
		$status_ok = true;
	}
	return $status_ok;
}

/**
 * TestLink wrapper for external Smarty class
 * @package 	TestLink
 */
class TLSmarty extends Smarty
{
  var $baseHREF;
  function __construct()
  {
    global $tlCfg;
    
    parent::__construct();
    $this->template_dir = TL_ABS_PATH . 'gui/templates/';
    $this->config_dir = TL_ABS_PATH . 'gui/templates/';
    $this->compile_dir = TL_TEMP_PATH;
    $this->baseHREF = isset($_SESSION['basehref']) ? $_SESSION['basehref'] : TL_BASE_HREF;

    $this->assign('testprojectColor',$tlCfg->gui->background_color); //TL_BACKGROUND_DEFAULT;
    if (isset($_SESSION['testprojectColor']))
    {
      $dummy = $_SESSION['testprojectColor'];
      $dummy = ($dummy != '') ? $dummy : $tlCfg->gui->background_color;
      $this->assign('testprojectColor', $dummy);
    }
    if($tlCfg->smarty_debug)
    {
      $this->debugging = true;
      tLog("Smarty debug window = ON");
    }
    
        
        
    // -------------------------------------------------------------------------------------
    // Must be initialized to avoid log on TestLink Event Viewer due to undefined variable.
    // This means that optional/missing parameters on include can not be used.
    //
    // Good refactoring must be done in future, to create group of this variable
    // with clear names that must be a hint for developers, to understand where this
    // variables are used.
    
    // inc_head.tpl
    $this->assign('SP_html_help_file',null);
    $this->assign('menuUrl',null);
    $this->assign('args',null);
    $this->assign('additionalArgs',null);
    $this->assign('pageTitle',null);
    
    $this->assign('css_only',null);
    $this->assign('body_onload',null);
        
    // inc_help.tpl
    $this->assign('inc_help_alt',null);
    $this->assign('inc_help_title',null);
    $this->assign('inc_help_style',null);
    $this->assign('show_help_icon',true);
            
    $this->assign('tplan_name',null);
    $this->assign('name',null);
    // -----------------------------------------------------------------------------
        
    $this->assign('basehref', $this->baseHREF);
    $this->assign('css', $this->baseHREF . TL_TESTLINK_CSS);
    $this->assign('custom_css', $this->baseHREF . TL_TESTLINK_CUSTOM_CSS);

    // load configuration
    $this->assign('session',isset($_SESSION) ? $_SESSION : null);
    $this->assign('tlCfg',$tlCfg);
    $this->assign('gsmarty_gui',$tlCfg->gui);
    $this->assign('gsmarty_spec_cfg',config_get('spec_cfg'));
    $this->assign('tlVersion',TL_VERSION);
    $this->assign('testproject_coloring',null);
        
        	
    // -----------------------------------------------------------------------------
    // define a select structure for {html_options ...}
    $this->assign('gsmarty_option_yes_no', array(0 => lang_get('No'), 1 => lang_get('Yes')));
    $this->assign('gsmarty_option_priority', array(HIGH => lang_get('high_priority'), 
                                                   MEDIUM => lang_get('medium_priority'), 
                                                   LOW => lang_get('low_priority')));

    $this->assign('gsmarty_option_importance', array(HIGH => lang_get('high_importance'), 
                                                     MEDIUM => lang_get('medium_importance'), 
                                                     LOW => lang_get('low_importance')));
       
    // this allows unclosed <head> tag to add more information and link; see inc_head.tpl
    $this->assign('openHead', 'no');
    
    // there are some variables which should not be assigned for template but must be initialized
    // inc_head.tpl
    $this->assign('jsValidate', null);
    $this->assign('jsTree', null);
        	
        	
    // user feedback variables (used in inc_update.tpl)
    $this->assign('user_feedback', null);
    $this->assign('feedback_type', ''); // Possibile values: soft
    $this->assign('action', 'updated'); //todo: simplify (remove) - use user_feedback
    $this->assign('sqlResult', null); //todo: simplify (remove) - use user_feedback
        
    $this->assign('refresh', 'no');
    $this->assign('result', null);
        
    $this->assign('optLocale',config_get('locales'));
        
    $my_locale = isset($_SESSION['locale']) ? $_SESSION['locale'] : TL_DEFAULT_LOCALE;
    $this->assign('locale', $my_locale);
    $this->assign('gsmarty_date_format',$tlCfg->locales_date_format[$my_locale]);
        
    // add smarty variable to be able to set localized date format on datepicker
    $this->assign('gsmarty_datepicker_format',
                  str_replace('%','',$tlCfg->locales_date_format[$my_locale]));
                      
    $this->assign('gsmarty_timestamp_format',$tlCfg->locales_timestamp_format[$my_locale]);
        
    // Images
    // add in alpha order please
    $tlImages = array('api_info' => TL_THEME_IMG_DIR . "brick.png",
                      'bullet' => TL_THEME_IMG_DIR . "slide_gripper.gif",
                      'bugMgmt' => TL_THEME_IMG_DIR . "bug1.gif",
                      'bugMgmtGreyed' => TL_THEME_IMG_DIR . "bug1_greyed.gif",
                      'calendar' => TL_THEME_IMG_DIR . "calendar.gif",
                      'checked' => TL_THEME_IMG_DIR . "apply_f2_16.png",
                      'date' => TL_THEME_IMG_DIR . "date.png",
                      'delete' => TL_THEME_IMG_DIR . "trash.png",
                      'delete_disabled' => TL_THEME_IMG_DIR . "trash_greyed.png",
                      'demo_mode' => TL_THEME_IMG_DIR . "emoticon_tongue.png",
    				          'direct_link' => TL_THEME_IMG_DIR . "world_link.png",
    				          'disconnect' => TL_THEME_IMG_DIR . 'disconnect.png',
    				          'edit' => TL_THEME_IMG_DIR . "icon_edit.png",
    				          'edit_type2' => TL_THEME_IMG_DIR . "edit_icon.png",
                      'exec_order' => TL_THEME_IMG_DIR . 'timeline_marker.png',
                      'executed' => TL_THEME_IMG_DIR . 'lightning.png',
                      'export' => TL_THEME_IMG_DIR . "export.png",
                      'export_import' => TL_THEME_IMG_DIR . "export_import.png",
				              'event_info' => TL_THEME_IMG_DIR . "question.gif",
				              'favicon' => TL_THEME_IMG_DIR . "favicon.ico",
                      'history' => TL_THEME_IMG_DIR . "history.png",
                      'history_small' => TL_THEME_IMG_DIR . "history_small.png",
                      'lock' => TL_THEME_IMG_DIR . "lock.png",
                      'log_message' => TL_THEME_IMG_DIR . "history.png",
                      'log_message_small' => TL_THEME_IMG_DIR . "history_small.png",
                      'note_edit' => TL_THEME_IMG_DIR . "note_edit.png",
                      'note_edit_greyed' => TL_THEME_IMG_DIR . "note_edit_greyed.png",
                      'import' => TL_THEME_IMG_DIR . "door_in.png",
    				          'info' => TL_THEME_IMG_DIR . "question.gif",
                      'insert_step' => TL_THEME_IMG_DIR . "insert_step.png",
                      'reorder' => TL_THEME_IMG_DIR . "arrow_switch.png",
                      'search' => TL_THEME_IMG_DIR . "magnifier.png",
                      'sort' => TL_THEME_IMG_DIR . "sort_hint.png",
                      'toggle_all' => TL_THEME_IMG_DIR . "toggle_all.gif",
                      'allToRight' => TL_THEME_IMG_DIR . 'ico_all_r.gif',
                      'leftToRight' => TL_THEME_IMG_DIR . 'ico_l2r.gif',
                      'rightToLeft' => TL_THEME_IMG_DIR . 'ico_r2l.gif',
                      'allToLeft' => TL_THEME_IMG_DIR . 'ico_all_l.gif',
                      'attachMgmt' => TL_THEME_IMG_DIR . 'upload_16.png',
                      'attachMgmtGreyed' => TL_THEME_IMG_DIR . 'upload_16_greyed.png',
                      'manualExec' => TL_THEME_IMG_DIR . 'user.png',
                      'automatedExec' => TL_THEME_IMG_DIR . 'bullet_wrench.png'
                      );

    $msg = lang_get('show_hide_api_info');
    $tlImages['toggle_api_info'] =  "<img class=\"clickable\" title=\"{$msg}\" alt=\"{$msg}\" " .
    								" onclick=\"showHideByClass('span','api_info');event.stopPropagation();\" " .
    								" src=\"{$tlImages['api_info']}\" align=\"left\" />";

    $msg = lang_get('show_hide_direct_link');
    $tlImages['toggle_direct_link'] = "<img class=\"clickable\" title=\"{$msg}\" alt=\"{$msg}\" " .
    						  		  " onclick=\"showHideByClass('div','direct_link');event.stopPropagation();\" " .
    						  		  " src=\"{$tlImages['direct_link']}\" align=\"left\" />";

    // Some useful values for Sort Table Engine
    $tlImages['sort_hint'] = '';
    switch (TL_SORT_TABLE_ENGINE)
    {
        case 'kryogenix.org':
            $sort_table_by_column = lang_get('sort_table_by_column');
            $tlImages['sort_hint'] = "<img title=\"{$sort_table_by_column}\" " .
            						 " alt=\"{$sort_table_by_column}\" " .
            						 " src=\"{$tlImages['sort']}\" align=\"left\" />";
            
            $this->assign("noSortableColumnClass","sorttable_nosort");
        break;
        
        default:
            $this->assign("noSortableColumnClass",'');
        break;
    }

    $this->assign('tlRefreshTreeJS',
                  '<script type="text/javascript">' .
                  "parent.frames['treeframe'].document.forms['filter_panel_form'].submit();" .
                  '</script>');    

    $this->assign('tlRefreshTreeByReloadJS',
                  '<script type="text/javascript"> parent.treeframe.location.reload(); </script>');    


		// Do not move!!!
    $this->assign("tlImages",$tlImages);
    
    // Register functions
    $this->registerPlugin("function","lang_get", "lang_get_smarty");
    $this->registerPlugin("function","localize_date", "localize_date_smarty");
    $this->registerPlugin("function","localize_timestamp", "localize_timestamp_smarty");
    $this->registerPlugin("function","localize_tc_status","translate_tc_status_smarty");
    
    $this->registerPlugin("modifier","basename","basename");
    $this->registerPlugin("modifier","dirname","dirname");
  } 

} // end of class TLSmarty
?>
