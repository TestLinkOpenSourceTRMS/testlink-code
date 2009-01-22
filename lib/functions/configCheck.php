<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: configCheck.php,v $
 * @version $Revision: 1.37 $
 * @modified $Date: 2009/01/22 17:09:57 $ by $Author: havlat $
 *
 * @author Martin Havlat
 * 
 * Scope: Check configuration and system 
 * Using: Installer, sysinfo.php and Login
 *
 * Revisions:
 * 	
 *  20090109 - havlatm - import checking functions from Installer 
 * 	20081122 - franciscom - checkForExtensions() - added check of needed extensions to use pChart
 *  20081015 - franciscom - getSecurityNotes() - refactoring
 *
 **/
// ---------------------------------------------------------------------------------------------------
/** @TODO martin: remove this include (obsolete) */
require_once('plan.core.inc.php');

/**
 * get home url.
 * @author adapted from Mantis Bugtracking system
 * @return string URL 
 */
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

/** check language acceptance by web client */
function checkServerLanguageSettings($defaultLanguage)
{
	global $g_locales;
	$language = $defaultLanguage;

	// check for !== false because getenv() returns false on error
	$serverLanguage = getenv($_SERVER['HTTP_ACCEPT_LANGUAGE']);
	if(false !== $serverLanguage)
	{
		if (array_key_exists($serverLanguage,$g_locales))
			$language = $serverLanguage;
	}

	return ($language);
}



/** check if we need to run the install program */
function checkConfiguration()
{
	clearstatcache();
	$file_to_check = "config_db.inc.php";

	if(!is_file($file_to_check))
	{
		echo '<html><body onload="' . "location.href='./install/index.php'" . '"></body></html>';
		exit();	
	}
}


/** 
 * check if TL is installed
 * @return boolean true = installed 
 **/
function checkInstallStatus()
{
	if (defined('DB_TYPE'))
		return true;
	else
		return false;
}



/**
 * checks if needed functions and extensions are defined 
 *
 * @param array [ref] msgs will be appended
 * @return bool returns true if all extension or functions ar present or defined
 *
 * @author Andreas Morsing 
 *
 * rev: 20081122 - franciscom - added gd2 check
 **/
function checkForExtensions(&$msg)
{
	if (!function_exists('domxml_open_file'))
	{
		$msg[] = lang_get("error_domxml_missing");
	}
	
	// without this pChart do not work
	if( !extension_loaded('gd') )
	{
		$msg[] = lang_get("error_gd_missing");
	}
	return true;
}

/**
 * checks if the install dir is present
 *
 * @return bool returns true if the install dir is present, false else
 *
 * @version 1.0
 * @author Andreas Morsing 
 **/
function checkForInstallDir()
{
	$installer_dir = TL_ABS_PATH. DIRECTORY_SEPARATOR . "install"  . DIRECTORY_SEPARATOR;
	clearstatcache();
	$bPresent = false;
	if(is_dir($installer_dir))
		$bPresent = true;
	
	return $bPresent;	
}

/**
 * checks if the default password for the admin accout is still set
 *
 * @return bool returns true if the default password for the admin account is set, 
 * 				false else
 *
 * @version 1.0
 * @author Andreas Morsing 
 **/
function checkForAdminDefaultPwd(&$db)
{
	$bDefaultPwd = false;
	
	$user = new tlUser();
	$user->login = "admin";
	if ($user->readFromDB($db,tlUser::USER_O_SEARCH_BYLOGIN) >= tl::OK && 
		 $user->comparePassword("admin") >= tl::OK)
		$bDefaultPwd = true;
		
	return $bDefaultPwd;
}

/*
  function: checkForLDAPExtension
  args :
  returns: 
*/
function checkForLDAPExtension()
{
	return extension_loaded("ldap");
}

/**
 * builds the security notes while checking some security issues
 * these notes should be displayed!
 *
 * @return array returns the security issues, or null if none found!
 *
 * @version 1.0
 * @author Andreas Morsing 
 *
 * rev :
 *      20081015 - franciscom - LDAP checks refactored
 *      20080925 - franciscom - added option to not show results
 *      20070626 - franciscom - added LDAP checks  
 **/
