<?php 
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: newInstallStart_TL.php,v 1.2 2005/08/16 17:59:48 franciscom Exp $ */

session_start(); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" 
  "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>TestLink Installer</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <style type="text/css">
             @import url('./css/style.css');
        </style>
</head>	

<?php
$inst_type = $_GET['installationType'];

$main_title = 'TestLink Setup';
$explain_msg = '<p>' . $main_title . 
               'has carried out a number of checks ' .
               "to see if everything's ready to start the setup. </br>";

$the_msg = '<p><b>' . $main_title . '</b></p>' . $explain_msg;



?>

<body>
<table border="0" cellpadding="0" cellspacing="0" class="mainTable">
  <tr class="fancyRow">
    <td><span class="headers">&nbsp;<img src="./img/dot.gif" alt="" style="margin-top: 1px;" />&nbsp;TestLink</span></td>
    <td align="right"><span class="headers">Installation - <?php echo $inst_type; ?> </span></td>
  </tr>
  <tr class="fancyRow2">
    <td colspan="2" class="border-top-bottom smallText" align="right">&nbsp;</td>
  </tr>
  <tr align="left" valign="top">
    <td colspan="2"><table width="100%"  border="0" cellspacing="0" cellpadding="1">
      <tr align="left" valign="top">
        <td class="pad" id="content" colspan="2">

<?php
echo $the_msg;

$errors = 0;

$check = check_php_version();
$errors += $check['errors'];
echo $check['msg'];

$check = check_mysql_version();
$errors += $check['errors'];
echo $check['msg'];

$check = check_session();
$errors += $check['errors'];
echo $check['msg'];

$check = check_with_feedback();
echo $check['msg'];
$errors += $check['errors'];

?>



