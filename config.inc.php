<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: config.inc.php,v $
 *
 * @version $Revision: 1.102 $
 * @modified $Date: 2007/02/19 14:03:42 $ by $Author: schlundus $
 *
 * SCOPE:
 * Constants and configuration parameters used throughout TestLink 
 * are defined within this file they should be changed for your environment
 *-----------------------------------------------------------------------------
 * Revisions:
 *
 * 20070218 - franciscom - added $g_spec_cfg->show_tsuite_filter
 *                               $g_spec_cfg->automatic_tree_refresh
 *                               REFRESH_SPEC_TREE
 *                               $g_spec_cfg->steps_results_layout
 *
 * 20070212 - franciscom - $g_exec_cfg->can_delete_execution
 * 20070207 - franciscom - $g_testcase_cfg->can_edit_executed
 * 20070205 - franciscom - $g_risk,$g_importance,$g_priority
 *
 * 20070203 - franciscom - added TL_TESTPROJECT_COLORING
 *
 * 20070131 - franciscom - added g_req_cfg->reqdoc_id->is_system_wide
 * 20070120 - franciscom - added check for TL_LOG_LEVEL_DEFAULT
 * 20070110 - havlatm - refactorization; unchangable const moved to const.inc.php
 * 20070105 - franciscom - added $g_gui->custom_fields->sizes
 * 20061016 - franciscom - added new keys to $g_field_size
 * 20061009 - franciscom - changed $g_req_cfg
 * 20060822 - franciscom - new properties for $g_attachments
 *                         enabled and disabled_msg
 *
 * 20060820 - franciscom - trying to remove useless CSS
 *
 * 20060602 - franciscom - new config param to manage attachments
 *                         different models for the attachments management 
 *                         on execution page.
 *
 * 20060528 - franciscom - new config param to choose order of execution history
 *                         $g_tc_status_for_ui for generation of radio buttons
 *
 * 20060508 - franciscom - new config params for LDAP
 *
 * 20060207 - franciscom - reorder of element on $g_locales
 *            Again English UK is DEFAULT, rest of element ordered in
 *            alphabetical fashion
 *
 * 20060421 - azl - Added en_US locale
 * 20060207 - franciscom - some changes in config vars related to testproject
 * 20060223 - scs - added basic stuff for attachments
 * 20060207 - franciscom - BUGID 303
 * 20060205 - JBA - 	Remember last product (BTS 221)
 * 20060101 - fm - 	version 1.7.0 Alpha
 * 20051227 - fm - 	fixed BUGID 300
 * 20051204 - mht -	added HTTP_ACCEPT_LANGUAGE support; 
 *                  added patch for Chinese
 * 20051115 - fm - 	added constant for JIRA
 * 20051106 - fm - 	Adding configuration parameters to use PHPMAILER, to send mail.
 * 					The PHPMAILER solution uses code from Mantis Bugtracking System.
 * 20051022 - fm - 	added french locale and translations
 * 					added portuguese locale and translations
 * 20051011 - fm -	New constant to configure CSS files Boolean values managed 
 * 					with TRUE/FALSE instead of 1/0 .
 * 20051005 - fm -	new config structures to manage L18N for date and time format
 * 					$g_locales_date_format, $g_locales_timestamp_format
 * 20051004 - fm - 	$g_allow_duplicate_keywords
 * 20051002 - fm - 	Test Plan filtering by product related configuration parameters
 *   				$g_ui_show_check_filter_tp_by_testproject
 * 				 - 	New configuration parameters for Requirements Functionality
 * 20050919 - fm - 	g_timestamp_format
 * 20050915 - fm - 	from 1.6.Beta1 to 1.6.RC1
 * 20050908 - fm - 	New configuration parameters:
 * 					$g_check_names_for_duplicates
 *					$g_action_on_duplicate_name
 * 					$g_prefix_name_for_copy
 *					Fixed BUGID 0000086: Using "|" in the component or category name causes malformed URLs
 * 20050904 - fm - 	added $g_show_tp_without_prodid to manage TL 1.5.1 compatibility.
 * 20050827 - fm - 	changes in $g_tc_status, $g_tc_sd_color
 * 					new config parameters: $g_date_format, $g_fckeditor_toolbar
 * 20050822 - fm - 	$tpl -> $g_tpl
 * 20050821 - fm - 	template configuration/customization
 * 20050806 - fm - 	Changes to support the installer
 *
 *-----------------------------------------------------------------------------
 **/

