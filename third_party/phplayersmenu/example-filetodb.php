<pre>
<?php print basename(__FILE__); ?> - output the DB SQL dump corresponding to the Menu Structure

<?php
require_once 'lib/layersmenu-common.inc.php';
require_once 'lib/layersmenu-process.inc.php';
$mid = new ProcessLayersMenu();
$mid->setIconsize(16, 16);
$mid->setMenuStructureFile('layersmenu-horizontal-1.txt');
$mid->parseStructureForMenu('hormenu1');
//$mid->setDBConnParms('mysql://mysql:mysql@localhost/phplayersmenu');
//$mid->setDBConnParms('pgsql://postgres:postgres@localhost/phplayersmenu');
//$mid->setDBConnParms('sqlite:///DUMPS/phplayersmenu');
//$mid->scanTableForMenu('hormenu1');
//print $mid->getSQLDump('hormenu1', 'sqlite');	// PHP 5 only
print $mid->getSQLDump('hormenu1');
?>

</pre>
