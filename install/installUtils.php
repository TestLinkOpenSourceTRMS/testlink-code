<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: installUtils.php,v $
 * @version $Revision: 1.38.6.3 $
 * @modified $Date: 2010/11/20 11:48:19 $ by $Author: franciscom $
 * 
 * Functions for installation process
 *
 * Revisions :
 *	 20101120 - franciscom - check_mysql_version() minimun version increased
 *
 *   20090120 - havlatm - some functions moved to checkConfig.php. 
 *                         fixed reader
 *   20090101 - franciscom - check_php_version() - minimun version 5.2.0.
 *                           this is version need to use ext-js tree due to
 *                           need of json_* functions.
 *                           
 *   20080914 - franciscom - check_php_resource_settings()
 *   20080219 - franciscom - improvements on getDirSqlFiles
 *   20080102 - franciscom - fix bug with postgres on check_db_loaded_extension()
 *   20071021 - franciscom - getDirFiles() -> getDirSqlFiles()
 *   20070302 - franciscom - changed PHP minimun required versions
 *
 * ----------------------------------------------------------------------------------- */


/** 
 * @author fman
 * @author Code extracted from several places
 */
//
// rev:
//     20080219 - franciscom - after having problems with some directories
//                             added directory to file before using is_dir.
//                             Hint found on PHP Manual notes.
//
//     20071021 - franciscom - get only files with .sql extension
//     20070131 - franciscom - now returns an array
function getDirSqlFiles($dirPath, $add_dirpath=0)
{
$aFileSets=array(); 
$my_dir_path = '';	

foreach( $dirPath as $the_dir)
{
  if ( $add_dirpath )
  {
    $my_dir_path = $the_dir;
  }    		           

  if ($handle = opendir($the_dir)) 
  {
      clearstatcache();
      while (false !== ($file = readdir($handle))) 
      {
          $is_folder=is_dir($the_dir . $file);
          // needed because is_dir() cached result. See PHP Manual
          clearstatcache();
          
          if ($file != "." && $file != ".." && !$is_folder)
          {
             // 20071021 - use only is extension sql
             $file=trim($file);
             $path_parts=pathinfo($file);
             if( isset($path_parts['extension']) && $path_parts['extension'] == 'sql' )
             {   
               $filesArr[] = $my_dir_path . $file;
             }  
          } 
      }
      closedir($handle);
  }  
  
  sort($filesArr);
  reset($filesArr);
  $aFileSets[]=$filesArr;
}


return $aFileSets; 
}
// +----------------------------------------------------------------------+


/**
  function: getTableList
            a foolish wrapper - 20051231 - fm
  args :
  
  returns: map or null
  @author Jo�o Prado Maia <jpm@mysql.com> Eventum - Issue Tracking System
*/
function getTableList($db)
{
    $my_ado = $db->get_dbmgr_object();
    $tables = $my_ado->MetaTables('TABLES',false,'db_version');
    return($tables);
}



