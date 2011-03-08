<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource $RCSfile: sysinfo.php,v $
 * @version $Revision: 1.4.6.1 $
 * @modified $Date: 2010/11/20 08:22:39 $ by $Author: franciscom $
 *
 * @author	Martin Havlat 
 * 
 * Report about system and background services
 * 
 * @todo check if custom_config, config_db are writable and remove/rewrite commented part of code
 * @todo test database (if installed only)
 *
 */

require_once('config.inc.php');

$root = dirname(__FILE__);
define('ROOT_PATH', $root);

function check_php_version2($sys_php_version = '') {

	$sys_php_version = empty($sys_php_version) ? constant('PHP_VERSION') : $sys_php_version;
	// versions below $min_considered_php_version considered invalid by default,
	// versions equal to or above this ver will be considered depending
	// on the rules that follow
	$min_considered_php_version = '5.2.0';

	// only the supported versions,
	// should be mutually exclusive with $invalid_php_versions
	$supported_php_versions = array (
		'5.0.1', '5.0.2', '5.0.3', '5.0.4',
		'5.1.0', '5.1.1', '5.1.2', '5.1.3',
		'5.1.4', '5.1.5', '5.1.6', '5.1.7',
		'5.2.0', '5.2.1', '5.2.2'
	);

	sort($supported_php_versions);

	// invalid versions above the $min_considered_php_version,
	// should be mutually exclusive with $supported_php_versions
	$invalid_php_versions = array('5.0.0', '5.0.5');

	// default unsupported
	$retval = 0;

	// versions below $min_considered_php_version are invalid
	if(1 == version_compare($sys_php_version, $min_considered_php_version, '<')) {
		$retval = -1;
	}

	// supported version check overrides default unsupported
	foreach($supported_php_versions as $ver) {
		if(1 == version_compare($sys_php_version, $ver, 'eq')) {
			$retval = 1;
			break;
		}
	}

	if (($retval != 1) && (1 == version_compare($sys_php_version, $ver, '>'))) {
		$retval = 1;
	}

	// invalid version check overrides default unsupported
	foreach($invalid_php_versions as $ver) {
		if(1 == version_compare($sys_php_version, $ver, 'eq')) {
			$retval = -1;
			break;
		}
	}

	return $retval;
}

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

$ohrmVersion = "2.4.1";

if (@include_once ROOT_PATH."/lib/confs/Conf.php-distribution") {
	$conf = new Conf();
	$ohrmVersion = $conf->version;
}


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>TestLink - System Information</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="gui/themes/default/images/favicon.ico" rel="icon" type="image/gif"/>
<link href="gui/themes/default/css/testlink.css" rel="stylesheet" type="text/css" />
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
$bInstallDone = checkInstallStatus();
if ($bInstallDone)
	echo "Installed.";
else
	echo "Not installed.";
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
	<p><a href="http://www.teamst.org" target="_blank" tabindex="99">TestLink</a> System check is finished. </p>
</div>

</div>
</body>
</html>
