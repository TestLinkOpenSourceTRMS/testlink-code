<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * Load core functions for TestLink GUI
 * Common functions: database connection, session and data initialization,
 * maintain $_SESSION data, redirect page, log, etc.
 * 
 * Note: this file must uses only globally used functionality and cannot include 
 * a feature specific code because of performance and readability reasons
 *
 * @filesource	common.php
 * @package 	TestLink
 * @copyright 	2005,2011 TestLink community 
 * @link 		http://www.teamst.org/index.php
 *
 * @internal revisions
 * 20110502 - franciscom - testlinkInitPage() logic changed
 * 20110430 - franciscom - checkSecurityClearance()
 * 20110416 - franciscom - setSessionProject() -> setCurrentProject()
 * 20110415 - Julian - BUGID 4418: Clean up priority usage within Testlink
 *                                  -> priority_to_level() uses urgencyImportance
 * 20110321 - franciscom - 	BUGID 4025: option to avoid that obsolete test cases 
 *							can be added to new test plans
 *							getConfigAndLabels($configKey,$accessMode='key')
 *
 *  20101028 - asimon - BUGID 3951: Status and Type for requirements are not saved
 *  20101025 - Julian - BUGID 3930 - added function split_localized_date()
 *                                 - simplified function is_valid_date()
 *  20101022 - asimon - BUGID 3716: added is_valid_date()
 *	20100904 - eloff - BUGID 3740 - redirect to destination after login
 * 	20100714 - asimon - BUGID 3601: show req spec link only when req mgmt is enabled
 *	20100616 - eloff - config_get: log warning when requested option does not exist
 * 	20100310 - franciscom - changes to make code compatible with smarty 3
 * 	20100207 - havlatm - cleanup
 * 	20100124 - eloff - added $redirect parameter to checkSessionValid()
 * 	20100124 - eloff - BUGID 3012 - added buildExternalIdString()
 */

/** core and parenthal classes */
require_once('object.class.php');
require_once('metastring.class.php');

/** library for localization */
require_once('lang_api.php');

/** logging functions */
require_once('logging.inc.php');
require_once('logger.class.php');
require_once('pagestatistics.class.php');

/** library of database wrapper */
require_once('database.class.php');

/** user right checking */
require_once('roles.inc.php');

/** Testlink Smarty class wrapper sets up the default smarty settings for testlink */
require_once('tlsmarty.inc.php');

// Needed to avoid problems with Smarty 3
spl_autoload_register('tlAutoload');


/** Input data validation */
require_once("inputparameter.inc.php");

/** @TODO use the next include only if it is used -> must be removed */
require_once("testproject.class.php"); 
require_once("treeMenu.inc.php");
require_once("exec_cfield_mgr.class.php");

/**
 * Automatic loader for PHP classes
 * See PHP Manual for details 
 */
// function __autoload($class_name) 
function tlAutoload($class_name) 
{
	// exceptions
	$tlClasses = null;
	$tlClassPrefixLen = 2;
	$classFileName = $class_name;
    
	if (isset($tlClasses[$classFileName]))
	{
    	$len = tlStringLen($classFileName) - $tlClassPrefixLen;
		$classFileName = strtolower(tlSubstr($classFileName,$tlClassPrefixLen,$len));
	} 
    require_once $classFileName . '.class.php';
}


// ----- End of loading and begin functions ---------------------------------------------

/** @var integer global main DB connection identifier */
$db = 0;


/**
 * TestLink connects to the database
 *
 * @return array
 *         aa['status'] = 1 -> OK , 0 -> KO
 *         aa['dbms_msg''] = 'ok', or $db->error_msg().
 */
