<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * @filesource $RCSfile: common.php,v $
 * @version $Revision: 1.25 $ $Author: schlundus $
 * @modified $Date: 2005/11/09 19:54:10 $
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
 * email, userID, productID, productName, project (use rather testPlanID),
 * testPlanID, testPlanName
 *
 *
 * @author: francisco mancardi - 20051005 - set_dt_formats()
 * @author: francisco mancardi - 20051002 - more changes to support filter_tp_by_product
 * 20051002 - am - code reformatted, small corrections
 * @author: francisco mancardi - 20050929 - changes to support filter_tp_by_product
 * @author: francisco mancardi - 20050917 - BUG ID 0000120: Impossible to edit product
 *
 * @author: francisco mancardi - 
 * created updateSessionTp_Prod() and changed doInitSelection() to solve: 
 * BUGID  0000092: Two products each with one active test plan incorrectly prints the wrong plan
 * 
 * @author: francisco mancardi - 20050907 - added hash2array()
 * @author: francisco mancardi - 20050904 - added check_hash_keys()
 *
 * @author: francisco mancardi - 20050904
 * TL 1.5.1 compatibility, get also Test Plans without product id.
 *
 * @author: francisco mancardi - 20050813 - added localize_date_smarty()
 * @author: francisco mancardi - 20050813 - added TP filtered by Product *
 * @author: francisco mancardi - 20050810 - added function to_boolean($alt_boolean)
 * 
 * @author: Asiel Brumfield - 20051012 - optimize sql queries
**/ 
require_once("getRights.php");
require_once("product.core.inc.php");

// 20050917 - fm - BUG ID 0000120: Impossible to edit product
require_once("plan.core.inc.php");
require_once("logging.inc.php");
require_once("lang_api.php");

/** $db is a global used throughout the code when accessing the db. */
$db = 0;


// ----------------------------------------------------------------
/** 
* TestLink connects to the database
*
* @return assoc array
*         aa['status'] = 1 -> OK , 0 -> KO
*         aa['dbms_msg''] = 'ok', or mysql_error().
*
* 20050416 - fm
* 
*/
function doDBConnect()
{
	global $db;
	
	$result = array('status' => 1, 'dbms_msg' => 'ok');
	$db = mysql_connect(DB_HOST, DB_USER, DB_PASS);
	
	if (!$db or !mysql_select_db(DB_NAME,$db) )
	{
		echo $result['dbms_msg'];
		$result['status'] = 0;
		$result['dbms_msg'] = mysql_error();
		tLog('Connect to database fails!!! ' . $result['dbms_msg'], 'ERROR');
  	}
  	else
	{
		if (DB_SUPPORTS_UTF8)
		{
			$r = @do_mysql_query("SET CHARACTER SET utf8;");
			$r = @do_mysql_query("SET collation_connection = 'utf8_general_ci';");
		}
	}

  	return $result;
}


// 20050622 mht added options and productID
// 20050813 - fm - removed $_SESSION['product'];
function setSessionProduct($productInfo)
{
	if ($productInfo)
	{
		/** @todo check if the session product is updated when its modified per adminproductedit.php  */
		// 20050813 - fm $_SESSION['product'] = $productInfo['id'];
		$_SESSION['productID'] = $productInfo['id']; 
		$_SESSION['productName'] = $productInfo['name'];
		$_SESSION['productColor'] = $productInfo['color'];
		$_SESSION['productOptReqs'] = isset($productInfo['option_reqs']) ? $productInfo['option_reqs'] : null;
		$_SESSION['productOptPriority'] = isset($productInfo['option_priority']) ? $productInfo['option_priority'] : null;
		
		tLog("Product was adjusted to [" . $productInfo['id'] . "]" . $productInfo['name'], 'INFO');
		tLog("Product features REQ=" . $_SESSION['productOptReqs'] . ", PRIORITY=" . $_SESSION['productOptPriority']);
	}
	else
	{
		unset($_SESSION['productID']);
		unset($_SESSION['productName']);
		unset($_SESSION['productColor']);
		unset($_SESSION['productOptReqs']);
		unset($_SESSION['productOptPriority']);
	}
}