<?php
if($errors>0) {
?>
<br />
<br />
Unfortunately, TestLink setup cannot continue at the moment, due to the above <?php echo $errors > 1 ? $errors." " : "" ; ?>error<?php echo $errors > 1 ? "s" : "" ; ?>. Please correct the error<?php echo $errors > 1 ? "s" : "" ; ?>, and try again. If you need help figuring out how to fix the problem<?php echo $errors > 1 ? "s" : "" ; ?>, please read the documentation in the <a href="http://www.cookiebean.com/wiki" target="_blank">TestLink Wiki</a>, or visit the <a href="http://www.cookiebean.com/forums" target="_blank">TestLink Forums</a>.
<br />
			</p>
		</td>
      </tr>
    </table></td>
  </tr>
  <tr class="fancyRow2">
    <td class="border-top-bottom smallText">&nbsp;</td>
    <td class="border-top-bottom smallText" align="right">&nbsp;</td>
  </tr>
</table>
</body>
</html>
<?php
exit;
}
?>
<br />
<br />
		<script language="JavaScript">
		function validate() {
			var f = document.myForm;
			if(f.databasename.value=="") {
				alert('You need to enter a value for database name!');
				return false;
			}
			if(f.databasehost.value=="") {
				alert('You need to enter a value for database host!');
				return false;
			}
			if(f.databaseloginname.value=="") {
				alert('You need to enter your database login name (with Administrative Rights)!');
				return false;
			}
			
			if(f.tl_loginname.value=="") {
				alert('You need to enter your TestLink database login name (For Normal TestLink Operation)!');
				return false;
			}

			if(f.tl_loginpassword.value=="") {
				alert('You need to enter your TestLink database password (For Security empty password is not allowed)!');
				return false;
			}

			
			/*
			if(f.cmsadmin.value=="") {
				alert('You need to enter a username for the TestLink admin account!');
				return false;
			}
			if(f.cmspassword.value=="") {
				alert('You need to a password for the TestLink admin account!');
				return false;
			}
			if(f.cmspassword.value!=f.cmspasswordconfirm.value) {
				alert('The administrator password and the confirmation don\'t match!');
				return false;
			}
			*/
			return true;
		}
		</script>
				<form action="license.php" method="post" name="myForm" onsubmit="return validate()">

         <input type="hidden" name="installationType" value="<?php  echo $inst_type; ?>">
         <?php 
          echo ewigth($inst_type); 
         ?>

					
					
					Database Configuration <p />
					
					<div class="labelHolder">
						<label for="databasehost">Database host:</label>
					</div>
					<input type="text" id="databasehost" name="databasehost" value="localhost" style="width:200px" /><br />
					
				<p>
         <?php echo db_msg($inst_type); ?>
				</p>
				<p>
					<div class="labelHolder"><label for="databasename">Database name:</label></div><input type="text" id="databasename" name="databasename" style="width:200px" value="TestLink"><br />
					<!--
					20050611 - fm
					<div class="labelHolder"><label for="tableprefix">Table prefix:</label></div><input type="text" id="tableprefix" name="tableprefix" style="width:200px" value="TestLink_">
					-->
				</p>

				<p>
					Database User with administrative rights.
				</p>
				
				<p class="smallText">
				This user requires permission to create databases on the Database Server.<br>
        This value is used only for these installation procedures, and is not saved.
			  </p>
			
				<p>
					
					<div class="labelHolder"><label for="databaseloginname">Database login:</label></div><input type="text" id="databaseloginname" name="databaseloginname" style="width:200px" /><br />
					<div class="labelHolder"><label for="databaseloginpassword">Database password:</label></div><input type="password" id="databaseloginpassword" name="databaseloginpassword" style="width:200px" /><br />
				</p>

				<p>
					Database User for Normal Testlink use.
				</p>
        <p class="smallText">
				This user will have permission only to work on TestLink database.<br>
        All connections to the Database Server during normal operation will be done with this user.
			  </p>
			


				<p>
					<div class="labelHolder">
						<label for="tl_loginname">TestLink DB login:</label>
					</div>
					<input type="text" id="tl_loginname" name="tl_loginname" style="width:200px" /><br />
					
					<div class="labelHolder">
						<label for="tl_loginpassword">TestLink DB password:</label>
					</div>
					<input type="password" id="tl_loginpassword" name="tl_loginpassword" style="width:200px" /><br />
				</p>
				
				<p> &nbsp;</p>
				<p>
					<?php echo tl_admin_msg($inst_type); ?>
				</p>
				
				<!--
				<p>
					Now you'll need to enter some details for the main TestLink administrator account.<br />
					You can fill in your own name here, and a password you're not likely to forget. <br />
					You'll need these to log into TestLink once setup is complete.
				</p>
				<p>
					<div class="labelHolder"><label for="cmsadmin">Administrator username:</label></div><input type="text" id="cmsadmin" name="cmsadmin" style="width:200px" value="admin" /><br />
					<div class="labelHolder"><label for="cmspassword">Administrator password:</label></div><input type="password" id="cmspassword" name="cmspassword" style="width:200px" value="" /><br />
					<div class="labelHolder"><label for="cmspasswordconfirm">Confirm password:</label></div><input type="password" id="cmspasswordconfirm" name="cmspasswordconfirm" style="width:200px" value="" /><br />
				</p>
        -->
        
				<p>
					<input type="submit" value="Setup TestLink!">
				</p>
				
			</form>	

			</p>
		</td>
      </tr>
    </table></td>
  </tr>
  <tr class="fancyRow2">
    <td class="border-top-bottom smallText">&nbsp;</td>
    <td class="border-top-bottom smallText" align="right">&nbsp;</td>
  </tr>
</table>
</body>
</html>


<?php
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
function check_php_version()
{
$min_ver = "4.1.0";

$errors=0;	
$final_msg = "<br />Checking PHP version:<b> ";
$my_version = phpversion();
// version_compare:
// -1 if left is less, 0 if equal, +1 if left is higher
$php_ver_comp =  version_compare($my_version, $min_ver);

if($php_ver_comp < 0) 
{
	$final_msg .= "<span class='notok'>Failed!</span> - You are running on PHP " . 
	        $my_version . ", and TestLink requires PHP " . $min_ver . " or greater";
	$errors += 1;
} 
else 
{
	$final_msg .= "<span class='ok'>OK! (" . $my_version . " >= " . $min_ver . ")</span>";
}

$ret = array ('errors' => $errors,
              'msg' => $final_msg);


return ($ret);
}  //function end



function check_mysql_version()
{
$min_ver = "4.0.0";

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

$my_version = @mysql_get_server_info();

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
  
  
  $msg .= "<br><br> During the Upgrade the name of testcases, categories, ecc WILL BE TRUNCATED to 100 chars</h1>";
  $msg .= '<br>' . $many_warnings . "<br><br>"; 
        


}

return($msg);
}  //function end


function db_msg($inst_type)
{

$msg = '';

$msg .=	"Please enter the name of the database you want to use for TestLink. <br>" .
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



?>