function doDBConnect(&$db)
{
	global $g_tlLogger;
	
	$charSet = config_get('charset');
	$result = array('status' => 1, 'dbms_msg' => 'ok');

	$db = new database(DB_TYPE);
	$result = $db->connect(DSN, DB_HOST, DB_USER, DB_PASS, DB_NAME);

	if (!$result['status'])
	{
		echo $result['dbms_msg'];
		$result['status'] = 0;
		tLog('Connect to database fails!!! ' . $result['dbms_msg'], 'ERROR');
	}
	else
	{
		if((DB_TYPE == 'mysql') && ($charSet == 'UTF-8'))
		{
			$db->exec_query("SET CHARACTER SET utf8");
			$db->exec_query("SET collation_connection = 'utf8_general_ci'");
		}
	}
	
	// if we establish a DB connection, we reopen the session, 
	// to attach the db connection
	$g_tlLogger->endTransaction();
	$g_tlLogger->startTransaction();
	
	return $result;
}


/**
 * Set session data related to the current test plan
 * and saves a cookie with current testplan id
 * 
 * @param array $tplan_info result of DB query
 */
function setSessionTestPlan($tplan_info)
{
	if ($tplan_info)
	{
		$_SESSION['testplanID'] = $tplan_info['id'];
		$_SESSION['testplanName'] = $tplan_info['name'];
		// Save testplan id for next session
		setcookie('TL_lastTestPlanForUserID_' . 1, $tplan_info['id'], TL_COOKIE_KEEPTIME, '/');

		tLog("Test Plan was adjusted to '" . $tplan_info['name'] . "' ID(" . $tplan_info['id'] . ')', 'INFO');
	}
	else
	{
		unset($_SESSION['testplanID']);
		unset($_SESSION['testplanName']);
	}
}


/**
 * Set home URL path
 * @internal Revisions:
 * 200806 - havlatm - removed rpath
 */
function setPaths()
{
	if (!isset($_SESSION['basehref']))
	{
		$_SESSION['basehref'] = get_home_url();
	}	
}


/** 
 * Verify if user is log in. Redirect to login page if not.
 * 
 * @param integer $db DB identifier 
 * @param boolean $redirect if true (default) redirects user to login page, 
 * 							otherwise returns true/false as login status
 **/
function checkSessionValid(&$db, $redirect=true)
{
	$isValidSession = false;
	if (isset($_SESSION['userID']) && $_SESSION['userID'] > 0)
	{
		/** @TODO martin: 
		    Talk with Andreas to understand:
		    1. advantages of this approach
		    2. do we need to recreate it every time ? why ?
		   
		 * a) store just data -not all object
		 * b) do not read again and again the same data from DB
		 * c) this function check JUST session validity
		 **/
		$now = time();
		if (($now - $_SESSION['lastActivity']) <= (config_get("sessionInactivityTimeout") * 60))
		{
			$_SESSION['lastActivity'] = $now;
			$user = new tlUser($_SESSION['userID']);
			$user->readFromDB($db);
			$_SESSION['currentUser'] = $user;
			$isValidSession = true;
		}
	}
	if (!$isValidSession && $redirect)
	{
        $ip = $_SERVER["REMOTE_ADDR"];
	    tLog('Invalid session from ' . $ip . '. Redirected to login page.', 'INFO');
		
		$fName = "login.php";
        $baseDir = dirname($_SERVER['SCRIPT_FILENAME']);
        
        while(!file_exists($baseDir . DIRECTORY_SEPARATOR .$fName))
        {
            $fName = "../" . $fName;
        }
		$destination = "&destination=" . urlencode($_SERVER['REQUEST_URI']);
        redirect($fName . "?note=expired" . $destination,"top.location");
        exit();
	}
	return $isValidSession;
}


/**
 * Start session
 */
function doSessionStart()
{
	session_set_cookie_params(99999);
	if(!isset($_SESSION))
	{
		session_start();
	}
}


/**
 * Initialize structure of top menu for the user and the project.
 * 
 * @param integer $db DB connection identifier
 * @param object $userObj user object
 * @param integer $tproject_id test project identifier
 * @param integer $tplan_id test plan identifier
 * @param boolean $reqMgmtEnabled
 * 
 * @since 1.9
 *
 * @internal revisions
 *  20100714 - asimon - BUGID 3601: show req spec link only when req mgmt is enabled
 *	20091119 - franciscom - removed global coupling 
 */