/*
  function: getUserList

  args:
  
  returns: map or null
  
  rev :
       20071104 - franciscom
       added code for mssql
       added trim() to avoid problems with mssql,
       while creating values for return map.

*/
function getUserList(&$db,$db_type)
{
   $users=null;
   switch($db_type)
   {
      case 'mysql':
      $result = $db->exec_query('SELECT DISTINCT user AS user FROM user');
      break;
      
      case 'postgres':
      $result = $db->exec_query('SELECT DISTINCT usename AS user FROM pg_user');
      break;
   
      case 'mssql':
      // info about running store procedures, get form adodb manuals
      // Important:
      // From ADODB manual - Prepare() documentation
      //
      // Returns an array containing the original sql statement in the first array element; 
      // the remaining elements of the array are driver dependent.
      //
      // 20071104 - franciscom
      // Looking into adodb-mssql.inc.php, you will note that array[1] 
      // is a mssql stm object.
      // This info is very important, to use mssql_free_statement()
      //
      $stmt = $db->db->PrepareSP('SP_HELPLOGINS'); # note that the parameter name does not have @ in front!
      $result=$db->db->Execute($stmt); 
      
      // Very important:
      // Info from PHP Manual notes
      // mssql_free_statement()
      //
      // mitch at 1800radiator dot kom (23-Mar-2005 06:02)
      // Maybe it's unique to my FreeTDS configuration, but if I don't call mssql_free_statement() 
      // after every stored procedure (i.e. mssql_init, mssql_bind, mssql_execute, mssql_fetch_array), 
      // all subsequent stored procedures on the same database connection will fail.
      // I only mention it because this man-page deprecates the use of mssql_free_statement(), 
      // saying it's only there for run-time memory concerns.  
      // At least in my case, it's also a crucial step in the process of running a stored procedure.  
      // If anyone else has problems running multiple stored procedures on the same connection, 
      // I hope this helps them out.
      //
      // franciscom - 20071104
      // Without this was not possible to call other functions that use store procedures,
      // because I've got:
      // a) wrong results
      // b) mssql_init() errors
      //
      mssql_free_statement($stmt[1]);
      break;
   
   }
   
   $users = array();
   
   // MySQL NOTE:
   // if the user cannot select from the mysql.user table, then return an empty list
   //
   if (!$result) 
   {
       return $users;
   }
   if( $db_type == 'mssql' )
   {
     while (!$result->EOF) 
     { 
       $row = $result->GetRowAssoc();
       $users[] = trim($row['LOGINNAME']);
       $result->MoveNext(); 
     } 
   }
   else
   {
   while ($row = $db->fetch_array($result)) 
   {
       $users[] = trim($row['user']);
     }
   }
   return($users);
}



/*
Function: create_user_for_db
          
          Check for user existence.
          
          If doesn't exist
             Creates a user/passwd with the following GRANTS: SELECT, UPDATE, DELETE, INSERT
             for the database 
          Else
             do nothing
                

20051217 - fm
refactoring - cosmetics changes
                
20050910 - fm
webserver and dbserver on same machines      => user will be created as user
webserver and dbserver on DIFFERENT machines => user must be created as user@webserver

if @ in login ->  get the hostname using splitting, and use it
                                   during user creation on db. 
                
                
*/
function create_user_for_db($db_type,$db_name,$db_server, $db_admin_name, $db_admin_pass,
                            $login, $passwd)
{
$db = new database($db_type);

$user_host = explode('@',$login);
$the_host = 'localhost';

if ( count($user_host) > 1 )
{
  $login    = $user_host[0];    
  $the_host = trim($user_host[1]);  
}

$try_create_user=0;
switch($db_type)
{
    case 'mssql':
    @$conn_res = $db->connect(NO_DSN, $db_server, $db_admin_name, $db_admin_pass,$db_name); 
    $msg="For MSSQL, no attempt is made to check for user existence";
    $try_create_user=1;
    break;
    
    case 'postgres':
    @$conn_res = $db->connect(NO_DSN, $db_server, $db_admin_name, $db_admin_pass,$db_name); 
    $try_create_user=1;
    break;
    
    case 'mysql':
    @$conn_res = $db->connect(NO_DSN, $db_server, $db_admin_name, $db_admin_pass, 'mysql'); 
    $try_create_user=1;
    break;

    default:
    $try_create_user=0;
    break;

}

if( $try_create_user==1)
{
  $user_list = getUserList($db,$db_type);
  $login_lc = strtolower($login);
  $msg = "ko - fatal error - can't get db server user list !!!";
}

if ($try_create_user==1 && !is_null($user_list) && count($user_list) > 0) 
{

    $user_list = array_map('strtolower', $user_list);
    $user_exists=in_array($login_lc, $user_list);
    if (!$user_exists) 
    {
    	$msg = '';
    	switch($db_type)
    	{
        
        case 'mssql':
        $op = _mssql_make_user_with_grants($db,$the_host,$db_name,$login,$passwd);
        _mssql_set_passwd($db,$login,$passwd);
        break;

        case 'postgres':
        $op = _postgres_make_user_with_grants($db,$the_host,$db_name,$login,$passwd);
        break;

        case 'mysql':
        default:
        // for MySQL making the user and assign right is the same operation
        $op = _mysql_make_user($db,$the_host,$db_name,$login,$passwd);
        break;

      }  
    }
    else
    {
      // just assign rights on the database
    	$msg = 'ok - user_exists';
      switch($db_type)
    	{
        case 'mysql':
        $op = _mysql_assign_grants($db,$the_host,$db_name,$login,$passwd);
        break;
        
        case 'postgres':
        $op = _postgres_assign_grants($db,$the_host,$db_name,$login,$passwd);
        break;

        case 'mssql':
        $op = _mssql_assign_grants($db,$the_host,$db_name,$login,$passwd);
        break;

      }  
      
    }
    if( !$op->status_ok )
    {
       $msg .= " but ...";    
    } 
    $msg .= " " . $op->msg;    
    
    
}

if( !is_null($db) )
{
    $db->close();
}

return($msg);
}  /* Function ends */


