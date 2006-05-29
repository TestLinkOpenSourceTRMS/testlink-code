<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: configCheck.php,v ${file_name} $
 *
 * @version $Revision: 1.6 $
 * @modified $Date: 2006/05/29 06:39:11 ${date} ${time} $ by $Author: franciscom $
 *
 * @author Martin Havlat
 * 
 * Check configuration functions
 *
 * 20060429 - franciscom - added checkForRepositoryDir()
 * 20060103 - scs - ADOdb changes
 **/
// ---------------------------------------------------------------------------------------------------
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

?>