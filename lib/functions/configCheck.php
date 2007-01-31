<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: configCheck.php,v ${file_name} $
 *
 * @version $Revision: 1.9 $
 * @modified $Date: 2007/01/31 14:19:44 ${date} ${time} $ by $Author: franciscom $
 *
 * @author Martin Havlat
 * 
 * Check configuration functions
 *
 * 20060429 - franciscom - added checkForRepositoryDir()
 * 20060103 - scs - ADOdb changes
 **/
// ---------------------------------------------------------------------------------------------------

/*
  function: 
           try to get home url.
           Code from Mantis Bugtracking system

  args :
  
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
 * checks if the install dir is present
 *
 * @return bool returns true if the install dir is present, false else
 *
 * @version 1.0
 * @author Andreas Morsing 
 **/
function checkForInstallDir()
{
	// 20050823
	$installer_dir = TL_ABS_PATH. DS . "install"  . DS;
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
	$userInfo = null;
	$bDefaultPwd = false;
	if (existLogin($db,"admin",$userInfo) && ($userInfo['password'] == md5('admin')))
		$bDefaultPwd = true;
	
	return $bDefaultPwd;
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
 **/
function getSecurityNotes(&$db)
{
  $repository['type']=config_get('repositoryType');
  $repository['path']=config_get('repositoryPath');
  

	$securityNotes = null;
	if (checkForInstallDir())
		$securityNotes[] = lang_get("sec_note_remove_install_dir");

	if (checkForAdminDefaultPwd($db))
		$securityNotes[] = lang_get("sec_note_admin_default_pwd");

	// 20060413 - franciscom
	if (!checkForBTSconnection())
	{
		$securityNotes[] = lang_get("bts_connection_problems");
	}
		
	// 20060429 - franciscom	
  if( $repository['type'] == TL_REPOSITORY_TYPE_FS )
  {
    $ret = checkForRepositoryDir($repository['path']);
    
	  if(!$ret['status_ok'])
	  {
		  $securityNotes[] = $ret['msg'];
	  }
	}

  // 20070121 - needed when schemas change has been done
  // This call can be removed when release is stable
  $my_msg=check_schema_version($db);
  if( strlen(trim($my_msg)) > 0 )
  {
    $securityNotes[] = $my_msg;
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
function checkForBTSconnection()
{
	global $g_bugInterface;
	$status_ok=1;
	if($g_bugInterface)
	{
		if( !$g_bugInterface->connect() )
		{
			$status_ok=0;
		}
	}
	return($status_ok);
}


// 20060429 - franciscom
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
  function: check_schema_version

  args :
  
  returns: 

*/
function check_schema_version($db)
{
  $sql = "SELECT * FROM db_version ORDER BY upgrade_ts DESC LIMIT 1";
  $res = $db->exec_query($sql);  

  $myrow = $db->fetch_array($res);
  switch (trim($myrow['version']))
  {
    case '1.7.0 Alpha':
   	case '1.7.0 Beta 1':
   	case '1.7.0 Beta 2':
   	     $msg="You need to upgrade your Testlink Database to 1.7.0 Beta 3 - <br>" .
   	          '<a href="SCHEMA_CHANGES" style="color: white"> click here to see the Schema changes </a><br>' .
   	          '<a href="./install/index.php" style="color: white">click here access install and upgrade page </a><br>';
   	     break;
   	          
    case '1.7.0 Beta 3':
   	     $msg="You need to upgrade your Testlink Database to 1.7.0 Beta 4 - <br>" .
   	          '<a href="SCHEMA_CHANGES" style="color: white"> click here to see the Schema changes </a><br>' .
   	          '<a href="./install/index.php" style="color: white">click here access install and upgrade page </a><br>';
   	     break;

    case '1.7.0 Beta 4':
   	     $msg="";
   	     break;
         
    default:
   	     $msg="Unknown Schema version, please upgrade your Testlink Database to 1.7 Beta 3";
   	     break;
  }
  return ($msg);
}

?>