<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	sysinfo.php
 * @author		Martin Havlat 
 * 
 * Report about system and background services
 * 
 * @todo check if custom_config, config_db are writable and remove/rewrite commented part of code
 * @todo test database (if installed only)
 *
 * @internal revisions
 * @since 1.9.4
 */

require_once('..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.inc.php');
require_once('..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'lib' . 
			 DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR . 'common.php');
$root = dirname(__FILE__);
define('ROOT_PATH', $root);


function chk_memory($limit=9, $recommended=16) {

	$msg = '';
	$type = '';

	$max_memory = ini_get('memory_limit');

	if ($max_memory == "") {

		$msg = "OK (No Limit)";
		$type = "done";

	} else if ($max_memory === "-1") {

		$msg = "OK (Unlimited)";
		$type = "done";

	} else {

		$max_memory = rtrim($max_memory, "M");
		$max_memory_int = (int) $max_memory;

		if ($max_memory_int < $limit) {

			$msg = "Warning at least $limit M required ($max_memory M available, Recommended $recommended M)";
			$type = "error";

		} elseif ($max_memory_int < $recommended) {

				$msg = "OK (Recommended $recommended M)";
				$type = "pending";

		} else {
				$msg = "OK";
				$type = "done";
		}

	}

	$msg = "<b class='$type'>".$msg."</b>";

return $msg;
}
?>
<!DOCTYPE HTML>
<html>
<head>
<title>TestLink - System Information</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="../../gui/themes/default/images/favicon.ico" rel="icon" type="image/gif"/>
<link href="../../gui/themes/default/css/testlink.css" rel="stylesheet" type="text/css" />
<script language="javascript">
function reload() {
	window.location.reload(true);
}
</script>
</head>

<body>
<div style="width: 800px; margin-left: auto; margin-right: auto;">
<h1>TestLink - System & services checking</h1>
<p>Installation status:
<?php
if (checkInstallStatus())
{
	echo "Installed.";
}
else
{
	echo "Not installed.";
}	
?> 
</p>
<div>
	<input type="button" name="Re-check" value="Re-check" onClick="reload();" tabindex="1">
</div>
<div>
<?php
$errors = 0;

reportCheckingSystem($errors);
reportCheckingWeb($errors);
reportCheckingPermissions($errors);
reportCheckingDatabase($errors);
reportCheckingBrowser($errors);


echo '<p>Error counter = '.$errors.'</p>';
?>
<hr /></div>


<div id="footer">
	<p><a href="http://www.testlink.org" target="_blank" tabindex="99">TestLink</a> System check is finished. </p>
</div>

</div>
</body>
</html>