/*
  function: close_html_and_exit()

  args :
  
  returns: 

*/
function close_html_and_exit()
{
echo "
		</td>
      </tr>
    </table></td>
  </tr>" .
  '<tr class="fancyRow2">
		<td class="border-top-bottom smallText">&nbsp;</td>
		<td class="border-top-bottom smallText" align="right">&nbsp;</td>' .
  "</tr>
</table>
</body>
</html>";

exit;
}  /* Function ends */


/*
  function: check_mysql_version()

  args : [$conn]
  
  returns: 

  rev :

*/
function check_mysql_version($conn=null)
{
$min_ver = "5.0.3";  // 20101120 - franciscom - seems ti be first with size(varchar) > 255 

$errors=0;	
$final_msg = "</b><br/>Checking MySQL version:<b> ";

// As stated in PHP Manual:
//
// string mysql_get_server_info ( [resource link_identifier] )
// link_identifier: The MySQL connection. 
//                  If the link identifier is not specified, 
//                  the last link opened by mysql_connect() is assumed. 
//                  If no such link is found, it will try to create one as if mysql_connect() 
//                  was called with no arguments. 
//                  If by chance no connection is found or established, an E_WARNING level warning is generated.
//
// In my experience thi will succed only if anonymous connection to MySQL is allowed
// 

if( !$conn )
{
	$my_version = @mysql_get_server_info($conn);
}
else
{
	$my_version = @mysql_get_server_info();
}

if( $my_version !== FALSE )
{

  // version_compare:
  // -1 if left is less, 0 if equal, +1 if left is higher
  $php_ver_comp =  version_compare($my_version, $min_ver);
  
  if($php_ver_comp < 0) 
  {
  	$final_msg .= "<span class='notok'>Failed!</span> - You are running on MySQL " . 
  	        $my_version . ", and TestLink requires MySQL " . $min_ver . " or greater";
  	$errors += 1;
  } 
  else 
  {
  	$final_msg .= "<span class='ok'>OK! (" . $my_version . " >= " . $min_ver . ")</span>";
  }
}
else
{
	$final_msg .= "<span class='notok'>Warning!: Unable to get MySQL version (may be due to security restrictions) - " .
	              "Remember that Testlink requires MySQL >= " . $min_ver . ")</span>";
}	  

$ret = array ('errors' => $errors,
              'msg' => $final_msg);


return ($ret);
}  //function end



// check to see if required PEAR modules are installed
function check_pear_modules()
{
    $errors = 0;    
    $final_msg = '</b><br />Checking if PEAR modules are installed:<b>';
    
    // SpreadSheet_Excel_Writer is needed for TestPlanResultsObj that does excel reporting
    if(false == include_once('Spreadsheet/Excel/Writer.php'))
    {
        $final_msg .= '<span class="notok">Failed! - Spreadsheet_Excel_Writer PEAR Module is required.</span><br />See' .
                '<a href="http://pear.php.net/package/Spreadsheet_Excel_Writer">' .
                'http://pear.php.net/package/Spreadsheet_Excel_Writer</a> for additional information';
        $errors += 1;                        
    }
    else
    {
        $final_msg .= "<span class='ok'>OK!</span>";
    }

$ret = array ('errors' => $errors,
              'msg' => $final_msg);

return ($ret);  
} // function end

