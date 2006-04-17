<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: config.inc.php,v $
 *
 * @version $Revision: 1.56 $
 * @modified $Date: 2006/04/17 22:31:03 $ by $Author: asielb $
 *
 *
 * Constants and configuration parameters used throughout TestLink 
 * are defined within this file they should be changed for your environment
 *-------------------------------------------------------------------------
 * Revisions:
 *
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
 *------------------------------------------------------------------------
**/
/** 
 * config_db.inc.php is generated automatically with the use of the installer
 * otherwise you must manualy create this file, that include constants:
 * - DB host and DB name (DB_HOST, DB_NAME)
 * - DB user and password to connect (DB_USER, DB_PASS)
 * - DB type: define('DB_TYPE', 'mysql');
 */ 
require_once('config_db.inc.php');

// 20051227 - fm - for method connect() of database.class
define('DSN',FALSE);

/** root of testlink directory location seen through the web server */
define('TL_BASE_HREF', get_home_url()); 

/** Set this to TRUE if your MySQL DB supports UTF8 (MySQL version >= 4.1) */
define('DB_SUPPORTS_UTF8', TRUE);

/** GUI CHARSET 
 * Chinese users must comment the next line and uncomment the second one 
 * @todo translate Chinese from gb2312 to UTF-8
 **/
define('TL_TPL_CHARSET', DB_SUPPORTS_UTF8  ? 'UTF-8' : 'ISO-8859-1');
define('TL_XMLEXPORT_HEADER', "<?xml version=\"1.0\" encoding=\"".TL_TPL_CHARSET."\"?>\n");
//define('TL_TPL_CHARSET', 'gb2312'); // Chinese charset

/* Directory separator */
define('DS', DIRECTORY_SEPARATOR);

/** set the delimeter properly for the include_path */
define('DELIM', (PHP_OS == "WIN32" || PHP_OS == "WINNT") ? ';' : ':');

/** The root dir for the testlink installation with trailing slash */
define('TL_ABS_PATH', dirname(__FILE__) . DS);

/** The temporary dir for temporary files */
define('TL_TEMP_PATH', TL_ABS_PATH . 'gui'.DS.'templates_c'.DS);

/** Logging  @see logging.inc.php for more */
/** path for testlink logs; e.g. /tmp */
define('TL_LOG_PATH', TL_TEMP_PATH );


/** Default level of logging (NONE, ERROR, INFO, DEBUG, EXTENDED) */
define('TL_LOG_LEVEL_DEFAULT', 'NONE');
require_once(TL_ABS_PATH.'/lib/functions/logging.inc.php');

/** Is the metrics table displayed on the main page enabled? Accepts TRUE or FALSE values */
define('MAIN_PAGE_METRICS_ENABLED', 'FALSE');

/** some maxmima related to importing stuff in TL */
/** maximum uploadfile size */
define('TL_IMPORT_LIMIT', '204800'); // in bytes
/** maximum line size of the imported file */
define('TL_IMPORT_ROW_MAX', '10000'); // in chars


/** Bug Tracking systems */////////////////////////////////////////////////////
/** 
* TestLink uses bugtracking systems to check if displayed bugs resolved, verified, 
* and closed bugs. If they are it will strike through them
*/

/** 
* @var STRING TL_INTERFACE_BUGS = ['NO', 'BUGZILLA','MANTIS','JIRA']
* BUGZILLA: edit configuration in TL_ABS_PATH/cfg/bugzilla.cfg.php
* MANTIS  : edit configuration in TL_ABS_PATH/cfg/mantis.cfg.php
* JIRA    : edit configuration in TL_ABS_PATH/cfg/jira.cfg.php
*/
define('TL_INTERFACE_BUGS', 'NO');
require_once(TL_ABS_PATH . 'lib/bugtracking/int_bugtracking.php');

/** Setting up the global include path for testlink */
ini_set('include_path', '.' . DELIM . TL_ABS_PATH . 'lib' . DS . 'functions' . DS . DELIM);

/**
* Set the session timeout value (in minutes).
* This will prevent sessions timing out after very short periods of time
*/
//ini_set('session.cache_expire',900);

/**
 * Set the session garbage collection timeout value (in seconds)
 * The default session garbage collection in php is set to 1440 seconds (24 minutes)
 * If you want sessions to last longer this must be set to a higher value.
 * You may need to set this in your global php.ini if the settings don't take effect.
 */
//ini_set('session.gc_maxlifetime', 54000)

/** Error reporting - do we want php errors to show up for users */
error_reporting(E_ALL & ~E_NOTICE);
 
/** GUI related constants *///////////////////////////////////////////////////

