<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * Filename $RCSfile: logout.php,v $
 *
 * @version $Revision: 1.4 $
 * @modified $Date: 2005/08/31 11:35:11 $
 *
 * @author Martin Havlat
 *
 * 
 * 20050831 - scs - cosmetic changes
**/
// Unset all of the session variables.
session_start();
session_unset();
session_destroy();
?>
<html>
<head>
	<script type="text/javascript">
		location.href='login.php';
	</script>
</head>
<body>
</body>
</html>