function getSecurityNotes(&$db)
{
	$repository['type'] = config_get('repositoryType');
	$repository['path'] = config_get('repositoryPath');
  
	$securityNotes = null;
	if (checkForInstallDir())
	{
		$securityNotes[] = lang_get("sec_note_remove_install_dir");
  }
  
  $authCfg = config_get('authentication');
  if( 'LDAP' == $authCfg['method']  )
  {
    if( !checkForLDAPExtension() )
    {
	    $securityNotes[] = lang_get("ldap_extension_not_loaded");
	  }  
  } 
  else
  {
    if( checkForAdminDefaultPwd($db) )
    {
		    $securityNotes[] = lang_get("sec_note_admin_default_pwd");
		}
  }

  
	if (!checkForBTSConnection())
	{
		$securityNotes[] = lang_get("bts_connection_problems");
	}
		
	if($repository['type'] == TL_REPOSITORY_TYPE_FS)
	{
		$ret = checkForRepositoryDir($repository['path']);
		if(!$ret['status_ok'])
		{
			$securityNotes[] = $ret['msg'];
		}	
	}

	// 20070121 - needed when schemas change has been done
	// This call can be removed when release is stable
	$msg = checkSchemaVersion($db);
	if(strlen($msg))
	{
		$securityNotes[] = $msg;
	}
	
	// 20070911 - fixing bug 1021 
	$msg = checkForTestPlansWithoutTestProjects($db);
	if(strlen($msg))
	{
		$securityNotes[] = $msg;
	}
	
	// 20080308 - franciscom
	$msg = checkEmailConfig();
	if(!is_null($msg))
	{
	  foreach($msg as $detail)
	  {
		   $securityNotes[] = $detail;
		}   
	}
	checkForExtensions($securityNotes);
  
	// write problems to a file
	if(!is_null($securityNotes))
	{
      	$warnings='';
		$filename = config_get('log_path') . 'config_check.txt';
      
      	if (@$handle = fopen($filename, 'w')) 
      	{
      		$warnings=implode("\n",$securityNotes);
      		@fwrite($handle, $warnings);

			$securityNotes=null;
			// based on configuration show warning on login page
			if (config_get('show_config_check_warning'))
				$securityNotes[] = sprintf(lang_get('config_check_warnings'),$filename);
      	}
      	else
      	{
			// show problems on login page
	    	$securityNotes[] = lang_get('unable_to_create_file');
      	}
		@fclose($handle);	
      
	}
	
	return $securityNotes;
}


/**
 * checks if the connection to the Bug Tracking System database is working
 *
 * @return bool returns true if ok
 * 				false else
 *
 * @version 1.0
 * @author franciscom 
 **/
function checkForBTSConnection()
{
  
	global $g_bugInterface;
	$status_ok = true;
	if($g_bugInterface && !$g_bugInterface->connect())
		$status_ok = false;
	return $status_ok; 
}


/*
  function: checkForRepositoryDir
  args :
  returns: 
*/
function checkForRepositoryDir($the_dir)
{
	clearstatcache();

  $ret['msg']=lang_get('attachments_dir') . " " . $the_dir . " ";
              
  $ret['status_ok']=false;
  	
  if(is_dir($the_dir)) 
  {
  	$ret['msg'] .= lang_get('exists');
    $ret['status_ok']=true;

    // There is a note on PHP manual that points that on windows
    // is_writable() has problems => need a workaround
    
    /*
    */
    //echo substr(sprintf('%o', fileperms($the_dir)), -4);
    
    $os_id = strtoupper(substr(PHP_OS, 0, 3));
    if( strcmp('WIN',$os_id) == 0 )
    {
      $test_dir= $the_dir . '/requirements/';
      if(!is_dir($test_dir))
      {
        // try to make the dir
        $stat = @mkdir($test_dir);
        if( $stat )
        {
      	    $ret['msg'] .= lang_get('directory_is_writable');
        }
        else
        {
            $ret['msg'] .= lang_get('but_directory_is_not_writable');
            $ret['status_ok']=false;
        }
      }
    }
    else
    {
        if(is_writable($the_dir)) 
        {
      	    $ret['msg'] .= lang_get('directory_is_writable');
      	}
        else
        {
      	    $ret['msg'] .= lang_get('but_directory_is_not_writable');
            $ret['status_ok']=false;
        }
    }
    
  } 
  else
  {
    $ret['msg'] .= lang_get('does_not_exist');
  }
  return($ret);
}