// 20051231 - fm
function check_db_version($dbhandler)
{

switch ($dbhandler->db->databaseType)
{
	case 'mysql':
	$min_ver = "4.1.0";
	$db_verbose="MySQL";
  break;
  
  case 'postgres':
  case 'postgres7':
  case 'postgres8':
  case 'postgres64':
	$min_ver = "8";
  $db_verbose="PostGres";
  break;

  // 20071010 - franciscom
	case 'mssql':
	$min_ver = "8";
  $db_verbose="MSSQL";
  break;
	
}

$errors=0;	
$final_msg = "</b><br/>Checking {$db_verbose} version:<b> ";

$server_info = @$dbhandler->get_version_info();
$my_version = trim($server_info['version']);

if( strlen($my_version) != 0 )
{

  // version_compare:
  // -1 if left is less, 0 if equal, +1 if left is higher
  $ver_comp =  version_compare($my_version, $min_ver);
  
  if($ver_comp < 0) 
  {
  	$final_msg .= "<span class='notok'>Failed!</span> - You are running on {$db_verbose} " . 
  	        $my_version . ", and TestLink requires {$db_verbose} " . $min_ver . " or greater";
  	$errors += 1;
  } 
  else 
  {
  	$final_msg .= "<span class='ok'>OK! (" . $my_version . " >= " . $min_ver . ")</span>";
  }
}
else
{
	$final_msg .= "<span class='notok'>Warning!: Unable to get {$db_verbose} version (may be due to security restrictions) - " .
	              "Remember that Testlink requires {$db_verbose} >= " . $min_ver . ")</span>";
}	  

$ret = array ('errors' => $errors,
              'msg' => $final_msg);


return ($ret);
}  //function end



/*
  function: check_db_loaded_extension
  args :
  returns: 

  rev :
       20080102 - franciscom - fix to check postgres
*/
function check_db_loaded_extension($db_type)
{
    $ext2search=$db_type;  
    $dbType2PhpExtension=array('postgres' => 'pgsql');
    
    if( isset($dbType2PhpExtension[$db_type]) )
    {
      $ext2search=$dbType2PhpExtension[$db_type];  
    }
      
    $msg_ko = "<span class='notok'>Failed!</span>";
    $msg_ok = '<span class="ok">OK!</span>';
    $tt=array_flip(get_loaded_extensions());
    
    $errors=0;	
    $final_msg = "</b><br/>Checking PHP DB extensions<b> ";
    
    if( !isset($tt[$ext2search]) )
    {
    	$final_msg .= "<span class='notok'>Warning!: Your PHP installation don't have the {$db_type} extension {$ext2search}- " .
    	              "without it is IMPOSSIBLE to use Testlink.</span>";
    	$final_msg .= $msg_ko;
    	$errors += 1;
    }
    else
    {
    	$final_msg .= $msg_ok;
    }
    $ret = array ('errors' => $errors,
                  'msg' => $final_msg);
    
    return ($ret);
}  //function end





// 20060514 - franciscom
function _mysql_make_user($dbhandler,$db_host,$db_name,$login,$passwd)
{

$op = new stdclass();

$op->status_ok=true;
$op->msg = 'ok - new user';     

// Escaping following rules form:
//
// MySQL Manual
// 9.2. Database, Table, Index, Column, and Alias Names
//
$stmt = "GRANT SELECT, UPDATE, DELETE, INSERT ON " . 
        "`" . $dbhandler->prepare_string($db_name) . "`" . ".* TO " . 
        "'" . $dbhandler->prepare_string($login) . "'";
        
// 20070310 - $the_host -> $db_host        
if (strlen(trim($db_host)) != 0)
{
  $stmt .= "@" . "'" . $dbhandler->prepare_string($db_host) . "'";
}         
$stmt .= " IDENTIFIED BY '" .  $passwd . "'";

      
if (!@$dbhandler->exec_query($stmt)) 
{
    $op->msg = "ko - " . $dbhandler->error_msg();
    $op->status_ok=false;
}
else
{
  // 20051217 - fm
  // found that you get access denied in this situation:
  // 1. you have create the user with grant for host.
  // 2. you are running your app on host.
  // 3. you don't have GRANT for localhost.       	
  // 
  // Then I've decide to grant always access from localhost
  // to avoid this kind of problem.
  // I hope this is not a security hole.
  //
  //
  // 20070310 - $the_host -> $db_host        
  if( strcasecmp('localhost',$db_host) != 0)
  {
    // 20060514 - franciscom - missing 
    $stmt = "GRANT SELECT, UPDATE, DELETE, INSERT ON " . 
             "`" . $dbhandler->prepare_string($db_name) . "`" . ".* TO " . 
             "'" . $dbhandler->prepare_string($login) . "'@'localhost'" .
            " IDENTIFIED BY '" .  $passwd . "'";
    if ( !@$dbhandler->exec_query($stmt) ) 
    {
      $op->msg = "ko - " . $dbhandler->error_msg();
      $op->status_ok=false;
    }
  }
}
     
return ($op); 
}


