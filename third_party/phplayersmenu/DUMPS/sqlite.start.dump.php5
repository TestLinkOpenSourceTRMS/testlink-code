<pre>
<?php
$file = 'sqlite.start.dump';
print "Importing $file...\n";
require_once 'PEAR.php';
require_once 'DB.php';
print "... connecting... ";
$db = DB::connect('sqlite:///phplayersmenu');
if (DB::isError($db)) {
	die('Connection error: ' . $db->getMessage());
}
print "connected\n[OK]\n\n";

if (!($fd = fopen($file, 'r'))) {
	die("Unable to open file $file");
}
$table_name = array('phplayersmenu', 'phplayersmenu_i18n');
$dbresult = $db->query('BEGIN TRANSACTION');
for ($i=0; $i<2; $i++) {
	print "Creating table '" . $table_name[$i] . "'...\n";
	while ($buffer = fgets($fd, 4096)) {
		$buffer = ereg_replace(chr(13), '', $buffer);	// Microsoft Stupidity Suppression
		if (strlen($buffer) > 1) {	// skip empty lines
			break;
		}
	}
	$query = $buffer;
	while ($buffer = fgets($fd, 4096)) {
		$buffer = ereg_replace(chr(13), '', $buffer);	// Microsoft Stupidity Suppression
		if (strlen($buffer) > 1) {	// if it is an empty line, then the 'CREATE TABLE' command has ended
			$query .= $buffer;
		} else {
			break;
		}
	}
	$dbresult = $db->query($query);
	if (DB::isError($dbresult)) {
		die("Error creating table '" . $table_name[$i] . "': " . $dbresult->getMessage());
	} else {
		print $query . "... created\n[OK]\n\n";
	}
}
$dbresult = $db->query('END TRANSACTION');
?>
</pre>
