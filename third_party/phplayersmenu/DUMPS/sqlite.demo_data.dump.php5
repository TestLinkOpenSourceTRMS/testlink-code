<pre>
<?php
$file = 'sqlite.demo_data.dump';
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
print "Executing queries...\n";
$dbresult = $db->query('BEGIN TRANSACTION');
while ($buffer = fgets($fd, 4096)) {
	if (strlen($buffer) < 5) {	// it's surely an empty line
		continue;
	}
	$buffer = ereg_replace(chr(13), '', $buffer);	// Microsoft Stupidity Suppression
	$dbresult = $db->query($buffer);
	if (DB::isError($dbresult)) {
		die('Error executing query: ' . $dbresult->getMessage());
	}
}
$dbresult = $db->query('END TRANSACTION');
fclose($fd);
print "... done\n[OK]\n";
?>
</pre>