/* CVS will not released, MUST BE changed at the release day */
define('TL_VERSION', '1.7.0 Alpha'); 
define('TL_BACKGROUND_DEFAULT', "#9BD");
define('TL_COOKIE_KEEPTIME', (time()+60*60*24*30)); // 30 days

/** 
*	Definition of tree menu component: dTree, jTree or phplayersmenu.
*	jTree has the best performance but others have a better functionality  
*	@varstatic string TL_TREE_KIND = [LAYERSMENU, DTREE, JTREE]
*/
define('TL_TREE_KIND', 'LAYERSMENU');

/* Some defines for I18N,L10N, don't touch */
define('TL_LOCALE_PATH',TL_ABS_PATH . 'locale/');
define('TL_HELP_RPATH','gui/help/');
define('TL_INSTRUCTIONS_RPATH','gui/help/');


/* Configure frmWorkArea frameset */
define('TL_FRMWORKAREA_LEFT_FRAME_WIDTH', "30%"); 


/* CSS configuration */
/* Standard */
define('TL_LOGIN_CSS','gui/css/tl_login.css');
define('TL_TESTLINK_CSS','gui/css/testlink.css');
define('TL_DOC_BASIC_CSS','gui/css/tl_doc_basic.css');


// 20060104 - fm
define('NON_TESTABLE_REQ','n');


/* An example
define('TL_LOGIN_CSS','gui/css/theme0/tl_login.css');
define('TL_TESTLINK_CSS','gui/css/theme0/testlink.css');
define('TL_DOC_BASIC_CSS','gui/css/theme0/tl_doc_basic.css');
*/

/* TRUE -> Check if:
           a. Product Name                   is unique
           b. Component Name Inside Product  is unique
           c. Category Name Inside Component is unique
           d. Test Case Name inside Category is unique 
   FALSE -> don't check
*/
$g_check_names_for_duplicates = TRUE;

/* 
if you have choose to check for unique names, what to do
when a duplicate name is found

'allow_repeat': allow the name to be repeated (backward compatibility)
'generate_new': generate a new name using $g_prefix_name_for_copy
'block'       : return with an error 

*/    
// $g_action_on_duplicate_name = 'allow_repeat';
$g_action_on_duplicate_name = 'generate_new';

/*  */
$g_prefix_name_for_copy = strftime("%Y%m%d-%H:%M:%S", time());
        
/* 
BUGID 0000086: Using "|" in the component or category name causes malformed URLs
regexp used to check for chars not allowed in product, component , category name, 
and testcase title 
*/
$g_ereg_forbidden = "[|]";

/* TRUE -> TL 1.5.1 compatibility, get also Test Plans without product id. */
$g_show_tp_without_prodid = TRUE;

// 20060219 - franciscom
$g_show_tp_without_tproject_id = $g_show_tp_without_prodid;


/* 
20051002 - fm
New Feature
TRUE -> user can enable/disable test plan filter by product 
        At user interface level a check box is displayed over
        the test plan combo box.
     
FALSE -> user can do nothing, no changes at UI.
         Test Plan always filtered by product
*/
$g_ui_show_check_filter_tp_by_testproject = TRUE;


/* TRUE -> you can create multiple time the same keyword for the same product */
$g_allow_duplicate_keywords=FALSE;

/*
Requirements - 

Test Case generation from Requirement
- use_req_spec_as_category_name
  FALSE -> test cases are created and assigned 
           to a category with name $g_req_cfg->default_category_name
  
  TRUE  -> REQuirement Specification Title is used a category name     
       
*/
$g_req_cfg->default_category_name="TODO";
$g_req_cfg->objective_for_category="Category/Test Cases generated from Requirements";

$g_req_cfg->default_component_name="Component Created by Requirement - Auto";
$g_req_cfg->scope_for_component="Component/Category/Test Cases generated from Requirements";

$g_req_cfg->use_req_spec_as_category_name = TRUE;


//20051002 - fm - Must be changed if Table definition changes
$g_field_size->category_name = 100;


/* fckeditor Toolbar */
//$g_fckeditor_toolbar = "TL_Medium";
$g_fckeditor_toolbar = "TL_Medium_2";


/* These are the supported locales */
$g_locales = array('en_GB' => 'English (UK)',
				   'it_IT' => 'Italian',
				   'es_AR' => 'Spanish (Argentine)',
				   'es_ES' => 'Spanish',
				   'de_DE' => 'German',
                   'fr_FR' => 'Fran&ccedil;ais',
                   'pt_BR' => 'Portuguese (Brazil)',
                   'zh_CN' => 'Chinese Simplified'
				          );