/*
  function: checkSchemaVersion
  args :
  returns: 
*/
function checkSchemaVersion(&$db)
{
	$last_version = 'DB 1.2';  // 20080102 - franciscom
	// $last_version = 'DB 1.1';
	// 1.7.0 RC 3';
	
	$sql = "SELECT * FROM db_version ORDER BY upgrade_ts DESC";
	$res = $db->exec_query($sql,1);  
	if (!$res)
		return $msg = "Failed to get Schema version from DB";
		
	$myrow = $db->fetch_array($res);
	$msg = "";
	switch (trim($myrow['version']))
	{
		case '1.7.0 Alpha':
		case '1.7.0 Beta 1':
		case '1.7.0 Beta 2':
		case '1.7.0 Beta 3':
		case '1.7.0 Beta 4':
		case '1.7.0 Beta 5':
		case '1.7.0 RC 2':
		case '1.7.0 RC 3':
		case 'DB 1.1':
			$msg = "You need to upgrade your Testlink Database to {$last_version} - <br>" .
				'<a href="SCHEMA_CHANGES" style="color: white"> click here to see the Schema changes </a><br>' .
				'<a href="./install/index.php" style="color: white">click here access install and upgrade page </a><br>';
			break;

		case $last_version:
			break;
		
		default:
			$msg = "Unknown Schema version " .  trim($myrow['version']) . 
			       ", please upgrade your Testlink Database to " . $last_version;
			break;
	}
	
	return $msg;
}

/**
 * checks if the install dir is present
 *
 * @return msg returns if there are any test plans without a test project 
 *
 * @version 1.0
 * @author Asiel Brumfield 
 **/
function checkForTestPlansWithoutTestProjects(&$db)
{
	$msg = "";
	if(count(getTestPlansWithoutProject($db)))
	{	
		$msg = "You have Test Plans that are not associated with Test Projects!";
		if(isset($_SESSION['basehref']))
		{		
			$url = $_SESSION['basehref'] . "/lib/project/fix_tplans.php"; 			
			$msg .= " <a style=\"color:red\" href=\"{$url}\">Fix This</a>";		
		}
	}	
	return $msg;
}


/*
  function: checkEmailConfig 
  args :
  returns: 
*/
function checkEmailConfig()
{
	$common[] = lang_get('check_email_config');
	$msg = null;
	$idx = 1;
	$key2get = array('tl_admin_email','from_email','return_path_email','smtp_host');
	
	foreach($key2get as $cfg_key)
	{  
	   $cfg_param = config_get($cfg_key);
	   if(strlen(trim($cfg_param)) == 0 || strpos($cfg_param,'not_configured') > 0 )
	   {
			$msg[$idx++] = $cfg_key;
	   }  
	}
	return is_null($msg) ? null : $common+$msg; 
}

/** 
 * checking register global = OFF (doesn't cause error')
 * @param integer &$errCounter pointer to error counter
 * @return string html table row
 */
function check_php_settings(&$errCounter)
{
    $max_execution_time_recommended = 120;
    $max_execution_time = ini_get('max_execution_time');
    $memory_limit_recommended = 64;
    $memory_limit = intval(str_ireplace('M','',ini_get('memory_limit')));

    $final_msg = '<tr><td>Checking max. execution time (Parameter max_execution_time)</td>';
    if($max_execution_time < $max_execution_time_recommended)
        $final_msg .=  "<td><span class='tab-warning'>{$max_execution_time} seconds - We suggest {$max_execution_time_recommended} " .
                  "seconds in order to manage hundred of test cases (edit php.ini)</span></td>";
    else
        $final_msg .= '<td><span class="tab-success">OK ('.$max_execution_time.' seconds)</span></td></tr>';

    $final_msg .=  "<tr><td>Check maximal allowed memory (Parameter memory_limit)</td>";
    if($memory_limit < $memory_limit_recommended)
       $final_msg .= "<td><span class='tab-warning'>$memory_limit MegaBytes - We suggest {$memory_limit_recommended} MB" .
                     " in order to manage hundred of test cases</span></td></tr>";
    else
        $final_msg .= '<td><span class="tab-success">OK ('.$memory_limit.' MegaBytes)</span></td></tr>';
    
	$final_msg .= "<tr><td>Checking if Register Globals is disabled</td>";
	if(ini_get('register_globals')) 
		$final_msg .=  "<td><span class='tab-warning'>Failed! is enabled - Please change the setting in your php.ini file</span></td></tr>";
	else 
		$final_msg .= "<td><span class='tab-success'>OK</span></td></tr>\n";

	return ($final_msg);
}