// 20060514 - franciscom
// for MySQL just a wrapper
function _mysql_assign_grants($dbhandler,$db_host,$db_name,$login,$passwd)
{

$op = _mysql_make_user($dbhandler,$db_host,$db_name,$login,$passwd);

if( $op->status_ok)
{
  $op->msg = 'ok - grant assignment';
}     

return ($op); 
}


/*
  function: _postgres_make_user_with_grants

  args :
  
  returns: 

*/
function _postgres_make_user_with_grants(&$db,$db_host,$db_name,$login,$passwd)
{
$op->status_ok=true;
$op->msg='';

$int_op = _postgres_make_user($db,$db_host,$db_name,$login,$passwd);

if( $int_op->status_ok)
{
  $op->msg = $int_op->msg;
  $int_op = _postgres_assign_grants($db,$db_host,$db_name,$login,$passwd);

  $op->msg .= " " . $int_op->msg;
  $op->status_ok=$int_op->status_ok;
}

return($op);
}  // function end


/*
  function: _postgres_make_user

  args :
  
  returns: 

*/
function _postgres_make_user(&$db,$db_host,$db_name,$login,$passwd)
{
$op->status_ok=true;  
$op->msg = 'ok - new user'; 
    
$sql = 'CREATE USER "' . $db->prepare_string($login) . '"' . " ENCRYPTED PASSWORD '{$passwd}'";
if (!@$db->exec_query($sql)) 
{
    $op->status_ok=false;  
    $op->msg = "ko - " . $db->error_msg();
}
return ($op); 
}



/*
  function: _postgres_assign_grants

  args :
  
  returns: 

*/
function _postgres_assign_grants(&$db,$db_host,$db_name,$login,$passwd)
{
	$op = new stdclass();
	$op->status_ok=true;  
	$op->msg = 'ok - grant assignment';     
	
	/*
	if( $op->status_ok )
	{
	    $sql=" REVOKE ALL ON SCHEMA public FROM public ";
	    if (!@$dbhandler->exec_query($sql)) 
	    {
	        $op->status_ok=false;  
	        $op->msg = "ko - " . $dbhandler->error_msg();
	    }
	}
	*/
	
	if( $op->status_ok )
	{
	    $sql = 'ALTER DATABASE "' . $db->prepare_string($db_name) . '" OWNER TO ' . 
	                        '"' . $db->prepare_string($login) . '"';
	    if (!@$db->exec_query($sql)) 
	    {
	        $op->status_ok=false;  
	        $op->msg = "ko - " . $db->error_msg();
	    }
	}
	
	if( $op->status_ok )
	{
	    $sql = 'ALTER SCHEMA public OWNER TO ' .  '"' . $db->prepare_string($login) . '"';
	    if (!@$db->exec_query($sql)) 
	    {
	        $op->status_ok=false;  
	        $op->msg = "ko - " . $db->error_msg();
	    }
	}
	
	return ($op); 
}