function initTopMenu(&$db,&$userObj,$tproject_id,$tplan_id,$reqMgmtEnabled)
{
	$menuString = '';
	$guiTopMenu = config_get('guiTopMenu');

	// check if Project is available
	if ($tproject_id > 0)
	{
		$idx = 1;	
    	foreach ($guiTopMenu as $element)
		{
			// check if Test Plan is available
			// BUGID 3601: check also if req mgmt is enabled
			if( 
				 ( !isset($element['condition']) || ($element['condition'] == '') ) ||
				 ( ($element['condition'] == 'TestPlanAvailable') && $tplan_ID > 0 ) ||
				 ( ($element['condition'] == 'ReqMgmtEnabled') && $reqMgmtEnabled )
			  )
			{
				// IMPORTANT NOTICE
				// (is_null($element['right']) => no right needed => display always
				if (is_null($element['right']) || $userObj->hasRight($db,$element['right'],$tproject_id,$tplan_id))
				{
				
					$dummy = $element['url'];
					if($element['addTProject'])
					{
						// need to understand how to add: with ? or &
						$glue = (strpos($dummy,'?') === false) ? '?' : '&';
						$dummy .= $glue . "tproject_id={$tproject_id}"; 
					}
					if($element['addTPlan'])
					{
						// need to understand how to add: with ? or &
						$glue = (strpos($dummy,'?') === false) ? '?' : '&';
						$dummy .= $glue . "tplan_id={$tplan_id}"; 
					}
					
					$menuString .= 	"<a href='{$dummy}' " .
									"target='{$element['target']}' accesskey='{$element['shortcut']}'" .
	     							"tabindex=''" . $idx++ . "''>" . lang_get($element['label'])."</a> | ";
				}
			}
		}
	}
	return $menuString;
}

/**
 * General GUI page initialization procedure
 * - init session
 * - init database
 * 
 * @param integer $db DB connection identifier
 * @param boolean $checkSession (optional) 
 */
function testlinkInitPage(&$db,$checkSession=true)
{
	doSessionStart();
	setPaths();
	set_dt_formats();
	doDBConnect($db);
	
	static $pageStatistics = null;
	if (!$pageStatistics && (config_get('log_level') == 'EXTENDED'))
	{
		$pageStatistics = new tlPageStatistics($db);
	}
	
	if ($checkSession)
	{
		checkSessionValid($db);
	}
	
	// used to disable the attachment feature if there are problems with repository path
	/** @TODO this check should not be done anytime but on login and using */
	global $g_repositoryType;
	global $g_attachments;
	global $g_repositoryPath;
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
}


/**
 * Redirect page to another one
 *
 * @param   string   URL of required page
 * @param   string   Browser location - use for redirection or refresh of another frame
 * 					 Default: 'location'
 */
function redirect($path, $level = 'location')
{
	echo "<html><head></head><body>";
	echo "<script type='text/javascript'>";
	echo "$level.href='$path';";
	echo "</script></body></html>";
	exit;
}


/**
 * Security parser for input strings
 * 
 * @param string $parameter
 * @return string cleaned parameter
 */
function strings_stripSlashes($parameter,$bGPC = true)
{
	if ($bGPC && !ini_get('magic_quotes_gpc'))
		return $parameter;

	if (is_array($parameter))
	{
		$retParameter = null;
		if (sizeof($parameter))
		{
			foreach($parameter as $key=>$value)
			{
				if (is_array($value))
					$retParameter[$key] = strings_stripSlashes($value,$bGPC);
				else
					$retParameter[$key] = stripslashes($value);
			}
		}
		return $retParameter;
	}
	else
		return stripslashes($parameter);
}


function to_boolean($alt_boolean)
{
	$the_val = 1;

	if (is_numeric($alt_boolean) && !intval($alt_boolean))
	{
		$the_val = 0;
	}
	else
	{
		$a_bool	= array ("on" => 1, "y" => 1, "off" => 0, "n" => 0);
		$alt_boolean = strtolower($alt_boolean);
		if(isset($a_bool[$alt_boolean]))
		{
			$the_val = $a_bool[$alt_boolean];
		}
	}

	return $the_val;
}


