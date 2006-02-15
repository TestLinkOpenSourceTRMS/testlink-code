<?php
/* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: installUtils.php,v 1.13 2006/02/15 14:01:03 franciscom Exp $ 

20060214 - franciscom - added warning regarding valid database names
20060108 - fm - removed some functions
20051231 - fm - changes due to ADODB
20051002 - fm - messages changes
20050925 - fm - changes to getDirFiles()
20050910 - fm - refactoring
20050830 - fm - added check_php_settings()
*/


// Code extracted from several places:

// +----------------------------------------------------------------------+
// From PHP Manual - User's Notes
// +----------------------------------------------------------------------+
//
// 20050925 - added sort()
function getDirFiles($dirPath, $add_dirpath=0)
{
$my_dir_path = '';	
if ( $add_dirpath )
{
  $my_dir_path = $dirPath;
}    		           
if ($handle = opendir($dirPath)) 
{
    while (false !== ($file = readdir($handle))) 
    
    // 20050808 - fm 
    // added is_dir() to exclude dirs
    if ($file != "." && $file != ".." && !is_dir($file))
    {
        $filesArr[] = $my_dir_path . trim($file);
    }            
    closedir($handle);
}  

// 20050925 - fm
sort($filesArr);
reset($filesArr);

return $filesArr; 
}
// +----------------------------------------------------------------------+



//
// +----------------------------------------------------------------------+
// | Eventum - Issue Tracking System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003, 2004, 2005 MySQL AB                              |
// |                                                                      |
// +----------------------------------------------------------------------+
// | Authors: João Prado Maia <jpm@mysql.com>                             |
// +----------------------------------------------------------------------+
//
// @(#) $Id: installUtils.php,v 1.13 2006/02/15 14:01:03 franciscom Exp $
//

// a foolish wrapper - 20051231 - fm
function getTableList($db)
{
    $my_ado = $db->get_dbmgr_object();
    $tables = $my_ado->MetaTables('TABLES',false,'db_version');
    return($tables);
}


