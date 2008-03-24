<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * @filesource $RCSfile: common.php,v $
 * @version $Revision: 1.101 $ $Author: havlat $
 * @modified $Date: 2008/03/24 19:33:27 $
 *
 * @author 	Martin Havlat
 * @author 	Chad Rosen
 *
 * Common functions: database connection, session and data initialization,
 * maintain $_SESSION data, redirect page, log, etc. 
 *
 * @var array $_SESSION
 * - user related data are adjusted via doAuthorize.php and here (product & test plan)  
 * - has next values: valid (yes/no), user (login name), role (e.g. admin),
 * email, userID, productID, productName, testplan (use rather testPlanID),
 * testPlanID, testPlanName
 *
 * 20080114 - franciscom - gen_spec_view(): adde external_id management.
 * 20071027 - franciscom - added ini_get_bool() from mantis code, needed to user
 *                         string_api.php, also from Mantis.
 * 
 * 20071002 - jbarchibald - BUGID 1051
 * 20070707 - franciscom - BUGID 921 - changes to gen_spec_view()
 * 20070705 - franciscom - init_labels()
 *                         gen_spec_view(), changes on process of inactive versions
 * 20070623 - franciscom - improved info in header of localize_dateOrTimeStamp()
 * 20070104 - franciscom - gen_spec_view() warning message removed
 *
 **/ 

/** library for localization */
require_once("lang_api.php");

/** library of database wrapper */
require_once("database.class.php");

/** user right checking */
require_once("roles.inc.php");

/** Testlink Smarty class sets up the default smarty settings for testlink */
require_once(TL_ABS_PATH . 'third_party'.DS.'smarty'.DS.'libs'.DS.'Smarty.class.php'); 
require_once(TL_ABS_PATH . 'lib'.DS.'general'.DS.'tlsmarty.inc.php'); 

/** logging functions */
require_once('logging.inc.php');

if ($g_interface_bugs != 'NO')
  require_once(TL_ABS_PATH.'lib'.DS.'bugtracking'.DS.'int_bugtracking.php');


require_once("object.class.php");
require_once("metastring.class.php");
require_once("logger.class.php");
require_once("role.class.php");
require_once("attachment.class.php");

/** @TODO use the next include only if it is used -> must be removed*/
require_once("user.class.php");
require_once("keyword.class.php");
require_once("testproject.class.php");
require_once("testplan.class.php");
require_once("testcase.class.php");
require_once("testsuite.class.php");
require_once("tree.class.php");
require_once("treeMenu.inc.php");
require_once("cfield_mgr.class.php");
require_once("exec_cfield_mgr.class.php");
require_once("plan.core.inc.php");
/** load the php4 to php5 domxml wrapper if the php5 is used and the domxml extension is not loaded **/
if (version_compare(PHP_VERSION,'5','>=') && !extension_loaded("domxml"))
	require_once(TL_ABS_PATH . 'third_party'.DS.'domxml-php4-to-php5.php');

// Contributed code - manish
require_once(TL_ABS_PATH . 'third_party'.DS.'phpxmlrpc'.DS.'lib'.DS.'xmlrpc.inc');
require_once(TL_ABS_PATH . 'third_party'.DS.'phpxmlrpc'.DS.'lib'.DS.'xmlrpcs.inc');
require_once(TL_ABS_PATH . 'third_party'.DS.'phpxmlrpc'.DS.'lib'.DS.'xmlrpc_wrappers.inc');


/** $db is a global used throughout the code when accessing the db. */
$db = 0;