// ----------------------------------------------------------------------------
// 20051005 - fm - see strftime() in PHP manual
//
// Very IMPORTANT: 
// setting according local is done in testlinkInitPage() using set_dt_formats()
//
// Default values
$g_date_format ="%d/%m/%Y";
$g_timestamp_format = "%d/%m/%Y %H:%M:%S";

$g_locales_date_format = array('en_GB' => "%d/%m/%Y",
				                       'it_IT' => "%d/%m/%Y",
				                       'es_AR' => "%d/%m/%Y",
				                       'es_ES' => "%d/%m/%Y",
				                       'de_DE' => "%d.%m.%Y",
				                       'fr_FR' => "%d/%m/%Y",
				                       'pt_BR' => "%d/%m/%Y",
				                       'zh_CN' => "%Y年%m月%d日"
				                       ); 

$g_locales_timestamp_format = array('en_GB' => "%d/%m/%Y %H:%M:%S",
				                       'it_IT' => "%d/%m/%Y %H:%M:%S",
				                       'es_AR' => "%d/%m/%Y %H:%M:%S",
				                       'es_ES' => "%d/%m/%Y %H:%M:%S",
				                       'de_DE' => "%d.%m.%Y %H:%M:%S",
				                       'fr_FR' => "%d/%m/%Y %H:%M:%S",
				                       'pt_BR' => "%d/%m/%Y %H:%M:%S",
				                       'zh_CN' => "%Y年%m月%d日 %H时%M分%S秒"
				                           ); 
// ----------------------------------------------------------------------------



/** Your default locale, this must be one of $g_locales */
$language = 'en_GB';
// check for !== false because getenv() returns false on error
$serverLanguage = getenv($_SERVER['HTTP_ACCEPT_LANGUAGE']);
if(false !== $serverLanguage)
{
	if (array_key_exists($serverLanguage,$g_locales))
		$language = $serverLanguage;
}
define ('TL_DEFAULT_LOCALE',$language);

/* These are the possible TestCase statuses */
$g_tc_status = array ( "failed"        => 'f',
                       "blocked"       => 'b',
                       "passed"        => 'p',
                       "not_run"       => 'n',
                       "not_available" => 'x',
                       "unknown"       => 'u',
                       "all"           => 'all'
                      ); 


// 20060328 - franciscom
$g_tc_status_css = array_flip($g_tc_status);

//20050508 - fm - enhancement
/* TestCase Status Description -> color */
$g_tc_sd_color = array ( "failed"        => 'red',
                         "blocked"       => 'blue',
                         "passed"        => 'green',
                         "not_run"       => 'black',
                         "not_available" => 'yellow',
                         "unknown"       => 'black',
                         "all"           => 'cyan'
                       ); 

define("TL_ROLES_GUEST",5);
define("TL_ROLES_NONE",3);
define("TL_ROLES_NONE_DESC","<no rights>");
define("TL_ROLES_UNDEFINED",0);
define("TL_ROLES_UNDEFINED_DESC","<undefined>");

define("TL_DEFAULT_ROLEID",TL_ROLES_GUEST);

$g_tc_risks = array('L1', 'L2', 'L3','M1', 'M2', 'M3','H1', 'H2', 'H3');

# ------------------------------------------------------------------
# 20051106 - fm - Taken from mantis for phpmailer config
define ("SMTP_SEND",2);
$g_phpMailer_method = SMTP_SEND;

$g_tl_admin_email     = 'tl_admin@127.0.0.1';  #  
$g_from_email         = 'testlink_system@127.0.0.1';  # email sender
$g_return_path_email  = 'tl_admin@127.0.0.1';


// SMTP Configuration
$g_smtp_host        = 'localhost';  # SMTP server MUST BE configured  

// Configure only if SMTP server requires authentication
$g_smtp_username    = '';  # user  
$g_smtp_password    = '';  # password 
# ------------------------------------------------------------------


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
$g_username_format='name_surname';


// 20060207 - franciscom - BUGID 303
// Contributed by Tools-R-Us@Cognizant.com
// Should Test Results of older builds be editable?
// FALSE --> Not editable
// TRUE  --> Editable
$g_edit_old_build_results = FALSE;



/** 
* Testlink Smarty class sets up the default smarty settings for testlink
*/
require_once(TL_ABS_PATH . 'third_party/smarty/Smarty.class.php'); 
require_once(TL_ABS_PATH . 'lib/general/tlsmarty.inc.php'); 

/** 
 * Next constants are used in printed documents.
 * Leave them empty if you would not to use.
 */ 
define('TL_COMPANY', '');
define('TL_DOC_COPYRIGHT', '');
define('TL_DOC_CONFIDENT', '');