// 20050926 - fm
function setSessionTestPlan($tpInfo)
{
	if ($tpInfo)
	{
		$_SESSION['testPlanId'] = $tpInfo['id'];
		$_SESSION['testPlanName'] = $tpInfo['name'];
		
		tLog("Test Plan was adjusted to '" . $tpInfo['name'] . "' ID(" . $tpInfo['id'] . ')', 'INFO');
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

// If we receive TestPlan ID in the _GET 
//    the user has changed the selection
//    Set this value at Session Level, to set it available in other
//    pieces of the application
//
function checkTestPlanSelection()
{
	$tpID = isset($_GET['project']) ? intval($_GET['project']) : 0;
	if ($tpID)
	{
		// Test Plan selection changed -> update SESSION INFO
		setSessionTestPlan(getUserTestPlan($_SESSION['userID'],$tpID));
	}	
}

// If we receive Product ID in the _GET 
//    the user has changed the selection
//    Set this value at Session Level, to set it available in other
//    pieces of the application
//
//
function checkProductSelection()
{
	$prodID = isset($_GET['product']) ? intval($_GET['product']) : 0;
	if ($prodID)
	{
		setSessionProduct(getProduct($prodID));
	}
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
function checkSessionTestPlan()
{
	// 20050813 - fm - added TP filtered by Product
	$prodID = isset($_SESSION['productID']) ? $_SESSION['productID'] : null;
	$sTestPlanID = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : null;
	
	if (!$sTestPlanID || ($sTestPlanID && !getUserTestPlan($_SESSION['userID'],$sTestPlanID,true)))
	{
	 	// 20050813 - fm
		$tpInfo = getUserProdTestPlans($_SESSION['userID'],$prodID,true);
		if ($tpInfo)
		{
			setSessionTestPlan($tpInfo[0]);
		}	
	}
}



// If we receive Product ID in the _SESSION
//    then do some checks and if everything OK
//    Update this value at Session Level, to set it available in other
//    pieces of the application
//
//

function checkSessionProduct()
{
	$prodID = isset($_SESSION['productID']) ? $_SESSION['productID'] : null;
	// if the session product exists, check to see if the user has rights to it
	// 20050813 - fm - implified if-clause
	if (!$prodID || !getProduct($prodID))
	{
		$products = getProducts();
		if ($products)
		{
			setSessionProduct($products[0]);
		}	
	}
}

/** Verify if user is log in. Redirect to login page if not. */
function checkSessionValid()
{
	if (!isset($_SESSION['userID']))
	{
		$ip = getenv ("REMOTE_ADDR");
	    tLog('Invalid session from ' . $ip . '. Redirected to login page.', 'INFO');
		// 20051012 - am - fix for 134
		$fName = "login.php";
		for($i = 0;$i < 5;$i++)
		{
			if (file_exists($fName))
			{
				redirect($_SESSION['basehref'] . $fName."?note=expired","top.location");
				break;
			}
			$fName = "../".$fName;
		}
		exit();
	}
}


function getUserTestPlan($userID,$tpID,$bActive = null)
{
	$tpInfo = getUserTestPlans($userID,$tpID,$bActive);
	return $tpInfo ? $tpInfo[0] : null;
}

// 20050810 - fm - Changes needed due to ACTIVE FIELD type change to BOOLEAN
function getUserTestPlans($userID,$tpID = null,$p_bActive = null)
{
	$sql = "SELECT * FROM project,projrights " .
	       "WHERE projrights.projid = project.id AND userID={$userID}";
	
	if (!is_null($tpID))
	{
		 $sql .= " AND project.id = {$tpID}";
	}
	if (!is_null($p_bActive))
	{
		// 20050810 - fm
		$bActive = to_boolean($p_bActive);	
		$sql .= " AND project.active = " . $bActive;
	}
	return selectData($sql);
}


// 20051002 - fm - added $g_ui_show_check_filter_tp_by_product
// 20050929 - fm - added $filter_by_product
// 20050904 - fm - TL 1.5.1 compatibility, get also Test Plans without product id.
// 20050813 - fm - new
// 
function getUserProdTestPlans($userID,$prodID,$filter_by_product,$p_bActive = null)
{
  global $g_show_tp_without_prodid;
  global $g_ui_show_check_filter_tp_by_product;
  
  $apply_tp_filter = 1;
  if( $g_ui_show_check_filter_tp_by_product )
  {
    $apply_tp_filter = $filter_by_product;
  }
  	
	$sql = " SELECT project.id, project.prodid, project.name, project.active, " .
			"projrights.projid, projrights.userID FROM project,projrights " .
	       " WHERE projrights.projid = project.id " .
	       " AND userID={$userID}";
	
	if (!is_null($prodID))
	{
		// 20050904 - fm - 
		// TL 1.5.1 compatibility, get also Test Plans without product id.
		// 20051012 - azl
		// Removed the OR in the sql because it slows down the query signifigantly
		// doing the logic here to determine if it is compat with 1.5 or not
		if($apply_tp_filter and $g_show_tp_without_prodid)		    	
		{
			$sql .= " AND project.prodid=0";
		}
		elseif($apply_tp_filter)
		{
			$sql .= " AND project.prodid = {$prodID}";
		}
    }		 
	
	if (!is_null($p_bActive))
	{
		// 20050810 - fm
		$bActive = to_boolean($p_bActive);
		$sql .= " AND project.active = " . $bActive;
 	}
 	return selectData($sql);
}

/** 
* Function adjust Product and Test Plan to $_SESSION
*
*/
function doInitSelection()
{
	
	/*
	checkTestPlanSelection();
	checkProductSelection();
	
	
	checkSessionProduct();
	checkSessionTestPlan();	
  */
  
  // 20050929 - fm - _GET -> _REQUEST to get other info
  // 20050910 - fm - 
  // BUGID  0000092: Two products each with one active test plan incorrectly prints the wrong plan
  updateSessionTp_Prod($_REQUEST);


	return 1;
}

/**
* Function start session
*/
function doSessionStart()
{
	session_set_cookie_params(99999);
	session_start();

	return 1;
}

/** 
* General page initialization procedure 
*
* @param boolean $initProduct (optional) Set true if adjustment of Product or  
* 		Test Plan is required; default is FALSE
* @param boolean $bDontCheckSession (optional) Set to true if no session should be
* 		 started
*/
function testlinkInitPage($initProduct = FALSE, $bDontCheckSession = false)
{
	doDBConnect() or die("Could not connect to DB");
	doSessionStart() or die("Could not start session");
	setPaths();
	set_dt_formats();
	
	if (!$bDontCheckSession)
	{
		checkSessionValid();
	}	
	checkUserRights();
		
	if ($initProduct)
	{
		doInitSelection() or die("Could not set session variables");
	}
}

function checkUserRights()
{
	global $g_userRights;
	$self = strtolower($_SERVER['SCRIPT_FILENAME']);
	$fName = str_replace(strtolower(str_replace("\\","/",TL_ABS_PATH)),"",$self);

	if (isset($g_userRights[$fName]) && !is_null($g_userRights[$fName]))
	{
		$fRights = $g_userRights[$fName];
		if (has_rights($fRights) != 'yes')
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

/**
 * Turn the $_POST map into a number valued array
 *
 * @return  array    number valued array of posted input 
 */
function extractInput($bStripInput = false)
{
	$newArray = null;
	foreach ($_POST as $key)
		$newArray[] = $bStripInput ? strings_stripSlashes($key) : $key;

	return $newArray;
}

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

/** 
 * generalized execution SELECT query
 * @param string SQL request
 * @return associated array  
 */
// MHT 200506 created
function selectData($sql)
{
	$output = null;
	$result = do_mysql_query($sql);
	
	if ($result)
	{
		while($row = mysql_fetch_array($result))
			$output[] = $row;
	}
	else
		tLog('FAILED SQL: ' . $sql . "\n" . mysql_error(), 'ERROR');
	
	return $output;
}

/** 
 * generalized execution SELECT query for option data
 * @param string SQL request (SELECT id,title FROM ...)
 * @return associated array  'id' => 'title'
 */
// MHT 200506 created
function selectOptionData($sql)
{
	$output = null;
	$result = do_mysql_query($sql);
	
	if ($result)
	{
		while($row = mysql_fetch_array($result))
			$output[$row[0]] = $row[1];
	}
	else
	{
		tLog('FAILED SQL: ' . $sql . "\n" . mysql_error(), 'ERROR');
	}
	
	return $output;
}

/** 
 * pick one value from SQL request
 * 
 * @param string $sql SQL request
 * @return string required value or null
 * @author havlatm
 */
function do_mysql_selectOne($sql)
{
	$output = null;
	
	$result = do_mysql_query($sql);

	// return null for error or no data
	if ($result && (mysql_num_rows($result) > 0)) {
		$output = mysql_result($result, 0);
	}
	
	return $output;
}


// --------------------------------------------------------------
// returns an array of messages, one element for every
// key of $a_fields_msg, that has empty value in $a_fields_values.
// The messages is taken from $a_fields_msg
//
// If the key from $a_fields_msg doesn't exists in $a_fields_values
// is considered has existent and empty.
//
//
// 20050417 - fm
// 
function control_empty_fields( $a_fields_values, $a_fields_msg )
{
	$a_msg = array();
	
	foreach ($a_fields_msg as $key_f=>$value_m)
	{
		if (strlen($a_fields_values[$key_f]) == 0)
			$a_msg[] = $value_m ;    
	}
	return ($a_msg);
}


// 20050809 - fm - to cope with the active field type change
// 20050816 - scs - simplified
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
	global $g_date_format;

	$the_d = strftime($g_date_format, strtotime($params['d']));	
	if(	isset($params['var']) )
	{
		$smarty->assign($params['var'], $the_ret);
	}
	else
	{
		return $the_d;
	}
}


/*
check the existence of every element of $akeys2check, in the hash.
For every key not found a call to tlog() is done. 

@param associative array: $hash
@param array: $akeys2check
@param string: [$msg] append to key name to use as tlog message
                      

@returns 1: all keys can be found
         0: at least one key not found  

@author Francisco Mancardi - 20050905 - creation
 20050905 - scs - corrected and refactored
*/
function check_hash_keys($hash, $akeys2check, $msg='')
{
	$status = 1;
	if (sizeof($akeys2check))
	{
		$tlog_msg = $msg . " is not defined";
		foreach($akeys2check as $key)
		{
			if (!isset($hash[$key])) 
			{
				$status = 0;
				tlog( $key . $tlog_msg);
			}
		}
	}
	
	return ($status);
}



/**
 * Turn a hash into a number valued array
 *
 * 
 * @return  array    number valued array of posted input 
 */
function hash2array($hash, $bStripInput = false)
{
	$newArray = null;
	foreach ($hash as $key)
	{
		$newArray[] = $bStripInput ? strings_stripSlashes($key) : $key;
	}
	return $newArray;
}


/**
 * Turn a hash into a number valued array
 *
 * @param string $str2check
 * @param string  $ereg_forbidden_chars: regular expression
 * 
 * @return  1: check ok, 0:check KO
 *
 * @author Francisco Mancardi - 20050907 
 *
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
function updateSessionTp_Prod($hash_user_sel)
{
	
	$user_sel = array("tpID" => 0, "prodID" => 0 );

	$user_sel["prodID"] = isset($hash_user_sel['product']) ? intval($hash_user_sel['product']) : 0;
	$user_sel["tpID"] = isset($hash_user_sel['project']) ? intval($hash_user_sel['project']) : 0;

  
  // 20050929 - fm
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

  $prodID = isset($_SESSION['productID']) ? $_SESSION['productID'] : 0;
	$tpID   = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;
	
	// Now what to do ???
	// Product is TestPlan container, then we start checking the container
	if( $user_sel["prodID"] != 0 )
	{
    $prodID = $user_sel["prodID"];
	} 
  $prodData=getProduct($prodID);

  // We need to do checks before updating the SESSION
	if (!$prodID || !$prodData)
	{
		$products = getProducts();
		if ($products)
		{
			$prodData = $products[0];
		}	
	}
	setSessionProduct($prodData);


  // Now we need to validate the TestPlan
  if( $user_sel["tpID"] != 0 )
	{
    $tpID = $user_sel["tpID"];
	} 
  
  // Wee need to check:
  // 1. $tpID belongs to prodID
  // 2. User has rights on $tpID
  // If any check fails we try to show the first TP in the Product, allowed to the user
  $redo = 1;
  $tpData = null;
  
  if (check_tp_father($prodID,$tpID))
  {
    // Good! first check OK
    $tpData = getUserTestPlan($_SESSION['userID'],$tpID);
    if( !is_null($tpData) )
    { 
      $redo = 0;
      setSessionTestPlan($tpData);
    }
    // 20050929 - 
  }
  
  if ( $redo )
  {
  	// 20050926 - fm
  	$tp_prodid = get_tp_father($tpID);
  	
    // Houston we have a problem
    $ACTIVE_TP=true;
    $tpInfo = getUserProdTestPlans($_SESSION['userID'],$prodID,$filter_tp_by_product,$ACTIVE_TP);
		
		if ($tpInfo)
		{
			
			// Attention: 
			// this TP has a prodid (father) != 0, but it's prodid is different that selected prodid
			// then what to do ?
			if ($tp_prodid && $filter_tp_by_product)
		  {
				$tpData = $tpInfo[0];
			}
			else
			{
        // We can ignore the prodid (father) of the selected TP
				// TL 1.5.1 compatibility 
		    foreach (	$tpInfo as $key => $elem )
		    {
		      if ($elem['id'] == $tpID)
		      {
		       $tpData = $tpInfo[$key];
		       break;
		      }
		    }	
			}	
		}
  }
  setSessionTestPlan($tpData);

}

// 20051005 - fm - SET Date and Time FORMATS 
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


// 20051105 - francisco.mancardi@gruppotesi.com
// idea from mantisbt
function config_get($config_id)
{
	$my = "g_" . $config_id;

	return $GLOBALS[$my];
}


# --------------------
# Return true if the parameter is an empty string or a string
#  containing only whitespace, false otherwise
# --------------------------------------------------------
# This piece of sowftare is based on work belonging to:
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

?>