/**
 * Validate string by relular expression
 *
 * @param string $str2check
 * @param string $regexp_forbidden_chars Regular expression (perl format)
 *
 * @return boolean 1: check ok, 0:check KO
 * 
 * @todo havlatm: remove as obsolete or move to inputparam.inc.php
 */
function check_string($str2check, $regexp_forbidden_chars)
{
	$status_ok = 1;

	if( $regexp_forbidden_chars != '' && !is_null($regexp_forbidden_chars))
	{
		if (preg_match($regexp_forbidden_chars, $str2check))
		{
			$status_ok=0;
		}
	}
	return $status_ok;
}


function set_dt_formats()
{
	global $g_date_format;
	global $g_timestamp_format;
	global $g_locales_date_format;
	global $g_locales_timestamp_format;

	if(isset($_SESSION['locale']))
	{
		if($g_locales_date_format[$_SESSION['locale']])
		{
			$g_date_format = $g_locales_date_format[$_SESSION['locale']];
		}
		if($g_locales_timestamp_format[$_SESSION['locale']])
		{
			$g_timestamp_format = $g_locales_timestamp_format[$_SESSION['locale']];
		}
	}
}


/**
 * Load global configuration to function
 * 
 * @param string $config_id key for identification of configuration parameter
 * @return mixed the configuration parameter(s)
 * 
 * @internal Revisions
 */
function config_get($config_id)
{
	$t_value = '';  
	$t_found = false;  
	$logInfo = array('msg' => "config option not available: {$config_id}", 'level' => 'WARNING');
	if(!$t_found)
	{
		$my = "g_" . $config_id;
		if( ($t_found = isset($GLOBALS[$my])) )
		{
			$t_value = $GLOBALS[$my];
		}
		else
		{
			$cfg = $GLOBALS['tlCfg'];
			if( ($t_found = property_exists($cfg,$config_id)) )
			{
				$t_value = $cfg->$config_id;
			}
		}
		
		if( $t_found )
		{
			$logInfo = array('msg' => "config option: {$config_id} is {$t_value}", 'level' => 'INFO');
		}
	}
	
	tLog($logInfo['msg'],$logInfo['level']);
	return $t_value;
}


/**  
 * @return boolean Return true if the parameter is an empty string or a string
 * containing only whitespace, false otherwise
 * @author Copyright (C) 2000 - 2004  Mantis Team, Kenzaburo Ito
 */ 
function is_blank( $p_var ) 
{
	$p_var = trim( $p_var );
	$str_len = strlen( $p_var );
	if ( 0 == $str_len ) {
		return true;
	}
	return false;
}


/**
 * Builds the header needed to make the content available for downloading
 *
 * @param string $content the content which should be downloaded
 * @param string $fileName the filename
 **/
function downloadContentsToFile($content,$fileName)
{
	$charSet = config_get('charset');

	ob_get_clean();
	header('Pragma: public' );
	header('Content-Type: text/plain; charset='. $charSet . '; name=' . $fileName );
	header('Content-Transfer-Encoding: BASE64;' );
	header('Content-Disposition: attachment; filename="' . $fileName .'"');
	echo $content;
}


/**
 * helper function for performance timing
 * 
 * @TODO havlatm: Andreas, move to logger?
 * returns: ?
 */
function microtime_float()
{
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}


/**
 * Converts a priority weight (urgency * importance) to HIGH, MEDUIM or LOW
 *
 * @return integer HIGH, MEDUIM or LOW
 */
function priority_to_level($priority) {
	$urgencyImportance = config_get('urgencyImportance');
	
	if ($priority >= $urgencyImportance->threshold['high']) {
		return HIGH;
	} else if ($priority < $urgencyImportance->threshold['low']) {
		return LOW;
	} else {
		return MEDIUM;
	}
}


/**
 * Get the named php ini variable but return it as a bool
 * 
 * @author Copyright (C) 2000 - 2004  Mantis Team, Kenzaburo Ito
 */