/** 
* TestLink connects to the database
*
* @return assoc array
*         aa['status'] = 1 -> OK , 0 -> KO
*         aa['dbms_msg''] = 'ok', or $db->error_msg().
*
* 20050416 - fm
* 
*/
function doDBConnect(&$db)
{
	global $tlCfg;
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
		if((DB_TYPE == 'mysql') && ($tlCfg->charset == 'UTF-8'))
		{
				$r = $db->exec_query("SET CHARACTER SET utf8");
				$r = $db->exec_query("SET collation_connection = 'utf8_general_ci'");
		}
	}

	//if we establish a DB connection, we reopen the session, to attach the db connection
	global $g_tlLogger;
	$g_tlLogger->endTransaction();
	$g_tlLogger->startTransaction();
	
 	return $result;
}

function setSessionTestProject($tproject_info)
{
	if ($tproject_info)
	{
		/** @todo check if the session product is updated when its modified per projectedit.php  */
		$_SESSION['testprojectID'] = $tproject_info['id']; 
		$_SESSION['testprojectName'] = $tproject_info['name'];
		$_SESSION['testprojectColor'] = $tproject_info['color'];
		$_SESSION['testprojectOptReqs'] = isset($tproject_info['option_reqs']) ? $tproject_info['option_reqs'] : null;
		$_SESSION['testprojectOptPriority'] = isset($tproject_info['option_priority']) ? $tproject_info['option_priority'] : null;
		$_SESSION['testprojectOptAutomation'] = isset($tproject_info['option_automation']) ? $tproject_info['option_automation'] : null;
		
		tLog("Product was adjusted to [" . $tproject_info['id'] . "]" . $tproject_info['name'], 'INFO');
		tLog("Product features REQ=" . $_SESSION['testprojectOptReqs'] . ", PRIORITY=" . $_SESSION['testprojectOptPriority']);
	}
	else
	{
		unset($_SESSION['testprojectID']);
		unset($_SESSION['testprojectName']);
		unset($_SESSION['testprojectColor']);
		unset($_SESSION['testprojectOptReqs']);
		unset($_SESSION['testprojectOptPriority']);
		unset($_SESSION['testprojectOptAutomation']);
	}
}

function setSessionTestPlan($tplan_info)
{
	if ($tplan_info)
	{
		$_SESSION['testPlanId'] = $tplan_info['id'];
		$_SESSION['testPlanName'] = $tplan_info['name'];
		
		tLog("Test Plan was adjusted to '" . $tplan_info['name'] . "' ID(" . $tplan_info['id'] . ')', 'INFO');
	}
	else
	{
		unset($_SESSION['testPlanId']);
		unset($_SESSION['testPlanName']);
	}
}

/**
 * Function set paths
 * @todo solve problems after session expires
 */
// MHT 20050712 create extra function for this; 
function setPaths()
{
	tLog('test ' . getenv('SCRIPT_NAME'));
	if (!isset($_SESSION['basehref']))
		$_SESSION['basehref'] = get_home_url();

	$my_locale = isset($_SESSION['locale']) ?  $_SESSION['locale'] : TL_DEFAULT_LOCALE;
	
	global $g_rpath;
	$g_rpath = array ( 'help' => TL_HELP_RPATH . $my_locale,
	                   'instructions' => TL_HELP_RPATH . $my_locale);
	
	global $g_apath;
	foreach ($g_rpath as $key => $value)
	    $g_apath[$key] = TL_ABS_PATH . $value;
	
	return 1;
}

/** Verify if user is log in. Redirect to login page if not. */
function checkSessionValid(&$db)
{
	if (!isset($_SESSION['userID']))
	{
		$ip = getenv ("REMOTE_ADDR");
	    tLog('Invalid session from ' . $ip . '. Redirected to login page.', 'INFO');
		$fName = "login.php";
		$requestURI = null;
		if (strlen($_SERVER['REQUEST_URI']))
			$requestURI = "&req=".urlencode($_SERVER['REQUEST_URI']);
		
		for($i = 0;$i < 5;$i++)
		{
			if (file_exists($fName))
			{
				redirect($_SESSION['basehref'] . $fName."?note=expired".$requestURI,"top.location");
				break;
			}
			$fName = "../".$fName;
		}
		exit();
	}
	else
	{
		$user = new tlUser($_SESSION['userID']);
		$user->readFromDB($db);
		$_SESSION['currentUser'] = $user;
	}
}