/** 
 * check php extensions
 * @param integer &$errCounter pointer to error counter
 * @return string html table rows
 * 
 * @todo martin: Do we require "Checking DOM XML support"?
 */
function check_php_extensions(&$errCounter)
{
	$out = '<tr><td>Checking graphic library (GD)</td>';

	if(extension_loaded('gd'))
		$out .= "<td><span class='tab-success'>OK</span></td></tr>\n";
	else 
		$out .=  '<td><span class="tab-warning">Failed! Graph rendering requires it. ' .
			'This feature will be disabled. It\'s recommended to install it.</span></td></tr>';

	$out .= '<tr><td>Checking LDAP library</td>';
	if(extension_loaded('ldap'))
		$out .= "<td><span class='tab-success'>OK</span></td></tr>\n";
	else 
		$out .=  "<td><span class='tab-warning'>Failed! LDAP authentication cannot be used" .
				" (default internal authentication will works)</span></td></tr>";

/*	$out .= '<tr><td>Checking DOM XML support</td>';
	if (function_exists('domxml_open_file'))
		$out .= '<td><span class="tab-success">OK</span></td></tr>\n;';
	else 
	{
		$out .=  '<td><span class="tab-error">ERROR - XML Import/Export cannot work. ' .
				'Please install related library to your web server (You can do it later).' .
				'</span></td></tr>';
        $errCounter++;
	}
*/
	return ($out);
}  



/**
 * Check if web server support session data
 * @param integer &$errCounter pointer to error counter
 * @return string html row with result 
 */
function check_session(&$errCounter)
{
	$out = "<tr><td>Checking if sessions are properly configured</td>";

	if( !isset($_SESSION) )
		session_start();

	if( $_SESSION['session_test'] != 1 ) 
	{
    	$color = 'success';
    	$msg = 'OK';
	} else {
    	$color = 'error';
    	$msg = 'Failed!';
        $errCounter++;
    }

	$out .= "<td><span class='tab-$color'>$msg</span></td></tr>\n";
	return ($out);
}  //function end


/**
 * check PHP defined timeout
 * @param integer &$errCounter pointer to error counter
 * @return string html row with result 
 */
function check_timeout(&$errCounter)
{
    $out = '<tr><td>Maximum Session Idle Time before Timeout</td>';

	$timeout = ini_get("session.gc_maxlifetime");
	$gc_maxlifetime_min = floor($timeout/60);
	$gc_maxlifetime_sec = $timeout % 60;
	
    if ($gc_maxlifetime_min > 30) {
    	$color = 'success';
    	$res = 'OK';
	} else if ($gc_maxlifetime_min > 10){
    	$color = 'warning';
    	$res = 'Short. Consider to extend.';
	} else {
    	$color = 'error';
    	$res = 'Too short. It must be extended!';
        $errCounter++;
    }
    $out .= "<td><span class='tab-$color'>".$gc_maxlifetime_min .
    		" minutes and $gc_maxlifetime_sec seconds - ($res)</span></td></tr>\n";
    
	return $out;
}


/**
 * Display OS
 * @return string html table row
 */
function check_os()
{
	$final_msg = '<tr><td>Server Operating System (no constrains)</td>';
	$final_msg .= '<td>'.PHP_OS.'</td></tr>';
	
/*
$os_id = strtoupper(substr(PHP_OS, 0, 3));
if( strcmp('WIN',$os_id) == 0 )
{
  $final_msg .= "<p><center><span class='notok'>" . 
  	            "Warning!: You are using a M$ Operating System, " .
  	            "be careful with authentication problems <br>" .
  	            "          between PHP 4 and the new MySQL 4.1.x passwords<br>" . 
  	            'Read this <A href="' . $info_location . 'MySQL-RefManual-A.2.3.pdf">' .
  	            "MySQL - A.2.3. Client does not support authentication protocol</A>" .
  	            "</span></center><p>";
}*/

	return ($final_msg);
}  


/**
 * check minimal required PHP version
 * @param integer &$errCounter pointer to error counter
 * @return string html row with result 

  rev :
  		- havlatm: converted to table format, error passed via argument, 
  			disabled unused "not tested version"
        - added argument to point to info
        - added warning regarding possible problems between MySQL and PHP 
          on windows systems due to MySQL password algorithm.
 */