// 20051120 - fm 
define('ALL_PRODUCTS',0);
define('TP_ALL_STATUS',null);
define('FILTER_BY_PRODUCT',1);
define('FILTER_BY_TESTPROJECT',FILTER_BY_PRODUCT);
define('TP_STATUS_ACTIVE',1);

// 20060213 - franciscom
// logo for login page, if not defined nothing happens
define('LOGO_LOGIN_PAGE','<img alt="TestLink" src="icons/company_logo.png" />');


// 20060217 - franciscom - fo navbar page
define('LOGO_NAVBAR','<img alt="TestLink" src="icons/company_logo.png" />');


// characters used to surround the role description in the user interface
define('ROLE_SEP_START','[');
define('ROLE_SEP_END',']');

/* ATTACHMENTS */
/* some attachment related defines, no need to modify them */
define("TL_REPOSITORY_TYPE_DB",1);
define("TL_REPOSITORY_TYPE_FS",2);

define("TL_REPOSITORY_COMPRESSIONTYPE_NONE",1);
define("TL_REPOSITORY_COMPRESSIONTYPE_GZIP",2);

/* the maximum allowed file size for each repository entry, default 1MB */
define("TL_REPOSITORY_MAXFILESIZE_MB",1);
define("TL_REPOSITORY_MAXFILESIZE",1024*1024*TL_REPOSITORY_MAXFILESIZE_MB);

/* the type of the repository can be database or filesystem
* TL_REPOSITORY_TYPE_DB => database
* TL_REPOSITORY_TYPE_FS => filesystem
**/
$g_repositoryType = TL_REPOSITORY_TYPE_FS;
/* the where the filesystem repository should be located */
$g_repositoryPath = "c:\\muell";

/* compression used within the repository 
 * TL_REPOSITORY_COMPRESSIONTYPE_NONE => no compression
 * TL_REPOSITORY_COMPRESSIONTYPE_GZIP => gzip compression
*/
$g_repositoryCompressionType = TL_REPOSITORY_COMPRESSIONTYPE_NONE;
/* END ATTACHMENTS */

// 20050821 - fm - configurable templates this help is you want to use a non standard template 
$g_tpl = array();

// Standard
$g_tpl['tcView'] = "tcView.tpl";
$g_tpl['tcSearchView'] = "tcSearchView.tpl";
$g_tpl['tcEdit'] = "tcEdit.tpl";
$g_tpl['tcNew'] = "tcNew.tpl";
$g_tpl['execSetResults'] = "execSetResults.tpl";


// Custom
$g_tpl['tcView'] = "tcView.tpl";
$g_tpl['tcSearchView'] = $g_tpl['tcView'];

// 20051230 - fm
$g_tpl['usersview'] = "usersview.tpl";

// ----- End of Config ------------------------------------------------


// -------------------------------------------------------------------
function get_home_url()
{
if ( isset ( $_SERVER['PHP_SELF'] ) ) {
	$t_protocol = 'http';
	if ( isset( $_SERVER['HTTPS'] ) && ( strtolower( $_SERVER['HTTPS'] ) != 'off' ) ) {
		$t_protocol = 'https';
	}

	// $_SERVER['SERVER_PORT'] is not defined in case of php-cgi.exe
	if ( isset( $_SERVER['SERVER_PORT'] ) ) {
		$t_port = ':' . $_SERVER['SERVER_PORT'];
		if ( ( ':80' == $t_port && 'http' == $t_protocol )
		  || ( ':443' == $t_port && 'https' == $t_protocol )) {
			$t_port = '';
		}
	} else {
		$t_port = '';
	}

	if ( isset( $_SERVER['HTTP_HOST'] ) ) {
		$t_host = $_SERVER['HTTP_HOST'];
	} else if ( isset( $_SERVER['SERVER_NAME'] ) ) {
		$t_host = $_SERVER['SERVER_NAME'] . $t_port;
	} else if ( isset( $_SERVER['SERVER_ADDR'] ) ) {
		$t_host = $_SERVER['SERVER_ADDR'] . $t_port;
	} else {
		$t_host = 'www.example.com';
	}

	$t_path = dirname( $_SERVER['PHP_SELF'] );
	if ( '/' == $t_path || '\\' == $t_path ) {
		$t_path = '';
	}

	$t_url	= $t_protocol . '://' . $t_host . $t_path.'/';
	
	return ($t_url);
}

}

//includes needed for userright checking
require_once(TL_ABS_PATH . 'lib/functions/roles.inc.php');
require_once(TL_ABS_PATH . 'cfg/userrightmatrix.php');
?>