/*
  function: _mssql_make_user_with_grants 

  args :
  
  returns: 

*/
function _mssql_make_user_with_grants($db,$the_host,$db_name,$login,$passwd)
{
  _mssql_make_user($db,$the_host,$db_name,$login,$passwd);
                                                                                                                                                                                                                                                                                                                                                                                                                                                                  
  $op->status_ok=true;
  $op->msg = 'ok - new user';     

  // Check if has been created, because I'm not able to get return code.
  $user_list=getUserList($db,'mssql');
  $user_list=array_map('strtolower', $user_list);
  $user_exists=in_array(trim($login), $user_list);
  if( !$user_exists )
  {
    $op->status_ok=false;  
    $op->msg = "ko - " . $db->error_msg();
  }
  else
  {
    _mssql_assign_grants($db,$the_host,$db_name,$login,$passwd);  
}
  return $op;
  
} // function end
  
  
function _mssql_make_user($db,$the_host,$db_name,$login,$passwd)
{

// Transact-SQL Reference                                                                                                                                                                                                                                                                                                                                                                                                                                           
//                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
// sp_addlogin                                                                                                                                                                                                                                                                                                                                                                                                                                                       
//   New Information - SQL Server 2000 SP3.                                                                                                                                                                                                                                                                                                                                                                                                                          
//                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
// Creates a new Microsoft® SQL Server™ login that allows a user 
// to connect to an instance of SQL Server using SQL Server Authentication.                                                                                                                                                                                                                                                                                                                            
//                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
// Security Note  When possible, use Windows Authentication.                                                                                                                                                                                                                                                                                                                                                                                                         
//                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
// Syntax                                                                                                                                                                                                                                                                                                                                                                                                                                                            
// sp_addlogin [ @loginame = ] 'login'                                                                                                                                                                                                                                                                                                                                                                                                                               
//     [ , [ @passwd = ] 'password' ]                                                                                                                                                                                                                                                                                                                                                                                                                                
//     [ , [ @defdb = ] 'database' ]                                                                                                                                                                                                                                                                                                                                                                                                                                 
//     [ , [ @deflanguage = ] 'language' ]                                                                                                                                                                                                                                                                                                                                                                                                                           
//     [ , [ @sid = ] sid ]                                                                                                                                                                                                                                                                                                                                                                                                                                          
//     [ , [ @encryptopt = ] 'encryption_option' ]                                                                                                                                                                                                                                                                                                                                                                                                                   
//                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
// Arguments                                                                                                                                                                                                                                                                                                                                                                                                                                                         
// [@loginame =] 'login'                                                                                                                                                                                                                                                                                                                                                                                                                                             
// Is the name of the login. login is sysname, with no default.                                                                                                                                                                                                                                                                                                                                                                                                      
//                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
// [@passwd =] 'password'                                                                                                                                                                                                                                                                                                                                                                                                                                            
// Is the login password. password is sysname, with a default of NULL. 
// After sp_addlogin has been executed, the password is encrypted and stored in the system tables.                                                                                                                                                                                                                                                                                               
//                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
// [@defdb =] 'database'                                                                                                                                                                                                                                                                                                                                                                                                                                             
// Is the default database of the login (the database the login is connected to after logging in). 
// database is sysname, with a default of master.                                                                                                                                                                                                                                                                                                                    
//                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
// [@deflanguage =] 'language'                                                                                                                                                                                                                                                                                                                                                                                                                                       
// Is the default language assigned when a user logs on to SQL Server. 
// language is sysname, with a default of NULL. 
// If language is not specified, language is set to the server's current default language 
// (defined by the sp_configure configuration variable default language). 
// Changing the server's default language does not change the default language for existing logins. 
// language remains the same as the default language used when the login was added.  
//                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
// [@sid =] sid                                                                                                                                                                                                                                                                                                                                                                                                                                                      
// Is the security identification number (SID). sid is varbinary(16), with a default of NULL. 
// If sid is NULL, the system generates a SID for the new login.  
// Despite the use of a varbinary data type, values other than NULL must be 
// exactly 16 bytes in length, and must not already exist. 
// SID is useful, for example, when you are scripting or moving SQL Server logins 
// from one server to another and you want the logins to have the same SID between servers.
//                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
// [@encryptopt =] 'encryption_option'                                                                                                                                                                                                                                                                                                                                                                                                                               
// Specifies whether the password is encrypted when stored in the system tables. 
// encryption_option is varchar(20), and can be one of these values.                                                                                                                                                                                                                                                                                                                   
//                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
// Value Description                                                                                                                                                                                                                                                                                                                                                                                                                                                 
// NULL The password is encrypted. This is the default.                                                                                                                                                                                                                                                                                                                                                                                                              
// skip_encryption The password is already encrypted. 
// SQL Server should store the value without re-encrypting it.                                                                                                                                                                                                                                                                                                                                                    
// skip_encryption_old The supplied password was encrypted by a previous version of SQL Server.  
// SQL Server should store the value without re-encrypting it. 
// This option is provided for upgrade purposes only.                                                                                                                                                                                                                                                      
//                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
//                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
// Return Code Values                                                                                                                                                                                                                                                                                                                                                                                                                                                
// 0 (success) or 1 (failure)                                                                                                                                                                                                                                                                                                                                                                                                                                        
//                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
// Permissions                                                                                                                                                                                                                                                                                                                                                                                                                                                       
// Only members of the sysadmin and securityadmin fixed server roles can execute sp_addlogin.                                                                                                                                                                                                                                                                                                                                                                        
//                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
// Examples                                                                                                                                                                                                                                                                                                                                                                                                                                                          
// A. Create a login ID with master default database                                                                                                                                                                                                                                                                                                                                                                                                                 
// This example creates an SQL Server login for the user Victoria, without specifying a default database.                                                                                                                                                                                                                                                                                                                                                            
//                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
// EXEC sp_addlogin 'Victoria', 'B1r12-36'                                                                                                                                                                                                                                                                                                                                                                                                                           
//                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
// B. Create a login ID and default database                                                                                                                                                                                                                                                                                                                                                                                                                         
// This example creates a SQL Server login for the user Albert, with a password of "B1r12-36" 
// and a default database of corporate.                                                                                                                                                                                                                                                                                                                                   
//                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
// EXEC sp_addlogin 'Albert', 'B1r12-36', 'corporate'                                                                                                                                                                                                                                                                                                                                                                                                                
//                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
// C. Create a login ID with a different default language                                                                                                                                                                                                                                                                                                                                                                                                            
// This example creates an SQL Server login for the user Claire Picard, with a password of "B1r12-36", 
// a default database of public_db, and a default language of French.                                                                                                                                                                                                                                                                                            
//                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
// EXEC sp_addlogin 'Claire Picard', 'B1r12-36', 'public_db', 'french'                                                                                                                                                                                                                                                                                                                                                                                               
//                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
// D. Create a login ID with a specific SID                                                                                                                                                                                                                                                                                                                                                                                                                          
// This example creates an SQL Server login for the user Michael, with a password of "B1r12-36," 
// a default database of pubs, a default language of us_english, 
// and an SID of 0x0123456789ABCDEF0123456789ABCDEF.                                                                                                                                                                                                                                                     
//                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
// EXEC sp_addlogin 'Michael', 'B1r12-36', 'pubs', 'us_english', 0x0123456789ABCDEF0123456789ABCDEF                                                                                                                                                                                                                                                                                                                                                                  
//                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
// E. Create a login ID and do not encrypt the password                                                                                                                                                                                                                                                                                                                                                                                                              
// This example creates an SQL Server login for the user Margaret with a password of "B1r12-36" on Server1, 
// extracts the encrypted password, and then adds the login for the user Margaret to Server2 using 
// the previously encrypted password but does not further encrypt the password. 
// User Margaret can then log on to Server2 using the password Rose.                                                                                                           
  
                                                                                                                                                                                                                                                                                                                                                                                                                                                                  
  $op->status_ok=true;
  $op->msg = 'ok - new user';     

  //sp_addlogin [ @loginame = ] 'login'                                                                                                                                                                                                                                                                                                                                                                                                                               
  //  [ , [ @passwd = ] 'password' ]                                                                                                                                                                                                                                                                                                                                                                                                                                
  //  [ , [ @defdb = ] 'database' ]                                                                                                                                                                                                                                                                                                                                                                                                                                 
  //  [ , [ @deflanguage = ] 'language' ]                                                                                                                                                                                                                                                                                                                                                                                                                           
  //  [ , [ @sid = ] sid ]                                                                                                                                                                                                                                                                                                                                                                                                                                          
  //  [ , [ @encryptopt = ] 'encryption_option' ]                                                                                                                                                                                                                                                                                                                                                                                                                   
  //
  // Important:
  // From ADODB manual - Prepare() documentation
  //
  // Returns an array containing the original sql statement in the first array element; 
  // the remaining elements of the array are driver dependent.
  //
  // 20071104 - franciscom
  // Looking into adodb-mssql.inc.php, you will note that array[1] 
  // is a mssql stm object.
  // This info is very important, to use mssql_free_statement()
  //
  
  $sid=null;
  $encryptopt=null;
  
  $stmt = $db->db->PrepareSP('SP_ADDLOGIN');
  $db->db->InParameter($stmt,$login,'loginame');
  // $db->db->InParameter($stmt,$passwd,'passwd');
  $db->db->InParameter($stmt,$db_name,'defdb');
  // $db->db->InParameter($stmt,$sid,'sid'); 
  // $db->db->InParameter($stmt,$encryptopt,'encryptopt');
    
  $db->db->OutParameter($stmt,$retval,'RETVAL');
  $result=$db->db->Execute($stmt); 
  
  // Very important:
  // Info from PHP Manual notes
  // mssql_free_statement()
  //
  // mitch at 1800radiator dot kom (23-Mar-2005 06:02)
  // Maybe it's unique to my FreeTDS configuration, but if I don't call mssql_free_statement() 
  // after every stored procedure (i.e. mssql_init, mssql_bind, mssql_execute, mssql_fetch_array), 
  // all subsequent stored procedures on the same database connection will fail.
  // I only mention it because this man-page deprecates the use of mssql_free_statement(), 
  // saying it's only there for run-time memory concerns.  
  // At least in my case, it's also a crucial step in the process of running a stored procedure.  
  // If anyone else has problems running multiple stored procedures on the same connection, 
  // I hope this helps them out.
  //
  // franciscom - 20071104
  // Without this was not possible to call other functions that use store procedures,
  // because I've got:
  // a) wrong results
  // b) mssql_init() errors
  //
  mssql_free_statement($stmt[1]);
  
  // I've problems trying to set password,
  // then I will use as workaround setting a NULL password
  // and after do a password change.
  $passwd_null=NULL;
  $stmt = $db->db->PrepareSP('SP_PASSWORD');
  $db->db->InParameter($stmt,$login,'loginame');
  $db->db->InParameter($stmt,$passwd_null,'old');
  $db->db->InParameter($stmt,$passwd,'new');
  $result=$db->db->Execute($stmt); 
  mssql_free_statement($stmt[1]);
    
  
} // function end


