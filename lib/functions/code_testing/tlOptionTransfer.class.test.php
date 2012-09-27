<?php
require_once('../../../config.inc.php');
require_once('common.php');

$objItem="GUI HTML Option Transfer";
$objClass="tlOptionTransfer";

echo "<pre>Poor's Man - $objItem - code inspection tool<br>";
echo "<pre>Scope of this page is allow you to understand with live<br>";
echo "examples how to use object: $objItem (implemented in file $objClass.class.php)<br>";

$otObj = new $objClass();
new dBug($otObj);
new dBug($otObj->getCfg());
?>