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
 * @copyright 	2005-2020, TestLink community 
 * @link 		    http://www.testlink.org/
 * @link 		    http://www.smarty.net/ 
 *
 *
 */

/** in this way you can switch ext js version in easy way,
	To use a different version of Sencha (Old EXT-JS) that provided with TL */
if( !defined('TL_EXTJS_RELATIVE_PATH') ) {
    define('TL_EXTJS_RELATIVE_PATH','third_party/ext-js' );
}

if(!defined('TL_USE_LOG4JAVASCRIPT') ) {
  define('TL_USE_LOG4JAVASCRIPT',0);
}


/** 
 * The next two functions was moved here from common.php */
function translate_tc_status($status_code) {
	$resultsCfg = config_get('results'); 
	$verbose = lang_get('test_status_not_run');
	if( $status_code != '') {
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
function translate_tc_status_smarty($params, $smarty) {
	$the_ret = translate_tc_status($params['s']);
	if(	isset($params['var']) ) {
		$smarty->assign($params['var'], $the_ret);
	} else {
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
function guard_header_smarty($file) {
	static $guarded = array();
	$status_ok = false;
	
	if (!isset($guarded[$file])) {
		$guarded[$file] = true;
		$status_ok = true;
	}
	return $status_ok;
}

/**
 * TestLink wrapper for external Smarty class
 * @package 	TestLink
 */
class TLSmarty extends Smarty {
  private $tlImages;
  private $tlIMGTags;
  var $tlTemplateCfg;
	var $dashioHome;
	var $fontawesomeHome;

  function __construct() {
    global $tlCfg;
    global $g_tpl;
    
    $basehref = isset($_SESSION['basehref']) ? $_SESSION['basehref'] : TL_BASE_HREF;
    $my_locale = isset($_SESSION['locale']) ? $_SESSION['locale'] : TL_DEFAULT_LOCALE;

    parent::__construct();
    
    $main = TL_ABS_PATH . 'gui/templates/dashio/';
    $this->template_dir = 
             ['main' => $main,
              'attach' => $main . 'attachments/',
              'execInc' => $main . 'execute/include/',
              'feedback' => $main . 'feedback/',
              'include' => $main . 'include/',
              'tcaseInc' => $main . 'testcases/include/',
              'tcaseLbl' => $main . 'testcases/labels/'
             ];
                          
    $this->config_dir = TL_ABS_PATH . 'gui/templates/conf';
    $this->compile_dir = TL_TEMP_PATH;

    // 20200222
    // Can not access $this->dashioHome in templates
    // without doing the ->assign().
    // I declare the variable anyway, to be able to access
    // it from PHP code
    //
    $this->dashioHome = 'gui/templates/dashio/dashio-template/';
    $this->assign('dashioHome', $this->dashioHome);
    $this->assign('dashioHomeURL', $basehref . $this->dashioHome);

    //$this->assign('fontawesomeHomeURL', $basehref . $this->dashioHome . 'lib/fontawesome-4.7.0');
    $this->assign('fontawesomeHomeURL', $basehref . $this->dashioHome . 'lib/fontawesome-free-6.2.0-web');


    // ----------------------------------------------------------    
    $testproject_coloring = $tlCfg->gui->testproject_coloring;
    $testprojectColor = $tlCfg->gui->background_color ; 
    $this->assign('testprojectColor', $testprojectColor);
    
    
    if ($tlCfg->smarty_debug) {
      $this->debugging = true;
      tLog("Smarty debug window = ON");
    }


    // --------------------------------------------------------------
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
    $this->assign('printPreferences',null);
    
    $this->assign('css_only',null);
    $this->assign('body_onload',null);
    
    // inc_attachments.tpl
    $this->assign('attach_tableStyles',"font-size:12px");
    $this->assign('attach_tableClassName',"simple");
    $this->assign('attach_inheritStyle',0);
    $this->assign('attach_show_upload_btn',1);
    $this->assign('attach_show_title',1);
    $this->assign('attach_downloadOnly',false);
    
    // inc_help.tpl
    $this->assign('inc_help_alt',null);
    $this->assign('inc_help_title',null);
    $this->assign('inc_help_style',null);
    $this->assign('show_help_icon',true);
            
    $this->assign('tplan_name',null);
    $this->assign('name',null);
    // -------------------------------------------------------------
    
    $this->assign('basehref', $basehref);
    $this->assign('css', $basehref . TL_TESTLINK_CSS);
    $this->assign('use_custom_css', 0);
    if(!is_null($tlCfg->custom_css) && $tlCfg->custom_css != '') {
      $this->assign('use_custom_css', 1);
      $this->assign('custom_css', 
        $basehref . TL_THEME_CSS_DIR . $tlCfg->custom_css);
    }
    
    $this->assign('locale', $my_locale);
     
    //
    $stdTPLCfg = ['tcViewViewer.inc' => '',
                  'tcbody.inc' => '',
                  'steps.inc' => '',
                  'aliens.inc' => '',
                  'keywords.inc' => '',
                  'relations.inc' => '', 
                  'quickexec.inc' => '',
                  'platforms.inc' => '',
                  'attributesLinearForViewer.inc' => '', 
                  'steps_horizontal.inc' => '',
                  'steps_vertical.inc' => ''];

    array_walk($stdTPLCfg, function (&$value, $key) {
      $bbb = "testcases/include/";
      return $value =  $bbb . $key . '.tpl';
    });
 
    $stdTPLCfg['exec_test_spec.inc'] = '';
    $stdTPLCfg['exec_img_controls.inc'] = '';
    $stdTPLCfg['exec_controls.inc'] = '';
    $stdTPLCfg['exec_show_tc_exec.inc'] = '';
    $stdTPLCfg['exec_tc_relations.inc'] = '';
    $stdTPLCfg['add_issue_on_step.inc'] = '';
    $stdTPLCfg['create_issue.inc'] = '';
    $stdTPLCfg['execSetResultsBulk.inc'] = '';
    $stdTPLCfg['execSetResultsJS.inc'] = '';
    $stdTPLCfg['execSetResultsRemoteExec.inc'] = '';
    $stdTPLCfg['execSetResultsUtils.inc'] = '';
    $stdTPLCfg['issueTrackerMetadata.inc'] = '';
    $stdTPLCfg['issue_inputs_on_step.inc'] = '';

    array_walk($stdTPLCfg, function (&$value, $key) {
      $bbb = "execute/include/";
      if ($value == '') {
        return $value =  $bbb . $key . '.tpl';
      }
    });
 
    $stdTPLCfg['showScriptsTable.inc'] = 'include/showScriptsTable.inc.tpl';


    // -----------------------------------------------------------------------------
    // load configuration
    $this->assign('session',isset($_SESSION) ? $_SESSION : null);
    $this->assign('tlCfg',$tlCfg);
    $this->assign('tplConfig',array_merge($stdTPLCfg,(array)$g_tpl));
    $this->assign('gsmarty_gui',$tlCfg->gui);
    $this->assign('gsmarty_spec_cfg',config_get('spec_cfg'));
    $this->assign('gsmarty_attachments',config_get('attachments'));
    
    $this->assign('pageCharset',$tlCfg->charset);
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
       
    $wkf = array();
    $xcfg = config_get('testCaseStatus');
    foreach($xcfg as $human => $key) {
      $wkf[$key] = lang_get('testCaseStatus_' . $human);
    }  
    $this->assign('gsmarty_option_wkfstatus',$wkf);


    // this allows unclosed <head> tag to add more information and link; see inc_head.tpl
    $this->assign('openHead', 'no');
    
    // there are some variables which should not be assigned for template but must be initialized
    // inc_head.tpl
    $this->assign('jsValidate', null);
    $this->assign('jsTree', null);
    $this->assign('editorType', null);
    	
    	
    // user feedback variables (used in inc_update.tpl)
    $this->assign('user_feedback', null);
    $this->assign('feedback_type', ''); // Possibile values: soft
    $this->assign('action', 'updated'); //todo: simplify (remove) - use user_feedback
    $this->assign('sqlResult', null); //todo: simplify (remove) - use user_feedback
    
    $this->assign('refresh', 'no');
    $this->assign('result', null);
    
    // $this->assign('optLocale',config_get('locales'));
    $this->assign('gsmarty_href_keywordsView',
    			        ' "lib/keywords/keywordsView.php?tproject_id=%s%" ' . ' target="mainframe" class="bold" ' .
    			        ' title="' . lang_get('menu_manage_keywords') . '"');
    

    $this->assign('gsmarty_href_platformsView',
                  ' "lib/platforms/platformsView.php?tproject_id=%s%" ' . ' target="mainframe" class="bold" ' .
                  ' title="' . lang_get('menu_manage_platforms') . '"');

    $this->assign('gsmarty_html_select_date_field_order',
                  $tlCfg->locales_html_select_date_field_order[$my_locale]);
                  
    $this->assign('gsmarty_date_format',$tlCfg->locales_date_format[$my_locale]);
    
    // add smarty variable to be able to set localized date format on datepicker
    $this->assign('gsmarty_datepicker_format',
                  str_replace('%','',$tlCfg->locales_date_format[$my_locale]));
                  
    $this->assign('gsmarty_timestamp_format',$tlCfg->locales_timestamp_format[$my_locale]);
    
    // -----------------------------------------------------------------------------
    // Images
    $this->tlImages = tlSmarty::getImageSet();
    $this->tlIMGTags = tlSmarty::getIMGTagsSet();
    
    $msg = lang_get('show_hide_api_info');
    $this->tlImages['toggle_api_info'] =  "<img class=\"clickable\" title=\"{$msg}\" alt=\"{$msg}\" " .
    								" onclick=\"showHideByClass('span','api_info');event.stopPropagation();\" " .
    								" src=\"{$this->tlImages['api_info']}\" align=\"left\" />";
    
    $msg = lang_get('show_hide_direct_link');
    $this->tlImages['toggle_direct_link'] = "<img class=\"clickable\" title=\"{$msg}\" alt=\"{$msg}\" " .
    						  		                      " onclick=\"showHideByClass('div','direct_link');event.stopPropagation();\" " .
    						  		                      " src=\"{$this->tlImages['direct_link']}\" align=\"left\" />";
    
    // Some useful values for Sort Table Engine
    $this->tlImages['sort_hint'] = '';
    switch (TL_SORT_TABLE_ENGINE)
    {
      case 'kryogenix.org':
        $sort_table_by_column = lang_get('sort_table_by_column');
        $this->tlImages['sort_hint'] = "<img title=\"{$sort_table_by_column}\" " .
        						                   " alt=\"{$sort_table_by_column}\" " .
        						                   " src=\"{$this->tlImages['sort']}\" align=\"left\" />";
        
        $this->assign("noSortableColumnClass","sorttable_nosort");
      break;
      
      default:
        $this->assign("noSortableColumnClass",'');
      break;
    }

    // Do not move!!!
    $this->assign("tlImages",$this->tlImages);
    $this->assign("tlIMGTags",$this->tlIMGTags);
    
    // Register functions
    $this->registerPlugin("function","lang_get", "lang_get_smarty");
    $this->registerPlugin("function","localize_date", "localize_date_smarty");
    $this->registerPlugin("function","localize_timestamp", "localize_timestamp_smarty");
    $this->registerPlugin("function","localize_tc_status","translate_tc_status_smarty");
      
    $this->registerPlugin("modifier","basename","basename");
    $this->registerPlugin("modifier","dirname","dirname");

    // Call to smarty filter that adds a CSRF filter to all form elements
    if(isset($tlCfg->csrf_filter_enabled) && 
       $tlCfg->csrf_filter_enabled === TRUE && function_exists('smarty_csrf_filter')) {
          $this->registerFilter('output','smarty_csrf_filter');
    }

  } // end of function TLSmarty()

  /**
   *
   */
  function getImages() {
    return $this->tlImages;
  }

  /**
   *
   */
  static function getImageSet() 
  {
    $burl = isset($_SESSION['basehref']) ? $_SESSION['basehref'] : TL_BASE_HREF;
    $imgLoc = $burl . TL_THEME_IMG_DIR;

    $dummy = array('active' => $imgLoc . 'flag_green.png',
                   'activity' => $imgLoc . 'information.png',
                   'account' => $imgLoc . 'user_edit.png',
                   'add' => $imgLoc . 'add.png',
                   'add2set' => $imgLoc . 'basket_put.png',
                   'api_info' => $imgLoc . 'brick.png',
                   'assign_task' => $imgLoc . 'assign_exec_task_to_me.png',
                   'bug' => $imgLoc . 'bug.png',
                   'bug_link_tl_to_bts' => $imgLoc . 'bug_link_famfamfam.png',
                   'bug_create_into_bts' => $imgLoc . 'bug_add_famfamfam.png',
                   'bug_link_tl_to_bts_disabled' => $imgLoc . 'bug_link_disabled_famfamfam.png',
                   'bug_create_into_bts_disabled' => $imgLoc . 'bug_add_disabled_famfamfam.png',
                   'bug_add_note' => $imgLoc . 'bug_edit.png',
                   'bullet' => $imgLoc . 'slide_gripper.gif',
                   'bulkOperation' => $imgLoc . 'bulkAssignTransparent.png',
                   'calendar' => $imgLoc . 'calendar.gif',
                   'checked' => $imgLoc . 'apply_f2_16.png',
                   'choiceOn' => $imgLoc . 'accept.png',
                   'clear' => $imgLoc . 'trash.png',
                   'clear_notes' => $imgLoc . 'font_delete.png',
                   'clipboard' => $imgLoc . 'page_copy.png',
                   'check_ok' => $imgLoc . 'lightbulb.png',
                   'check_ko' => $imgLoc . 'link_error.png',
                   'cog'  => $imgLoc . 'cog.png',
                   'copy_attachments'  => $imgLoc . 'folder_add.png',
                   'create_copy' => $imgLoc . 'application_double.png',
                   'create_from_xml' => $imgLoc . 'wand.png',
                   'date' => $imgLoc . 'date.png',
                   'delete' => $imgLoc . 'trash.png',
                   'demo_mode' => $imgLoc . 'emoticon_tongue.png',
                   'delete_disabled' => $imgLoc . 'trash_greyed.png',
                   'disconnect' => $imgLoc . 'disconnect.png',
                   'disconnect_small' => $imgLoc . 'disconnect_small.png',
                   'direct_link' => $imgLoc . 'world_link.png',
                   'duplicate' => $imgLoc . 'application_double.png',
                   'edit' => $imgLoc . 'icon_edit.png',
                   'edit_icon' => $imgLoc . 'edit_icon.png',
                   'email' => $imgLoc . 'email.png',
                   'events' => $imgLoc . 'bell.png',
                   'eye' => $imgLoc . 'eye.png',
                   'vorsicht' => $imgLoc . 'exclamation.png',
                   'export' => $imgLoc . 'export.png',
                   'export_import' => $imgLoc . 'export_import.png',
                   'execute' => $imgLoc . 'lightning.png',
                   'executed' => $imgLoc . 'lightning.png',
                   'exec_icon' => $imgLoc . 'exec_icon.png',
                   'exec_passed' => $imgLoc . 'emoticon_smile.png',
                   'exec_failed' => $imgLoc . 'emoticon_unhappy.png',
                   'exec_blocked' => $imgLoc . 'emoticon_surprised.png',
                   'execution' => $imgLoc . 'controller.png',
                   'execution_order' => $imgLoc . 'timeline_marker.png',
                   'execution_duration' => $imgLoc . 'hourglass.png',
                   'export_excel' => $imgLoc . 'page_excel.png',
                   'export_for_results_import' => $imgLoc . 'brick_go.png',
                   'ghost_item' => $imgLoc . 'ghost16x16.png',
                   'user_group' => $imgLoc . 'group.png',
                   'heads_up' => $imgLoc . 'lightbulb.png',
                   'help' => $imgLoc . 'question.gif',
                   'history' => $imgLoc . 'history.png',
                   'history_small' => $imgLoc . 'history_small.png',
                   'home' => $imgLoc . 'application_home.png',
                   'import' => $imgLoc . 'door_in.png',
                   'import_results' => $imgLoc . 'monitor_lightning.png',
                   'inactive' => $imgLoc . 'flag_yellow.png',
                   'info' => $imgLoc . 'question.gif',
                   'info_small' => $imgLoc . 'information_small.png',
                   'insert_step' => $imgLoc . 'insert_step.png',
                   'item_link' => $imgLoc . 'folder_link.png',
                   'link_to_report' => $imgLoc . 'link.png',
                   'lock' => $imgLoc . 'lock.png',
                   'lock_open' => $imgLoc . 'lock_open.png',
                   'log_message' => $imgLoc . 'history.png',
                   'log_message_small' => $imgLoc . 'history_small.png',
                   'logout' => $imgLoc . 'computer_go.png',
                   'magnifier' => $imgLoc . 'magnifier.png',
                   'move_copy' => $imgLoc . 'application_double.png',
                   'new_f2_16' => $imgLoc . 'new_f2_16.png',
                   'note_edit' => $imgLoc . 'note_edit.png',
                   'note_edit_greyed' => $imgLoc . 'note_edit_greyed.png',
                   'on' => $imgLoc . 'lightbulb.png',
                   'off' => $imgLoc . 'lightbulb_off.png',
                   'order_alpha' => $imgLoc . 'style.png',
                   'plugins' => $imgLoc . 'connect.png',
                   'public' => $imgLoc . 'door_open.png',
                   'private' => $imgLoc . 'door.png',
                   'remove' => $imgLoc . 'delete.png',
                   'reorder' => $imgLoc . 'arrow_switch.png',
                   'report' => $imgLoc . 'report.png',
                   'report_word' => $imgLoc . 'page_word.png',
                   'requirements' => $imgLoc . 'cart.png',
                   'resequence' => $imgLoc . 'control_equalizer.png',
                   'reset' => $imgLoc . 'arrow_undo.png',
                   'saveForBaseline' => $imgLoc . 'lock.png',
                   'summary_small' => $imgLoc . 'information_small.png',
                   'sort' => $imgLoc . 'sort_hint.png',
                   'steps' => $imgLoc . 'bricks.png',
                   'table' => $imgLoc . 'application_view_columns.png',
                   'testcases_table_view' => $imgLoc . 'application_view_columns.png',
                   'testcase_execution_type_automatic' => $imgLoc . 'bullet_wrench.png',
                   'testcase_execution_type_manual' => $imgLoc . 'user.png',
                   'test_specification' => $imgLoc . 'chart_organisation.png',
                   'toggle_all' => $imgLoc .'toggle_all.gif',
                   'user' => $imgLoc . 'user.png',
                   'user_badge' => $imgLoc . 'employee-badge.png',
                   'upload' => $imgLoc . 'upload_16.png',
                   'upload_greyed' => $imgLoc . 'upload_16_greyed.png',
                   'warning' => $imgLoc . 'error_triangle.png',
                   'wrench' => $imgLoc . 'wrench.png',
                   'test_status_not_run' => $imgLoc . 'test_status_not_run.png',
                   'test_status_passed' => $imgLoc . 'test_status_passed.png',
                   'test_status_failed' => $imgLoc . 'test_status_failed.png',
                   'test_status_blocked' => $imgLoc . 'test_status_blocked.png',
                   'test_status_passed_next' => $imgLoc . 'test_status_passed_next.png',
                   'test_status_failed_next' => $imgLoc . 'test_status_failed_next.png',
                   'test_status_blocked_next' => $imgLoc . 'test_status_blocked_next.png',
                   'keyword_add' => $imgLoc . 'tag_blue_add.png');

    $imi = config_get('images');
    if(count($imi) >0) {
      $dummy = array_merge($dummy,$imi);
    }                 
    return $dummy;
	}

  /**
   *
   */
  static function getIMGTagsSet() 
  {
    $burl = isset($_SESSION['basehref']) ? $_SESSION['basehref'] : TL_BASE_HREF;
    $imgLoc = $burl . TL_THEME_IMG_DIR;
 
    $dummy = array('displayOnExec' => '<i class="fa fa-desktop"></i>'
                   ,'cog' => '<i class="fa fa-cog" aria-hidden="true"></i>'
                  );

    $msg = lang_get('show_hide_direct_link');
    $dummy['toggle_direct_link'] = 
      "<i class=\"fas fa-link\" title=\"{$msg}\" alt=\"{$msg}\" " .
      " onclick=\"showHideByClass('div','direct_link');event.stopPropagation();\" " .
      "></i>";

    return $dummy;
  }
  

  /**
   *
   */
  static function getFontawesomeSet() 
  {

    $dummy = array('active' => '<i class="fas fa-heart" title="%s"></i>',
                   'inactive' => '<i class="far fa-heart" title="%s"></i>');

    return $dummy;
  }


  /**
   *
   */
  function getLoginBackgroundImg($mode='random') 
  {

    $imgRepo = $this->dashioHome . 'img/login-background';
    switch($mode) {
      case 'random':
        $files = glob($imgRepo . '/*');
        $img = $files[rand(0, count($files) - 1)];
      break;

      case 'fixed':
        $img = $imgRepo . '/wp-testing04.jpg';
      break;
      
      case 'unsplash-random':
        $img='https://source.unsplash.com/random';
      break;

      case 'unsplash-daily':
        $img='https://source.unsplash.com/daily';
      break;

      default:
        $img=$mode;
      break;

    }
    return $img;
  }


} 