function check_php_version(&$errCounter)
{
	// 5.2 is required because json is used in ext-js component
	$min_version = '5.2.0'; 
	$my_version = phpversion();

	// version_compare:
	// -1 if left is less, 0 if equal, +1 if left is higher
	$php_ver_comp = version_compare($my_version, $min_version);

/* not used
	$ver_not_tested="";
	$has_ver_not_tested=strlen(trim($ver_not_tested)) > 0;
	$check_not_tested = -1;
	if($has_ver_not_tested)
	{
		$check_not_tested = version_compare($my_version, $ver_not_tested);
	}
*/
	$final_msg = '<tr><td>PHP version</td>';

	if($php_ver_comp < 0) 
	{
		$final_msg .= "<td><span class='tab-error'>Failed!</span> - You are running on PHP " . 
	        $my_version . ", and TestLink requires PHP " . $min_version . ' or greater. ' .
	        'This is fatal problem. You must upgrade it.</td>';
		$errCounter += 1;
	} 
/*else if($check_not_tested >= 0) 
{
  // Just a Warning
  $final_msg .= "<br><span class='ok'>WARNING! You are running on PHP " . $my_version . 
                ", and TestLink has not been tested on versions >= " . $ver_not_tested . "</span>";
}*/
	else 
	{
		$final_msg .= "<td><span class='tab-success'>OK ( {$min_version} [minimun version] ";
		$final_msg .= ($php_ver_comp == 0 ? " = " : " <= ");
		$final_msg .=	$my_version . " [your version] " ;
	              
/*	if( $has_ver_not_tested )
	{
	  $final_msg .= " < {$ver_not_tested} [not tested yet]";
	}*/              
		$final_msg .= " ) </span></td></tr>";
	}

	return ($final_msg);
}  

/**
 * Check read/write permissions for directories
 * based on check_with_feedback($dirs_to_check);
 * @param integer &$errCounter pointer to error counter
 * @return string html row with result 
 */
function check_dir_permissions(&$errCounter)
{
	$dirs_to_check = array('gui'.DIRECTORY_SEPARATOR.'templates_c', 'logs', 'upload_area');
	$final_msg = '';

	$msg_ko = "<td><span class='tab-error'>Failed!</span></td></tr>";
	$msg_ok = "<td><span class='tab-success'>OK</span></td></tr>";

	foreach ($dirs_to_check as $the_d) 
	{
  		// Correct relative path for installer (var $inst_type is defined in newInstallStart_TL.php)
  		global $inst_type;
  		if (isset($inst_type))
  			$the_d = '..'.DIRECTORY_SEPARATOR.$the_d;
  			
  		$final_msg .= "<tr><td>Checking if <span class='mono'>{$the_d}</span> directory exists</td>";
  
		if(!file_exists($the_d)) 
		{
  			$errCounter += 1;
  			$final_msg .= $msg_ko; 
  		} 
		else 
		{
  			$final_msg .= $msg_ok;
    		$final_msg .= "<tr><td>Checking if <span class='mono'>{$the_d}</span> directory is writable</td>";
    		
  			if(!is_writable($the_d)) 
    		{
				$errCounter += 1;
  	  			$final_msg .= $msg_ko;  
  			}
    		else
    		{
				$final_msg .= $msg_ok;  
			}
   		}
	}

	return($final_msg);
}




/** 
 * print table with system checking results 
 * @param integer &$errCounter pointer to error counter
 **/
function reportCheckingSystem(&$errCounter)
{
	echo '<h2>System requirements</h2><table class="common" style="width: 100%;">';
	echo check_os();
	echo check_php_version($errCounter);
//	echo check_php_version($errCounter);
	echo '</table>';
}


/** 
 * print table with system checking results 
 * @param integer &$errCounter pointer to error counter
 **/
function reportCheckingWeb(&$errCounter)
{
	echo '<h2>Web and PHP configuration</h2><table class="common" style="width: 100%;">';
//	echo check_session($errCounter); // broken dependencies
	echo check_timeout($errCounter);
	echo check_php_settings($errCounter);
	echo check_php_extensions($errCounter);
	echo '</table>';
}


/** 
 * print table with system checking results 
 * @param integer &$errCounter pointer to error counter
 **/
function reportCheckingPermissions(&$errCounter)
{
	echo '<h2>Read/write permissions</h2><table class="common" style="width: 100%;">';
	echo check_dir_permissions($errCounter);
	echo '</table>';
}



?>