// 20060101 - fm - valid only for MySQL
function getUserList($system_schema)
{
   $res = $system_schema->exec_query('SELECT DISTINCT user from user');
   
   $users = array();
   // if the user cannot select from the mysql.user table, then return an empty list
   if (!$res) {
       return $users;
   }
   while ($row = $system_schema->fetch_array($res)) 
   {
       $users[] = $row['user'];
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
function create_user_for_db($system_schema, $db_name, $login, $passwd)
{

// 20050910 - fm
$user_host = explode('@',$login);
$the_host = 'localhost';

if ( count($user_host) > 1 )
{
  $login    = $user_host[0];    
  $the_host = trim($user_host[1]);  
}

$user_list = getUserList($system_schema);
$login_lc = strtolower($login);
$msg = "ko - fatal error - can't get db server user list !!!";

// echo "\$the_host=" .$the_host . "<br>";
//echo "<pre>debug174"; print_r($user_list); echo "</pre>";

if (count($user_list) > 0) 
{
	  $msg = 'ok - user_exists';
    $user_list = array_map('strtolower', $user_list);
    if (!in_array($login_lc, $user_list)) 
    {
    	$msg = 'ok - new user';
      $stmt = "GRANT SELECT, UPDATE, DELETE, INSERT ON " . 
              $db_name . ".* TO '" . $login . "'";
              
      if (strlen(trim($the_host)) != 0)
      {
        $stmt .= "@" . "'" . $the_host . "'";
      }         
      $stmt .= " IDENTIFIED BY '" .  $passwd . "'";
      
            
      if (!@$system_schema->exec_query($stmt)) 
      {
          $msg = "ko - " . $system_schema->error_msg();
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
        if( strcasecmp('localhost',$the_host) != 0)
        {
          $stmt = "GRANT SELECT, UPDATE, DELETE, INSERT ON " . 
                   $db_name . ".* TO '" . $login . "'@'localhost'" .
                  " IDENTIFIED BY '" .  $passwd . "'";
          if ( !@$system_schema->exec_query($stmt) ) 
          {
            $msg = "ko - " . $system_schema->error_msg();
          }
        }
      }
    }
}

return($msg);
}  /* Function ends */


/*

Rev : 
     20050724 - fm
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


function check_with_feedback()
{
$errors=0;	
$final_msg ='';

$msg_ko = "<span class='notok'>Failed!</span>";
$msg_ok = "<span class='ok'>OK!</span>";

$msg_check_dir_existence = "</b><br />Checking if <span class='mono'>PLACE_HOLDER</span> directory exists:<b> ";
$msg_check_dir_is_w = "</b><br />Checking if <span class='mono'>PLACE_HOLDER</span> directory is writable:<b> ";


$awtc = array('../gui/templates_c');


foreach ($awtc as $the_d) 
{
	
  $final_msg .= str_replace('PLACE_HOLDER',$the_d,$msg_check_dir_existence);
  
  if(!file_exists($the_d)) {
  	$errors += 1;
  	$final_msg .= $msg_ko; 
  } 
  else 
  {
  	$final_msg .= $msg_ok;
    $final_msg .= str_replace('PLACE_HOLDER',$the_d,$msg_check_dir_is_w);
  	if(!is_writable($the_d)) 
    {
    	$errors += 1;
  	  $final_msg .= $msg_ko;  
  	}
    else
    {
  	  $final_msg .= $msg_ok;  
    }
   }

}


$ret = array ('errors' => $errors,
              'msg' => $final_msg);
              
return($ret);

}  //function end



// 
/*
            <td align="left"><?php echo (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN') ? '<b class="ok">'.$okImg.'</b><span class="item"> ('.php_uname().')</span>' : '<b class="error">'.$failedImg.'</b><span class="warning">
            It seems you are using a proprietary operating system.  You might want to consider a Free Open Source operating system such as Linux.  dotProject is usually tested on Linux first and will always have better support for Linux than other operating systems.
            </span>';?></td>
*/
// 20050910 - fm
// added warning regarding possible problems between MySQL and PHP on windows systems
// due to MySQL password algorithm.
//
function check_php_version()
{
$min_ver = "4.1.0";
$ver_not_tested="5.0.0";

$errors=0;	
$check_title="Checking PHP version:";
$final_msg = "<p>{$check_title}<b> ";
$my_version = phpversion();

// version_compare:
// -1 if left is less, 0 if equal, +1 if left is higher
$php_ver_comp =  version_compare($my_version, $min_ver);
$check_not_tested = version_compare($my_version, $ver_not_tested);

if($php_ver_comp < 0) 
{
	$final_msg .= "<br><span class='notok'>Failed!</span> - You are running on PHP " . 
	        $my_version . ", and TestLink requires PHP " . $min_ver . " or greater";
	$errors += 1;
} 
else if($check_not_tested >= 0) 
{
  // 20051218 - fm - Just a Warning
  $final_msg .= "<br><span class='ok'>WARNING! You are running on PHP " . $my_version . 
                ", and TestLink has not been tested on versions >= " . $ver_not_tested . "</span>";
}
else 
{
	$final_msg .= "<span class='ok'>OK! (" . 
	              $min_ver . " <= " .$my_version . "[your version] < " . $ver_not_tested . " [not tested yet]  )</span>";
}





// 20050910 - fm
$os_id = strtoupper(substr(PHP_OS, 0, 3));
if( strcmp('WIN',$os_id) == 0 )
{
  $final_msg .= "<p><center><span class='notok'>" . 
  	            "Warning!: You are using a M$ Operating System, be careful with authentication problems <br>" .
  	            "          between PHP 4 and the new MySQL 4.1.x passwords<br>" . 
  	            'Read this <A href="./info/MySQL-RefManual-A.2.3.pdf">' .
  	            "MySQL - A.2.3. Client does not support authentication protocol</A>" .
  	            "</span></center><p>";
}

$ret = array ('errors' => $errors,
              'msg' => $final_msg);


return ($ret);
}  //function end





function check_mysql_version($conn=null)
{
$min_ver = "4.1.0";

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

// 20050824 - fm
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



function check_session()
{
$errors = 0;
$final_msg = "</b><br />Checking if sessions are properly configured:<b> ";

if($_SESSION['session_test']!=1 ) 
{
	$final_msg .=  "<span class='notok'>Failed!</span>";
	$errors += 1;
} 
else 
{
	$final_msg .= "<span class='ok'>OK!</span>";
}

$ret = array ('errors' => $errors,
              'msg' => $final_msg);


return ($ret);
}  //function end



/*
Explain What is Going To Happen 
*/
function ewigth($inst_type)
{

$msg = '';
if ($inst_type == "upgrade" )
{
	$many_warnings =  "<center><h1>Warning!!! Warning!!! Warning!!! Warning!!! Warning!!!</h1></center>";
	$msg ='';
  $msg .= $many_warnings; 

  $msg .= "<h1>You have requested an Upgrade, " .
          "this process WILL MODIFY your TestLink Database <br>" .
          "We STRONGLY recomend you to backup your Database Before starting this upgrade process"; 
  
  
  $msg .= "<br><br> Attention PLEASE:";
  $msg .= "<br> 1. The name/title of testcases, categories, ecc WILL BE TRUNCATED to 100 chars";
  $msg .= "<br> 2. Components and Categories present in Test Plans ";
  $msg .= "BUT NO MORE PRESENT IN PRODUCTS <br>WILL BE DELETED</h1>";
  $msg .= '<br>' . $many_warnings . "<br><br>"; 
  
        


}

return($msg);
}  //function end


// 20060214 - franciscom - added warning regarding valid database names
function db_msg($inst_type)
{

$msg = '';

$msg .=	"Please enter the name of the database you want to use for TestLink. <br>" .
				'<br><span class="notok">
				  Your attention please<br>' .
				"The database name can contain any character that is allowed in a directory name, except '/', '\', or '.'  
				  </span> <br><br>" .
				"If you haven't created a database yet, the installer will attempt to do so for you, <br>" . 
				"but this may fail depending on the MySQL setup your host uses.<br>";

if ($inst_type == "upgrade" )
{
  $msg =	"Please enter the name of the TestLink database you want to UPGRADE. <br>";
 
}

return($msg);
}  //function end


function tl_admin_msg($inst_type)
{

$msg = '';
$msg .= 'After installation You will have the following login for TestLink Administrator.<br />' .
        'login name: admin <br /> password  : admin <br />';

if ($inst_type == "upgrade" )
{
	$msg = '';
}


return($msg);
}  //function end



function check_php_settings()
{
$errors = 0;
$final_msg = "</b><br />Checking if Register Globals = OFF:<b> ";

if(ini_get('register_globals')) 
{
	$final_msg .=  "<span class='notok'>Failed! is ON - Please change the setting in your php.ini file</span>";
	$errors += 1;
} 
else 
{
	$final_msg .= "<span class='ok'>OK!</span>";
}

$ret = array ('errors' => $errors,
              'msg' => $final_msg);


return ($ret);
}  //function end


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
	$min_ver = "7";
  $db_verbose="PostGres";
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








?>