function ini_get_bool( $p_name ) {
	$result = ini_get( $p_name );

	if ( is_string( $result ) ) {
		switch ( $result ) {
			case 'off':
			case 'false':
			case 'no':
			case 'none':
			case '':
			case '0':
				return false;
				break;
			case 'on':
			case 'true':
			case 'yes':
			case '1':
				return true;
				break;
		}
	} else {
		return (bool)$result;
	}
}


/** @TODO martin: this is specific library and cannot be loaded via common.php
 * USE EXTRA LIBRARY             
// Contributed code - manish
$phpxmlrpc = TL_ABS_PATH . 'third_party'. DIRECTORY_SEPARATOR . 'phpxmlrpc' . 
             DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR;
require_once($phpxmlrpc . 'xmlrpc.inc');
require_once($phpxmlrpc . 'xmlrpcs.inc');
require_once($phpxmlrpc . 'xmlrpc_wrappers.inc');
*/


/**
* Initiate the execution of a testcase through XML Server RPCs.
* All the object instantiations are done here.
* XML-RPC Server Settings need to be configured using the custom fields feature.
* Three fields each for testcase level and testsuite level are required.
* The fields are: server_host, server_port and server_path.
* Precede 'tc_' for custom fields assigned to testcase level.
*
* @param $testcase_id: The testcase id of the testcase to be executed
* @param $tree_manager: The tree manager object to read node values and testcase and parent ids.
* @param $cfield_manager: Custom Field manager object, to read the XML-RPC server params.
* @return map:
*         keys: 'result','notes','message'
*         values: 'result' -> (Pass, Fail or Blocked)
*                 'notes' -> Notes text
*                 'message' -> Message from server
*/
/*
function executeTestCase($testcase_id,$tree_manager,$cfield_manager){

	//Fetching required params from the entire node hierarchy
	$server_params = $cfield_manager->getXMLServerParams($testcase_id);

  $ret=array('result'=>AUTOMATION_RESULT_KO,
             'notes'=>AUTOMATION_NOTES_KO, 'message'=>'');

	$server_host = "";
	$server_port = "";
	$server_path = "";
  $do_it=false;
	if( ($server_params != null) or $server_params != ""){
		$server_host = $server_params["xml_server_host"];
		$server_port = $server_params["xml_server_port"];
		$server_path = $server_params["xml_server_path"];
	  $do_it=true;
	}

  if($do_it)
  {
  	// Make an object to represent our server.
  	// If server config objects are null, it returns an array with appropriate values
  	// (-1 for executions results, and fault code and error message for message.
  	$xmlrpc_client = new xmlrpc_client($server_path,$server_host,$server_port);

  	$tc_info = $tree_manager->get_node_hierarchy_info($testcase_id);
  	$testcase_name = $tc_info['name'];

  	//Create XML-RPC Objects to pass on to the the servers
  	$myVar1 = new xmlrpcval($testcase_name,'string');
  	$myvar2 = new xmlrpcval($testcase_id,'string');

  	$messageToServer = new xmlrpcmsg('ExecuteTest', array($myVar1,$myvar2));
  	$serverResp = $xmlrpc_client->send($messageToServer);

  	$myResult=AUTOMATION_RESULT_KO;
  	$myNotes=AUTOMATION_NOTES_KO;

  	if(!$serverResp) {
  		$message = lang_get('test_automation_server_conn_failure');
  	} elseif ($serverResp->faultCode()) {
  		$message = lang_get("XMLRPC_error_number") . $serverResp->faultCode() . ": ".$serverResp->faultString();
  	}
  	else {
  		$message = lang_get('test_automation_exec_ok');
  		$arrayVal = $serverResp->value();
  		$myResult = $arrayVal->arraymem(0)->scalarval();
  		$myNotes = $arrayVal->arraymem(1)->scalarval();
  	}
  	$ret = array('result'=>$myResult, 'notes'=>$myNotes, 'message'=>$message);
  } //$do_it

	return $ret;
} // function end
*/


/**
 * Trim string and limit to N chars
 * 
 * @param string
 * @param int [len]: how many chars return
 *
 * @return string trimmed string
 *
 * @author Francisco Mancardi - 20050905 - refactoring
 */
