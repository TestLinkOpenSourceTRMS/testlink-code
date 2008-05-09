<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: configCheck.php,v ${file_name} $
 *
 * @version $Revision: 1.27 $
 * @modified $Date: 2008/05/09 17:14:19 ${date} ${time} $ by $Author: schlundus $
 *
 * @author Martin Havlat
 * 
 * Check configuration functions
 *
 **/
// ---------------------------------------------------------------------------------------------------
require_once('plan.core.inc.php');

/*
  function: 
           try to get home url.
           Code from Mantis Bugtracking system
  
  returns: 
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
 * checks if needed functions and extensions are defined 
 *
 * @param array [ref] msgs will be appended
 * @return bool returns true if all extension or functions ar present or defined
 *
 * @version 1.0
 * @author Andreas Morsing 
 **/
function checkForExtensions(&$msg)
{
	$bSuccess = true;
	
	if (!function_exists('domxml_open_file'))
		$msg[] = lang_get("error_domxml_missing");
	
	return $bSuccess;
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
function checkForLDAPExtension(&$bLDAPEnabled)
{
	$login_method = config_get('login_method');
	
	$bLDAPEnabled = ('LDAP' == $login_method ) ? 1 : 0;
	if(!$bLDAPEnabled || ($bLDAPEnabled && extension_loaded("ldap")))
		return true;
	return 	false;
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
 *      20070626 - franciscom - added LDAP checks  
 **/
function getSecurityNotes(&$db)
{
	$repository['type'] = config_get('repositoryType');
	$repository['path'] = config_get('repositoryPath');
  
	$securityNotes = null;
	if (checkForInstallDir())
		$securityNotes[] = lang_get("sec_note_remove_install_dir");

	$bLDAPEnabled = false;
	if (!checkForLDAPExtension($bLDAPEnabled))
		$securityNotes[] = lang_get("ldap_extension_not_loaded");
		
    if (!$bLDAPEnabled && checkForAdminDefaultPwd($db))
		$securityNotes[] = lang_get("sec_note_admin_default_pwd");
  
	if (!checkForBTSConnection())
		$securityNotes[] = lang_get("bts_connection_problems");
		
	if($repository['type'] == TL_REPOSITORY_TYPE_FS)
	{
		$ret = checkForRepositoryDir($repository['path']);
		if(!$ret['status_ok'])
			$securityNotes[] = $ret['msg'];
	}

	// 20070121 - needed when schemas change has been done
	// This call can be removed when release is stable
	$msg = checkSchemaVersion($db);
	if(strlen($msg))
		$securityNotes[] = $msg;
	
	// 20070911 - fixing bug 1021 
	$msg = checkForTestPlansWithoutTestProjects($db);
	if(strlen($msg))
		$securityNotes[] = $msg;
	
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

?>
