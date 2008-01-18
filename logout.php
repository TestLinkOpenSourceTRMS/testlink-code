<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * Filename $RCSfile: logout.php,v $
 *
 * @version $Revision: 1.10 $
 * @modified $Date: 2008/01/18 21:33:20 $
**/
// Unset all of the session variables.
require_once('config.inc.php');
require_once('common.php');
$userID = null;
doDBConnect($db);
if(!isset($_SESSION))
{ 
	session_start();
	$userID = $_SESSION['userID'] ?  $_SESSION['userID'] : null;
}
tLog(TLS("audit_user_logout"),'AUDIT',null,$userID,"users");  
session_unset();
session_destroy();
?>
<html>
<head>
	<script type="text/javascript">
		top.location.href='login.php';
	</script>
</head>
<body>
</body>
</html>
