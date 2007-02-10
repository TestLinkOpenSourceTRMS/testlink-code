<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * Filename $RCSfile: logout.php,v $
 *
 * @version $Revision: 1.7 $
 * @modified $Date: 2007/02/10 12:15:42 $
**/
// Unset all of the session variables.
session_start();
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
