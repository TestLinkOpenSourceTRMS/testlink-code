<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: configCheck.php,v ${file_name} $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2005/08/24 09:38:05 ${date} ${time} $ by $Author: havlat $
 *
 * @author Martin Havlat
 * 
 * Check configuration in login and index pages.
 *
 **/
// ---------------------------------------------------------------------------------------------------

/** check if we need to run the install program */
function checkConfiguration()
{
	clearstatcache();
	$file_to_check = "config_db.inc.php";

	if(!is_file($file_to_check))
	{
		echo "<html><body>Fatal Error. You haven't configured TestLink yet.<br/><a href='./install/index.php'>
			Click Here To Start Installation/Setup!</a></body></html>";
		exit();
	}

	// 20050823
	$installer_dir = "./install/";
	clearstatcache();
	if(is_dir($installer_dir))
	{
		echo "<html><body>TestLink Security Issue: <br> Please remove the install directory " . $installer_dir.
			"</body></html>";
		exit();
	}
}


?>
