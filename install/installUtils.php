<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: installUtils.php,v 1.2 2005/08/16 17:59:48 franciscom Exp $ */
// Code extracted from several places:

// +----------------------------------------------------------------------+
// From PHP Manual - User's Notes
// +----------------------------------------------------------------------+
//
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
// @(#) $Id: installUtils.php,v 1.2 2005/08/16 17:59:48 franciscom Exp $
//


function getDatabaseList($conn)
{
    $dbs = array();

    $db_list = mysql_list_dbs($conn);
    while ($row = mysql_fetch_array($db_list)) {
        $dbs[] = $row['Database'];
    }
    return $dbs;
}

function getTableList($conn)
{
    $tables = array();

    $res = @mysql_query('SHOW TABLES', $conn);
    // echo mysql_errno();
    while ($row = @mysql_fetch_row($res)) {
        $tables[] = $row[0];
    }
    return $tables;
}

function getUserList($conn)
{
    @mysql_select_db('mysql');
    $res = @mysql_query('SELECT DISTINCT User from user');
    $users = array();
    // if the user cannot select from the mysql.user table, then return an empty list
    if (!$res) {
        return $users;
    }
    while ($row = mysql_fetch_row($res)) {
        $users[] = $row[0];
    }
    return $users;
}


/*
Function: dbExists (DataBase Exists)

args :	$db_name: database to test for existence 
				$conn   : valid db connection handler
       

returns: 1 -> db exits
         0 ->   
*/
function dbExists($db_name,$conn)
{
    $db_list = getDatabaseList($conn);
    $db_list = array_map('strtolower', $db_list);
    $db_name_lc = strtolower($db_name);
    $ret_val = 1;
    if ( !in_array($db_name, $db_list)) 
    {
      $ret_val = 0;
    } 
    return $ret_val;
}


/*
Function: create_user_for_db
          
          Check for user existence.
          
          If doesn't exist
             Creates a user/passwd with the following GRANTS: SELECT, UPDATE, DELETE, INSERT
             for the database 
          Else
             do nothing
                
*/
function create_user_for_db($conn, $db, $login, $passwd, $db_server='localhost')
{

$user_list = getUserList($conn);
$login_lc = strtolower($login);
$msg = "ko - fatal error - can't get db server user list !!!";

if (count($user_list) > 0) 
{
	  $msg = 'ok - user_exists';
    $user_list = array_map('strtolower', $user_list);
    if (!in_array($login_lc, $user_list)) 
    {
    	$msg = 'ok - new user';
      $stmt = "GRANT SELECT, UPDATE, DELETE, INSERT ON " . 
              $db . ".* TO '" . $login . "'" . "@" . $db_server . 
              " IDENTIFIED BY '" .  $passwd . "'";
      if (!@mysql_query($stmt, $conn)) 
      {
          $msg = "ko - " . mysql_error();
      }
    }
    
}

return $msg;
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






?>