/*
  function: _mssql_assign_grants
  
  args :
  
  returns: 
  
*/
function _mssql_assign_grants($db,$the_host,$db_name,$login,$passwd)
{ 

  // $stmt = $db->db->PrepareSP('SP_GRANTDBACCESS');
  // $db->db->InParameter($stmt,$login,'loginame');
  // $result=$db->db->Execute($stmt); 
  // mssql_free_statement($stmt[1]);
  // 
  $db_role='db_owner';
  $stmt = $db->db->PrepareSP('SP_ADDUSER');
  $db->db->InParameter($stmt,$login,'loginame');
  $db->db->InParameter($stmt,$login,'name_in_db');
  $db->db->InParameter($stmt,$db_role,'grpname');
  $result=$db->db->Execute($stmt); 
  mssql_free_statement($stmt[1]);
  

  $op = new stdClass();	  
  $op->status_ok=true;  
  $op->msg = 'ok - grant assignment';     
  
  return $op;
} // function end

/*
  function: 

  args :
  
  returns: 

*/
function _mssql_set_passwd($db,$login,$passwd)
{
  // $passwd_null=NULL;
  //$stmt = $db->db->PrepareSP('SP_PASSWORD');
  //$db->db->InParameter($stmt,$login,'loginame');
  //$db->db->InParameter($stmt,$passwd,'old');
  //$db->db->InParameter($stmt,$passwd,'new');
  //$result=$db->db->Execute($stmt);
  // 
  //// echo "<pre>debug 20071104 - \ - " . __FUNCTION__ . " --- "; print_r($result); echo "</pre>";
  //mssql_free_statement($stmt[1]);
  
  //$sql="EXEC SP_PASSWORD '{$passwd}','{$passwd}',{$login}";
  $sql="EXEC SP_PASSWORD NULL,'{$passwd}',{$login}";
  $db->exec_query($sql);
  

} // function end


/*
  function: important_reminder()

  args :
  
  returns: 

*/
function important_reminder()
{
echo ' <br><br><span class="headers">YOUR ATTENTION PLEASE:</span><br>To have a fully functional installation 
       You need to configure mail server settings, following this steps<br>
       <ul>
       <li>copy from config.inc.php, [SMTP] Section into custom_config.inc.php.</li>
       <li>complete correct data regarding email addresses and mail server.</li></ul><p>';
}  /* Function ends */
?>