function trim_and_limit($s, $len = 100)
{
	$s = trim($s);
	if (tlStringLen($s) > $len) {
		$s = tlSubStr($s, 0, $len);
	}

	return $s;
}


/** @todo havlatm - 20100207 - what's that? and why here. Remove' */
// nodes_order format:  NODE_ID-?,NODE_ID-?
// 2-0,10-0,3-0
function transform_nodes_order($nodes_order,$node_to_exclude=null)
{
  $fa = explode(',',$nodes_order);

  foreach($fa as $key => $value)
  {
	// $value= X-Y
	$fb = explode('-',$value);

	if( is_null($node_to_exclude) || $fb[0] != $node_to_exclude)
  {
     $nodes_id[]=$fb[0];
  }
  }

  return $nodes_id;
}


/**
 * Checks $_FILES for errors while uploading
 * 
 * @param array $fInfo an array used by uploading files ($_FILES)
 * @return string containing an error message (if any)
 */
function getFileUploadErrorMessage($fInfo)
{
	$msg = null;
	if (isset($fInfo['error']))
	{
		switch($fInfo['error'])
		{
			case UPLOAD_ERR_INI_SIZE:
				$msg = lang_get('error_file_size_larger_than_maximum_size_check_php_ini');
				break;
			case UPLOAD_ERR_FORM_SIZE:
				$msg = lang_get('error_file_size_larger_than_maximum_size');
				break;
			case UPLOAD_ERR_PARTIAL:
			case UPLOAD_ERR_NO_FILE:
				$msg = lang_get('error_file_upload');
				break;
		}
	}
	return $msg;
}


/**
 * Redirect to a page with static html defined in locale/en_GB/texts.php
 * 
 * @param string $key keyword for finding exact html text in definition array
 */
function show_instructions($key, $refreshTree=0)
{
    $myURL = $_SESSION['basehref'] . "lib/general/staticPage.php?key={$key}";
    
    if( $refreshTree )
    {
        $myURL .= "&refreshTree=1";  
    }
  	redirect($myURL);
}


/**
 * @TODO: franciscom - 20091003 - document return value
 */
function templateConfiguration($template2get=null)
{
	$custom_templates = config_get('tpl');
	$access_key = $template2get;
	if( is_null($access_key) )
	{
		$access_key = str_replace('.php','',basename($_SERVER['SCRIPT_NAME']));
	}
	
	$path_parts=explode("/",dirname($_SERVER['SCRIPT_NAME']));
    $last_part=array_pop($path_parts);
    $tcfg = new stdClass();
    $tcfg->template_dir = "{$last_part}/";
    $tcfg->default_template = isset($custom_templates[$access_key]) ? $custom_templates[$access_key] : ($access_key . '.tpl');
    $tcfg->template = null;
    return $tcfg;
}


/**
 * Check if an string is a valid ISO date/time
 *          accepted format: YYYY-MM-DD HH:MM:SS
 * 
 * @param string $ISODateTime datetime to check
 * @return boolean True if string has correct format
 * 
 * @internal   
 * rev: 20080907 - franciscom - Code taked form PHP manual
 */
