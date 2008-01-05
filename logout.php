<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * Filename $RCSfile: logout.php,v $
 *
 * @version $Revision: 1.9 $
 * @modified $Date: 2008/01/05 22:00:51 $
**/
// Unset all of the session variables.
if(!isset($_SESSION))
{ 
  session_start();
}
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