// ----------------------------------------------------------------------------
/** [INITIALIZATION] - DO NOT CHANGE THE SECTION */
/** The root dir for the testlink installation with trailing slash */
define('TL_ABS_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

/** Include constants */
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cfg' . DIRECTORY_SEPARATOR.'const.inc.php');

/** Setting up the global include path for testlink */
ini_set('include_path',ini_get('include_path') .";". '.' . DELIM . TL_ABS_PATH . 'lib' . DS . 'functions' . DS . DELIM);

/** Include database consts (the file is generated automatically by TL installer) */ 
require_once('config_db.inc.php');



// ----------------------------------------------------------------------------
/** [LOCALIZATION] */

// Your first/suggested choice for default locale, this must be one of $g_locales (see cfg/const.inc.php).
// An attempt will be done to stablish the default locale 
// automatically using $_SERVER['HTTP_ACCEPT_LANGUAGE']

$language = 'en_GB'; // default

$serverLanguage = false;
// check for !== false because getenv() returns false on error
if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
	$serverLanguage = getenv($_SERVER['HTTP_ACCEPT_LANGUAGE']);
if(false !== $serverLanguage)
{
	if (array_key_exists($serverLanguage,$g_locales))
		$language = $serverLanguage;
}
define ('TL_DEFAULT_LOCALE', $language);

/** include support for localization */
require_once("lang_api.php");

/** Functions for check request status */
require_once('configCheck.php');

/** root of testlink directory location seen through the web server */
/*  20070106 - franciscom - this statement it's not 100% right      
    better use $_SESSION['basehref'] in the scripts. */      
define('TL_BASE_HREF', get_home_url()); 

// ----------------------------------------------------------------------------
/** [GLOBAL] */

/** Error reporting - do we want php errors to show up for users */
error_reporting(E_ALL);

/** Set the session timeout value (in minutes).
 * This will prevent sessions timing out after very short periods of time */
//ini_set('session.cache_expire',900);

/**
 * Set the session garbage collection timeout value (in seconds)
 * The default session garbage collection in php is set to 1440 seconds (24 minutes)
 * If you want sessions to last longer this must be set to a higher value.
 * You may need to set this in your global php.ini if the settings don't take effect.
 */
//ini_set('session.gc_maxlifetime', 54000)

// ----------------------------------------------------------------------------
/** [CHARSET] */

/** Set this to TRUE if your MySQL DB supports UTF8 (MySQL version >= 4.1) */
define('DB_SUPPORTS_UTF8', TRUE);

/** CHARSET - UTF-8 is only officially supported charset */
// ISO-8859-1 is there for backward compatability
$g_defaultCharset =  DB_SUPPORTS_UTF8  ? 'UTF-8' : 'ISO-8859-1';
define('TL_TPL_CHARSET', $g_defaultCharset);
define('TL_XMLEXPORT_HEADER', "<?xml version=\"1.0\" encoding=\"".TL_TPL_CHARSET."\"?>\n");



// ----------------------------------------------------------------------------
/** [LOGGING] */
  
/** @see logging.inc.php for more 
 * change path for testlink logs. For example "/tmp" instead of TL_TEMP_PATH */
define('TL_LOG_PATH', TL_TEMP_PATH );

/** Default level of logging (NONE, ERROR, INFO, DEBUG, EXTENDED) */
// added check to avoid notice message in the migration pages
if(!defined('TL_LOG_LEVEL_DEFAULT'))
{
	define('TL_LOG_LEVEL_DEFAULT', 'NONE');
}


// ----------------------------------------------------------------------------
/** [Bug Tracking systems] */
/** 
* TestLink uses bugtracking systems to check if displayed bugs resolved, verified, 
* and closed bugs. If they are it will strike through them
*/

/** 
* @var STRING TL_INTERFACE_BUGS = ['NO', 'BUGZILLA','MANTIS','JIRA','TRACKPLUS']
* BUGZILLA: edit configuration in TL_ABS_PATH/cfg/bugzilla.cfg.php
* MANTIS  : edit configuration in TL_ABS_PATH/cfg/mantis.cfg.php
* JIRA    : edit configuration in TL_ABS_PATH/cfg/jira.cfg.php
* TRACKPLUS : edit configuration in TL_ABS_PATH/cfg/trackplus.cfg.php
*/
define('TL_INTERFACE_BUGS', 'NO');



// ----------------------------------------------------------------------------
/** [authentication] */                 

/** Login authentication
 * possible values: '' or 'MD5' => use password stored on db
 *                   'LDAP'      => use password from LDAP Server
 */ 
$g_login_method				= 'MD5';

/** LDAP authentication must be only defined for $g_login_method = 'LDAP'*/
// LDAP authentication are developed by mantis project (www.mantisbt.org)
// Example: 
//	$g_ldap_bind_dn			= 'my_bind_user';
//	$g_ldap_bind_passwd	= 'my_bind_password';

$g_ldap_server			= 'localhost';
$g_ldap_port			= '389';
$g_ldap_root_dn			= 'dc=mycompany,dc=com';
$g_ldap_organization	= '';    # e.g. '(organizationname=*Traffic)'
$g_ldap_uid_field		= 'uid'; # Use 'sAMAccountName' for Active Directory
$g_ldap_bind_dn			= ''; // Left empty if you LDAP server allows anonymous binding 
$g_ldap_bind_passwd	= ''; // Left empty if you LDAP server allows anonymous binding 



// ----------------------------------------------------------------------------
/** [GUI] */

/** Is the metrics table displayed on the main page enabled? Accepts TRUE or FALSE values */
define('MAIN_PAGE_METRICS_ENABLED', 'FALSE');

/** some maxima related to importing stuff in TL */
// Maximum uploadfile size 
// Also check your PHP settings (default is usually 2MBs)
define('TL_IMPORT_LIMIT', '204800'); // in bytes

/** maximum line size of the imported file */
define('TL_IMPORT_ROW_MAX', '10000'); // in chars

/** Configure frmWorkArea navigator width */
define('TL_FRMWORKAREA_LEFT_FRAME_WIDTH', "30%"); 

/** CSS themes - modify if you create own*/
//define('TL_THEME_CSS_DIR','gui/css/theme_m0/');
//
define('TL_THEME_CSS_DIR','gui/themes/theme_m1/css/');

define('TL_TESTLINK_CSS',TL_THEME_CSS_DIR . 'testlink.css');
define('TL_LOGIN_CSS', TL_TESTLINK_CSS);

// 20070204 - franciscom
//define('TL_JOMLA_1_CSS', '');
define('TL_JOMLA_1_CSS', TL_THEME_CSS_DIR . 'jos_template_css.css');

// path to IMAGE directory - DO NOT ADD FINAL /
//define('TL_THEME_IMG_DIR','gui/images/theme_m0');
define('TL_THEME_IMG_DIR','gui/themes/theme_m1/images');

// logo for login page, if not defined nothing happens
define('LOGO_LOGIN_PAGE',
       '<img alt="TestLink" title="TestLink" src="' . TL_THEME_IMG_DIR . '/company_logo.png" />');

// logo for navbar page
define('LOGO_NAVBAR',
       '<img alt="TestLink" title="TestLink" src="' . TL_THEME_IMG_DIR . '/company_logo.png" />');

// use when componing an title using several strings
define('TITLE_SEP',' : ');
define('TITLE_SEP_TYPE2',' >> ');
define('TITLE_SEP_TYPE3',' - ');


// 20070203 - franciscom
// 'background'  -> standard behaivour
// 'none'        -> new behaivour no background color change 
//
// define('TL_TESTPROJECT_COLORING','background');
define('TL_TESTPROJECT_COLORING','none');

/** fckeditor Toolbar 
 * modify which icons will be available in html edit pages
 * refer to fckeditor configuration file 
 **/
$g_fckeditor_toolbar = "TL_Medium_2";



/* 
TRUE -> user can enable/disable test plan filter by 
        product (term used on TL < 1.7) / test project (term used on TL>= 1.7)
        At user interface level a check box is displayed over
        the test plan combo box.
FALSE -> user can do nothing, no changes at UI.
         Test Plan always filtered by product
*/
$g_ui_show_check_filter_tp_by_testproject = TRUE;

// 20051227 - fm - BUGID 300: Display name and surename in all user lists 
// $g_show_realname=TRUE; -> use the function format_username()
//                           to display user identification
//                           using $g_username_format
$g_show_realname = FALSE;

// used by function format_username()
// example: user ux555, real name= John Cook
// 'name_surname'          -> John Cook
// 'name_surname_login'    -> John Cook [ux555]
//$g_username_format='name_surname_login';
$g_username_format = 'name_surname';

/** characters used to surround the role description in the user interface */
define('ROLE_SEP_START','[');
define('ROLE_SEP_END',']');

/** true => icon edit will be added into <a href> as indication an edit features */
$g_gui->show_icon_edit=false;



// ----------------------------------------------------------------------------
/** [GUI: TREE] */

/** 
 * TREE MENU 
 *	Definition of tree menu component: dTree, jTree or phplayersmenu.
 *	jTree has the best performance but others have a better functionality  
 *	@varstatic string TL_TREE_KIND = [LAYERSMENU, DTREE, JTREE]
 */
define('TL_TREE_KIND', 'LAYERSMENU');

// When creating an node in the tree, when can choose if:
// Any node added independent of the type is added with order 0,
// then the initial display order will be by node id.
//
// An useful alternative is mantain, inside of a container two groups:
// one for test cases, and one for test suites.
// This can be achived assigned a default order different for every type of node.
//                 
// This values must be >= 0
//
$g_tree_node_ordering->default_testcase_order=100;
$g_tree_node_ordering->default_testsuite_order=1;



// ----------------------------------------------------------------------------
/** [GENERATED DOCUMENTATION] */
// Constants used in printed documents.
define('TL_DOC_BASIC_CSS', TL_THEME_CSS_DIR . 'tl_doc_basic.css');

// Leave them empty if you would not to use.
define('TL_DOC_COMPANY', "Testlink Development Team [configure using TL_DOC_COMPANY]");
define('TL_DOC_COMPANY_LOGO', 
       '<img alt="TestLink logo" title="configure using TL_DOC_COMPANY_LOGO" src="%BASE_HREF%' .
             TL_THEME_IMG_DIR . '/company_logo.png" />');
define('TL_DOC_COPYRIGHT', 'copyright - Testlink Development Team [configure using TL_DOC_COPYRIGHT]');
define('TL_DOC_CONFIDENT', 'this document is not confidential [configure using TL_DOC_CONFIDENT]');



// ----------------------------------------------------------------------------
/** [ATTACHMENTS] */

$g_attachments->enabled = TRUE;

/** the type of the repository can be database or filesystem
 * TL_REPOSITORY_TYPE_DB => database
 * TL_REPOSITORY_TYPE_FS => filesystem
 **/
$g_repositoryType = TL_REPOSITORY_TYPE_FS;

/** 
 * TL_REPOSITORY_TYPE_FS: the where the filesystem repository should be located
 * We recommend to change the directory for security reason. 
 **/
$g_repositoryPath = TL_ABS_PATH . "upload_area" . DS;

/** 
 * compression used within the repository 
 * TL_REPOSITORY_COMPRESSIONTYPE_NONE => no compression
 * TL_REPOSITORY_COMPRESSIONTYPE_GZIP => gzip compression
 */
$g_repositoryCompressionType = TL_REPOSITORY_COMPRESSIONTYPE_NONE;

// the maximum allowed file size for each repository entry, default 1MB.
// Also check your PHP settings (default is usually 2MBs)
define("TL_REPOSITORY_MAXFILESIZE_MB", 1);
define("TL_REPOSITORY_MAXFILESIZE", 1024*1024*TL_REPOSITORY_MAXFILESIZE_MB); // don't change


// TRUE -> when you upload a file you can give no title
$g_attachments->allow_empty_title = TRUE;

// $g_attachments->allow_empty_title == TRUE, you can ask the system
// to do something 
// 
// 'none' -> just write on db an empty title
$g_attachments->action_on_save_empty_title='none';
//$g_attachments->action_on_save_empty_title='use_filename';

// Remember that title is used as link description for download
// then if title is empty, what the system has to do when displaying ?
// 'show_icon'  -> the $g_attachments->access_icon will be used.
// 'show_label' -> the value of $g_attachments->access_string will be used .
$g_attachments->action_on_display_empty_title='show_icon';

$g_attachments->access_icon='<img src="' . TL_THEME_IMG_DIR . '/new_f2_16.png" style="border:none">';
$g_attachments->access_string="[*]";


// used to disable the attachment feature if there are problems with repository path
$g_attachments->disabled_msg = "";
if($g_repositoryType == TL_REPOSITORY_TYPE_FS)
{
  $ret = checkForRepositoryDir($g_repositoryPath);
  if(!$ret['status_ok'])
  {
	  $g_attachments->enabled = FALSE;
	  $g_attachments->disabled_msg = $ret['msg'];
  }
}


// ----------------------------------------------------------------------------
/** [Requirements] */

/** Test Case generation from Requirement
	- use_req_spec_as_category_name
  	FALSE -> test cases are created and assigned 
           to a category with name $g_req_cfg->default_category_name
  	TRUE  -> REQuirement Specification Title is used a category name     
*/
$g_req_cfg->use_req_spec_as_testsuite_name = TRUE;
$g_req_cfg->default_testsuite_name = "Test suite created by Requirement - Auto";
$g_req_cfg->testsuite_details = "<b>Test suite/Test Cases generated from Requirements</b>";
$g_req_cfg->testcase_summary_prefix = "<b>Test Case generated from Requirement</b><br>";


// true : you want req_doc_id UNIQUE IN THE WHOLE DB (system_wide)
// false: you want req_doc_id UNIQUE INSIDE a SRS
//
$g_req_cfg->reqdoc_id->is_system_wide=false;



// Used to force the max len of this field, during the automatic creation
// of requirements
$g_field_size->testsuite_name = 100;

// requirements and req_spec tables
$g_field_size->req_docid=32;
$g_field_size->req_title=100;
$g_field_size->requirement_title=100;


// ----------------------------------------------------------------------------
/** [SMTP] */

# 20051106 - franciscom - Taken from mantis for phpmailer config
define ("SMTP_SEND",2);
$g_phpMailer_method = SMTP_SEND;

$g_tl_admin_email     = 'tl_admin@127.0.0.1';         # for problem/error notification 
$g_from_email         = 'testlink_system@127.0.0.1';  # email sender
$g_return_path_email  = 'no_replay@127.0.0.1';

# Urgent = 1, Not Urgent = 5, Disable = 0
$g_mail_priority = 5;   

// SMTP Configuration
$g_smtp_host        = 'localhost';  # SMTP server MUST BE configured  

// Configure only if SMTP server requires authentication
$g_smtp_username    = '';  # user  
$g_smtp_password    = '';  # password 



// ----------------------------------------------------------------------------
/** [MISC] */

/** Check unique titles of Test Project, Test Suite and Test Case
 *  TRUE  => Check              [STANDARD BEHAIVOUR]
 *  FALSE => don't check
 **/
$g_check_names_for_duplicates = TRUE;

/** 
 * Action for duplication check (only if TRUE)
 * 'allow_repeat' => allow the name to be repeated (backward compatibility)
 * 'generate_new' => generate a new name using $g_prefix_name_for_copy
 * 'block'        => return with an error 
 **/    
$g_action_on_duplicate_name = 'generate_new';

/** Used when creating a Test Suite using copy 
   and you have choose  $g_action_on_duplicate_name = 'generate_new'
   if the name exist.
 */
$g_prefix_name_for_copy = strftime("%Y%m%d-%H:%M:%S", time());
        
/** 
BUGID 0000086: Using "|" in the component or category name causes malformed URLs
regexp used to check for chars not allowed in product, component , category name, 
and testcase title 
*/
$g_ereg_forbidden = "[|]";

/** Allow/disallow to have Test Plans without dependency to Test Project.
 * TRUE  => allow Test Plan over all Test Projects (TL 1.5 compatibility)
 * FALSE => all Test Plans should have own Test Project   [STANDARD BEHAIVOUR]
 **/
$g_show_tp_without_tproject_id = FALSE;

// obsolete (use $g_show_tp_without_tproject_id)
$g_show_tp_without_prodid = $g_show_tp_without_tproject_id;

// TRUE  -> you can create multiple time the same keyword 
//           for the same product (term used on TL < 1.7) / test project (term used on TL>= 1.7) 
// FALSE ->   [STANDARD BEHAIVOUR]
$g_allow_duplicate_keywords = FALSE;


/** @TODO description */
// TRUE   -> custom field logic will be executed  [STANDARD BEHAIVOUR]
// FALSE  -> no possibility to use custom fields
$g_gui->enable_custom_fields = TRUE;

// Applied to HTML inputs created to get/show custom field contents
// 
// For string,numeric,float,email: size & maxlenght of the input type text.
// For list,email size of the select input.
//
$g_gui->custom_fields->sizes = array( 
	'string' => 50,
	'numeric'=> 10,
	'float'  => 10,
	'email'  => 50,
	'list'   => 1,
	'multiselection list' => 5
);


/** [Risk, Priority, Importance] */
// item_id => item_label (must be defined in strings.txt file)
$g_risk=array( '1' => 'high_risk',
               '2' => 'medium_risk',
               '3' => 'low_risk');

$g_importance=array( 'H' => 'high_importance',
                     'M' => 'medium_importance',
                     'L' => 'low_importance');

$g_priority=array( 'A' => 'high_priority',
                   'B' => 'medium_priority',
                   'C' => 'low_priority');



/** [Test case] */

// 1 -> user can edit executed tc versions
// 0 -> editing of executed tc versions is blocked.  [STANDARD BEHAIVOUR]
$g_testcase_cfg->can_edit_executed=0;


/** [Test Plan] */
// TRUE  -> standard behaivour, user can remove assigned test cases
//          using the assign/add page.
//
// FALSE -> user need to use the remove page
//
$g_testplan_cfg->can_remove_tc_on_add=TRUE;  // To be developed


/** [Executions] */

// ASCending   -> last execution at bottom
// DESCending  -> last execution on top      [STANDARD BEHAIVOUR]
$g_exec_cfg->history_order='DESC';

// TRUE  -> the whole execution history for the choosen build will be showed
// FALSE -> just last execution for the choosen build will be showed [STANDARD BEHAIVOUR]
$g_exec_cfg->history_on=FALSE;


// TRUE  ->  test case VERY LAST (i.e. in any build) execution status 
//           will be displayed
// FALSE -> only last result on current build.  [STANDARD BEHAIVOUR]
$g_exec_cfg->show_last_exec_any_build=FALSE;


// different models for the attachments management on execution page
// $att_model_m1 ->  shows upload button and title 
//   
// $att_model_m2 ->  hides upload button and title
//
$g_exec_cfg->att_model = $att_model_m2;   //defined in const.inc.php


// 1 -> User can delete an execution result
// 0 -> User can not.  [STANDARD BEHAIVOUR]
$g_exec_cfg->can_delete_execution=0;




/** [Test case specification] */

// 'horizontal' ->  step and results on the same row
// 'vertical'   ->  steps on one row, results in the row bellow
//                   
$g_spec_cfg->steps_results_layout='horizontal';
//$g_spec_cfg->steps_results_layout='vertical';

// 1 -> User will see a test suite filter while creating test specification
// 0 -> no filter available
$g_spec_cfg->show_tsuite_filter=1;


// 1 -> every time user do some operation on test specification
//      tree is updated on screen.
// 0 -> tree will not be updated, user can update it manually.
//
$g_spec_cfg->automatic_tree_refresh=1;
if( $g_spec_cfg->automatic_tree_refresh)
{
  define('REFRESH_SPEC_TREE','yes');
}
else
{
  define('REFRESH_SPEC_TREE','no');
}




// ----- End of Config ------------------------------------------------
/** Include important libraries */

/** Bug tracking include */
$g_bugInterfaceOn = false;
$g_bugInterface = null;
if (TL_INTERFACE_BUGS != 'NO')
  require_once(TL_ABS_PATH . 'lib/bugtracking/int_bugtracking.php');


/** Testlink Smarty class sets up the default smarty settings for testlink */
require_once(TL_ABS_PATH . 'third_party/smarty/Smarty.class.php'); 
require_once(TL_ABS_PATH . 'lib/general/tlsmarty.inc.php'); 

/** logging functions */
require_once('logging.inc.php');

/** user right checking */
require_once(TL_ABS_PATH . 'lib/functions/roles.inc.php');
require_once(TL_ABS_PATH . 'cfg/userrightmatrix.php');

// ----- END OF FILE --------------------------------------------------
?>