function isValidISODateTime($ISODateTime)
{
   $dateParts=array('YEAR' => 1, 'MONTH' => 2 , 'DAY' => 3);
   
   $matches=null;
   $status_ok=false;
   if (preg_match("/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $ISODateTime, $matches)) 
   {
       $status_ok=checkdate($matches[$dateParts['MONTH']],$matches[$dateParts['DAY']],$matches[$dateParts['YEAR']]);
   }
   return $status_ok;
}

/**
 * Check if a localized timestamp is valid
 * uses split_localized_date()
 *
 */
function is_valid_date($timestamp, $dateFormat) {
	$date_array = split_localized_date($timestamp,$dateFormat);
	
	$status_ok = false;
	if ($date_array != null) {
		$status_ok = checkdate($date_array['month'],$date_array['day'],$date_array['year']);
	}
	
	return $status_ok;
}

/**
 * Returns array containing date pieces for a given timestamp according to dateFormat
 */

function split_localized_date($timestamp,$dateFormat) {
	
	$splitChar = ".";
	if (strpos($timestamp,"-") !== false) {
		$splitChar = "-";
	} 
	if (strpos($timestamp,"/") !== false) {
		$splitChar = "/";
	}
	
	// strip splitchar
	$strippedDateFormat = str_replace($splitChar,"",$dateFormat);
	// strip %
	$strippedDateFormat = str_replace("%","",$strippedDateFormat);
	
	// put each char of strippedDateFormat into an Array Element
	$dateFormatArray = preg_split('//', $strippedDateFormat, -1, PREG_SPLIT_NO_EMPTY);
	
	// cut timestamp in pieces
    $date_pieces = explode($splitChar,$timestamp);
    
    $ok_pieces_qty = 3;
	$date_array = array();
    if( count($date_pieces) == $ok_pieces_qty ) {
    
		foreach ($dateFormatArray as $key => $char) {
			switch ($char) {
				case "Y":
					$date_array['year'] = $date_pieces[$key];
					break;
				case "m":
					$date_array['month'] = $date_pieces[$key];
					break;
				case "d":
					$date_array['day'] = $date_pieces[$key];
					break;					
			}
		}
    }
    
    return $date_array;
}


/**
 * 
 *
 */
function checkUserRightsFor(&$db,$pfn)
{
	$script = basename($_SERVER['PHP_SELF']);
	$currentUser = $_SESSION['currentUser'];
	$doExit = false;
	$action = null;
	if (!$pfn($db,$currentUser,$action))
	{
		if (!$action)
		{
			$action = "any";
		}
		logAuditEvent(TLS("audit_security_user_right_missing",$currentUser->login,$script,$action),
					  $action,$currentUser->dbID,"users");
		$doExit = true;
	}
	if ($doExit)
	{  	
		$myURL = $_SESSION['basehref'];
	  	redirect($myURL,"top.location");
		exit();
	}
}


function tlStringLen($str)
{
	$charset = config_get('charset');	
	$nLen = iconv_strlen($str,$charset);
	if ($nLen === false)
	{
		throw new Exception("Invalid UTF-8 Data detected!");
	}
	return $nLen; 
}


function tlSubStr($str,$start,$length = null)
{
	$charset = config_get('charset');
	if ($length === null)
	{
		$length = iconv_strlen($str,$charset);
	}	
	// BUGID 3951: replaced iconv_substr() by mb_substr()
	$function_call = "mb_substr";
	if (function_exists('iconv_substr') && version_compare(PHP_VERSION, '5.2.0') >= 0) {
		$function_call = "iconv_substr";
	}
	return $function_call($str,$start,$length,$charset);
}

/**
 * Get text from a configured item template for editor objects
 * 
 * @param $itemTemplate identifies a TestLink item that can have
 *        templates that can be loaded when creating an item to semplify
 *        or guide user's work.
 *        $itemTemplate is a property (of type stdClass) of $tlCfg configuration object.
 *
 *        supported values:
 *        testcase_template
 *
 * @param $webEditorName webeditor name, that identifies a propety of $tlCfg->$itemTemplate
 *        that holds input tenmplate configuration
 * 
 * @param $defaultText text to use if:
 *        $tlCfg->itemTemplate OR $tlCfg->itemTemplate->$webEditorName 
 *        does not exists.
 *
 */
function getItemTemplateContents($itemTemplate, $webEditorName, $defaultText='') 
{
    $editorTemplate = config_get($itemTemplate);
    $value=$defaultText;
    if( !is_null($editorTemplate) )
    {
      if (property_exists($editorTemplate, $webEditorName)) 
      {
      	switch($editorTemplate->$webEditorName->type)
      	{
      		case 'string':
      			$value = $editorTemplate->$webEditorName->value;
      			break;
      			 
      		case 'string_id':
      			$value = lang_get($editorTemplate->$webEditorName->value);
      			break;
      			 
      		case 'file':
      			$value = getFileContents($editorTemplate->$webEditorName->value);
				if (is_null($value))
				{
					$value = lang_get('problems_trying_to_access_template') . 
					         " {$editorTemplate->$webEditorName->value} ";
				}	
      			break;
      			 
      		default:
      			$value = '';
      			break;
      	}
      }
    }
    return $value; 
}


/**
 * Builds a string $testCasePrefix . $glueChar . $external_id
 *
 * @param string $testCasePrefix prefix for the project without glue character
 * @param mixed $external_id
 */
function buildExternalIdString($testCasePrefix, $external_id)
{
	static $glueChar;
	if (!$glueChar) {
		$glueChar = config_get('testcase_cfg')->glue_character;
	}
	return $testCasePrefix . $glueChar . $external_id;

}


/*
	return map with config values and strings translated (using lang_get()) 
	to be used on user interface  for a Test link configuration option that 
	is structure in this way:
  	config_option = array( string_value => any_value, ...)

  	All this works if TL_ strings defined on strings.txt follows this naming standard.  
  	For a config option like:
  	$tlCfg->workflowStatus=array('draft' => 1, 'review' => 2);
  
  	will exists:  $TL_workflowStatus_draft='...';
    	          $TL_workflowStatus_review='...';
 
  	@param string configKey: valus used on call to standard test link
                           	 method to get configuration option
  	@param string accessMode: two values allowed 'key', 'code'
                              indicates how the returned map must be indexed.
                              'key' => will be indexed by string                          
                                       value that is key of config option
                              'code' => will be indexed by value of config option         
  @example
   $tlCfg->workflowStatus=array('draft' => 1, 'review' => 2);
   $i18nlabels = getLabels('workflowStatus','key');
   array_keys($i18nlabels) will return array('draft','review');
   
   $tlCfg->workflowStatus=array('draft' => 1, 'review' => 2);
   $i18nlabels = getLabels('workflowStatus','code');
   array_keys($i18nlabels) will return array(1,2);
   
   @internal revisions
   20110321 - franciscom - BUGID 4025: option to avoid that obsolete test cases can be added to new test plans
*/
function getConfigAndLabels($configKey,$accessMode='key')
{
    $stringKeyCode = config_get($configKey);
    $labels=null;
    foreach( $stringKeyCode as $accessKey => $code )
    {
      $index = ($accessMode == 'key') ? $accessKey : $code;
      $labels[$index] = lang_get($configKey . '_' . $accessKey);
    }
    
    return array('cfg' => $stringKeyCode, 'lbl' => $labels); 
}


/*
   @internal revisions
   20110416 - franciscom - 
*/
function getCurrentTProjectID()
{
	$cookieID = config_get('current_tproject_id_cookie');
	$ret = isset($_COOKIE[$cookieID]) ? $_COOKIE[$cookieID] : 0;
	return $ret;
}

function setCurrentTProjectID($tprojectID)
{
	$cookieID = config_get('current_tproject_id_cookie');
	setcookie($cookieID,$tprojectID,true,'/');
}




function checkSecurityClearance(&$dbHandler,&$userObj,$context,$rightsToCheck,$checkMode)
{
	$script = basename($_SERVER['PHP_SELF']); // name of caller script
	$currentUser = $_SESSION['currentUser'];
	$doExit = false;
	$action = 'any';
	$myContext = array('tproject_id' => 0, 'tplan_id' => 0);
	$myContext = array_merge($myContext, $context);

	
	if( $doExit = (is_null($myContext) || $myContext['tproject_id'] == 0) )
	{
		logAuditEvent(TLS("audit_security_no_environment",$script), $action,$user->dbID,"users");
	}
	 
	if( !$doExit )
	{
		foreach($rightToCheck as $verboseRight)
		{
			$status = $userObj->hasRight($dbHandler,$verboseRight,
										 $myContext['tproject_id'],$myContext['tplan_id']);
	
			if( ($doExit = !$status) && ($checkMode == 'and'))
			{	
				$action = 'any';
				logAuditEvent(TLS("audit_security_user_right_missing",$userObj->login,$script,$action),
						  	  $action,$user->dbID,"users");
				break;
			}
		}
	}
	if ($doExit)
	{  	
	  	redirect($_SESSION['basehref'],"top.location");
		exit();
	}
}
?>