/** 
* Function adjust Product and Test Plan to $_SESSION
*
*/
function doInitSelection(&$db)
{
	upd_session_tplan_tproject($db,$_REQUEST);

	return 1;
}

/**
* Function start session
*/
function doSessionStart()
{
	session_set_cookie_params(99999);

	if(!isset($_SESSION))
		session_start();
}

/** 
* General page initialization procedure 
*
* @param boolean $initProduct (optional) Set true if adjustment of Product or  
* 		Test Plan is required; default is FALSE
* @param boolean $bDontCheckSession (optional) Set to true if no session should be
* 		 started
*/
function testlinkInitPage(&$db,$initProduct = FALSE, $bDontCheckSession = false)
{
	doSessionStart();
	doDBConnect($db);
	
	
	setPaths();
	set_dt_formats();
	
	if (!$bDontCheckSession)
		checkSessionValid($db);

	checkUserRights($db);
		
	if ($initProduct)
		doInitSelection($db) or die("Could not set session variables");
		


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

}

function checkUserRights(&$db)
{
	//bypassed as long roles and rights aren't fully defined
	return;
	
	// global $g_userRights;
	$g_userRights = config_get('userRights');
	
	$self = strtolower($_SERVER['SCRIPT_FILENAME']);
	$fName = str_replace(strtolower(str_replace("\\","/",TL_ABS_PATH)),"",$self);

	if (isset($g_userRights[$fName]) && !is_null($g_userRights[$fName]))
	{
		$fRights = $g_userRights[$fName];
		if (has_rights($db,$fRights) != 'yes')
		{
			tLog("Warning: Insufficient rights for ".$self);
			die("Insufficient rights");
		}
		else
			tLog("Sufficient rights for ".$self);
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

/*
  function: 

  args:
  
  returns: 

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


/* 
-------------------------------------------------------------------------------------------
20050708 - fm
Modified to cope with situation where you need to assign a Smarty Template variable instead
of generate output.
Now you can use this function in both situatuons.

if the key 'var' is found in the associative array instead of return a value, 
this value is assigned to $params['var`]

usage: Important: if registered as localize_date()
       {localize_date d='the date to localize'} 
------------------------------------------------------------------------------------------
*/
function localize_date_smarty($params, &$smarty)
{
	return localize_dateOrTimeStamp($params,$smarty,'date_format',$params['d']);
}

/*
  function: 

  args:
  
  returns: 

*/
function localize_timestamp_smarty($params, &$smarty)
{
	return localize_dateOrTimeStamp($params,$smarty,'timestamp_format',$params['ts']);
}

/*
  function: 

  args :
         $params: used only if you call this from an smarty template
                  or a wrapper in an smarty function.
                  
         $smarty: when not used in an smarty template, pass NULL.
         $what: give info about what kind of value is contained in value.
                possible values: timestamp_format
                                 date_format
         $value: must be a date or time stamp in ISO format 
  
  returns: 

*/
function localize_dateOrTimeStamp($params,&$smarty,$what,$value)
{
  // to supress E_STRICT messages
  setlocale(LC_ALL, TL_DEFAULT_LOCALE);

	$format = config_get($what);
	if (!is_numeric($value))
		$value = strtotime($value);
	$retVal = strftime($format, $value);	
	if(isset($params['var']))
		$smarty->assign($params['var'],$retVal);
	return $retVal;
}


/**
 *
 * @param string $str2check
 * @param string  $ereg_forbidden_chars: regular expression
 * 
 * @return  1: check ok, 0:check KO
 */
function check_string($str2check, $ereg_forbidden_chars)
{
	$status_ok = 1;
	
	if( $ereg_forbidden_chars != '' && !is_null($ereg_forbidden_chars))
	{
		if (eregi($ereg_forbidden_chars, $str2check))
		{
			$status_ok=0;	
		} 	
	}	
	return $status_ok;
}

// If we receive TestPlan ID in the _SESSION
//    then do some checks and if everything OK
//    Update this value at Session Level, to set it available in other
//    pieces of the application
//
//
// Calling getUserProdTestPlans() instead of getUserTestPlans()
//         to add ptoduct filtering of TP
//
// rev :
//      20070906 - franciscom - getAccessibleTestPlans() interface changes
function upd_session_tplan_tproject(&$db,$hash_user_sel)
{
	$tproject = new testproject($db);

	// ------------------------------------------------------------------
	$filter_tp_by_product = 1;
	if( isset($hash_user_sel['filter_tp_by_product']) )
	{
	  $filter_tp_by_product = 1;
	}
	else if ( isset($hash_user_sel['filter_tp_by_product_hidden']) )
	{
	  $filter_tp_by_product = 0;
	} 
	// ------------------------------------------------------------------
	$user_sel = array("tplan_id" => 0, "tproject_id" => 0 );
	$user_sel["tproject_id"] = isset($hash_user_sel['testproject']) ? intval($hash_user_sel['testproject']) : 0;
	$user_sel["tplan_id"] = isset($hash_user_sel['testplan']) ? intval($hash_user_sel['testplan']) : 0;

	$tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
	
	// test project is Test Plan container, then we start checking the container
	if( $user_sel["tproject_id"] != 0 )
	{
		$tproject_id = $user_sel["tproject_id"];
	} 
	$tproject_data = $tproject->get_by_id($tproject_id);

	// We need to do checks before updating the SESSION
	if (!$tproject_id || !$tproject_data)
	{
		$all_tprojects = $tproject->get_all();
		if ($all_tprojects)
		{
			$tproject_data = $all_tprojects[0];
		}	
	}
	setSessionTestProject($tproject_data);
	$tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;

	$tplan_id    = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;
	// Now we need to validate the TestPlan
	if($user_sel["tplan_id"] != 0)
		$tplan_id = $user_sel["tplan_id"];

	//check if the specific combination of testprojectid and testplanid is valid
	$tplan_data = getAccessibleTestPlans($db,$tproject_id,
	                                     $_SESSION['userID'],$filter_tp_by_product,$tplan_id);
	if(!is_null($tplan_data))
	{ 
		$tplan_data = $tplan_data[0];
		setSessionTestPlan($tplan_data);
		return;
	}
  
	//get the first accessible TestPlan
	$tplan_data = getAccessibleTestPlans($db,$tproject_id,$_SESSION['userID'],$filter_tp_by_product,null);
	if(!is_null($tplan_data))
		$tplan_data = $tplan_data[0];
		
	setSessionTestPlan($tplan_data);
}


/*
  function: 

  args :
  
  returns: 

*/
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


/*
  function: 

  args :
  
  returns: 

*/
function config_get($config_id)
{
	$my = "g_" . $config_id;
	return $GLOBALS[$my];
}


# --------------------
# Return true if the parameter is an empty string or a string
#  containing only whitespace, false otherwise
# --------------------------------------------------------
# This piece of softare is based on work belonging to:
# --------------------------------------------------------
#
# Mantis - a php based bugtracking system
# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
# This program is distributed under the terms and conditions of the GPL
# See the README and LICENSE files for details
function is_blank( $p_var ) {
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
 *
 *
**/
function downloadContentsToFile($content,$fileName)
{
	global $tlCfg;

	ob_get_clean();
	header('Pragma: public' );
	header('Content-Type: text/plain; charset='. $tlCfg->charset .'; name=' . $fileName );
	header('Content-Transfer-Encoding: BASE64;' );
	header('Content-Disposition: attachment; filename="' . $fileName .'"');
	echo $content;
}


/*
  function: translate_tc_status

  args :
  
  returns: 

*/
function translate_tc_status($status_code)
{
	$map_tc_status = array_flip(config_get('tc_status'));
	
	$verbose = lang_get('test_status_not_run');
	if( $status_code != '')
	{
		$suffix = $map_tc_status[$status_code];
		$verbose = lang_get('test_status_' . $suffix);
	}
	return $verbose;
}


/*
  function: translate_tc_status_smarty

  args :
  
  returns: 

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


/*
  function: 

  args :
  
  returns: 

*/
function my_array_intersect_keys($array1,$array2)
{
	$aresult = array();
	foreach($array1 as $key => $val)
	{
		if(isset($array2[$key]))
		{
			$aresult[$key] = $array2[$key];
		} 	
	}	
	return($aresult);	
}

/*
  function: 
            for performance timing
  args :
  
  returns: 

*/
function microtime_float()
{
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}


/*
  function: init_labels

  args : map key=a code
             value: string_to_translate, that can be found in strings.txt
             
  
  returns: map key=a code
               value: lang_get(string_to_translate)

*/
function init_labels($map_code_label)
{
	foreach($map_code_label as $key => $label)
	{
		$map_code_label[$key] = lang_get($label);
	}
	return $map_code_label;
}


// From Mantis
// Get the named php ini variable but return it as a bool
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
  
  	$tc_info = $tree_manager->get_node_hierachy_info($testcase_id);
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


// MHT: I'm not able find a simple SQL (subquery is not supported 
// in MySQL 4.0.x); probably temporary table should be used instead of the next
function array_diff_byId ($arrAll, $arrPart)
{
	// solve empty arrays
	if (!count($arrAll) || is_null($arrAll))
	{
		return(null);
	}
	if (!count($arrPart) || is_null($arrPart)) 
	{
		return $arrAll;
	}

	$arrTemp = array();
	$arrTemp2 = array();

	// converts to associated arrays
	foreach ($arrAll as $penny) {
		$arrTemp[$penny['id']] = $penny;
	}
	foreach ($arrPart as $penny) {
		$arrTemp2[$penny['id']] = $penny;
	}
	
	// exec diff
	$arrTemp3 = array_diff_assoc($arrTemp, $arrTemp2);
	
	$arrTemp4 = null;
	// convert to numbered array
	foreach ($arrTemp3 as $penny) {
		$arrTemp4[] = $penny;
	}
	return $arrTemp4;
}


/** 
 * trim string and limit to N chars
 * @param string
 * @param int [len]: how many chars return
 *
 * @return string trimmed string
 *
 * @author Francisco Mancardi - 20050905 - refactoring
 *
 */
function trim_and_limit($s, $len=100)
{
  $s=trim($s);
	if (strlen($s) > $len ) {
		$s = substr($s, 0, $len);
	}
	return($s);
}

// 
// nodes_order format:  NODE_ID-?,NODE_ID-?
// 2-0,10-0,3-0
//                      
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
/*
*	Checks $_FILES for errors while uploading
*
*	 @param array $fInfo an array used by uploading files ($_FILES)
* 	
*	returns string containing  an error message (if any)
*/
function getFileUploadErrorMessage($fInfo)
{
	$msg = null;
	if (isset($fInfo['error']))
	{
		switch($fInfo['error'])
		{
			case UPLOAD_ERR_INI_SIZE:
				$msg = lang_get('file_size_larger_than_maximum_size_check_php_ini!');
				break;
			case UPLOAD_ERR_FORM_SIZE:
				$msg = lang_get('file_size_larger_than_maximum_size!');
				break;
			case UPLOAD_ERR_PARTIAL:
			case UPLOAD_ERR_NO_FILE:
				$msg = lang_get('file_upload_error');
				break;
		}
	}
	return $msg;
}

/*
  function: show_instructions 

  args :
  
  returns: 

*/
function show_instructions($key)
{
  	redirect($_SESSION['basehref'] . "lib/general/staticPage.php?key={$key}");
}
?>
