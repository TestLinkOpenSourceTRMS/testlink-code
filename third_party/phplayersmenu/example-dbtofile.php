<pre>
<?php print basename(__FILE__); ?> - output the Menu Structure Format obtained querying the DB

<?php
require_once 'PEAR.php';
require_once 'DB.php';
require_once 'lib/layersmenu-common.inc.php';
require_once 'lib/layersmenu-process.inc.php';
$mid = new ProcessLayersMenu();
$mid->setIconsize(16, 16);
//$mid->setMenuStructureFile('layersmenu-horizontal-1.txt');
//$mid->parseStructureForMenu('hormenu1');
$mid->setDBConnParms('mysql://mysql:mysql@localhost/phplayersmenu');
//$mid->setDBConnParms('pgsql://postgres:postgres@localhost/phplayersmenu');
//$mid->setDBConnParms('sqlite:///DUMPS/phplayersmenu');
$mid->scanTableForMenu('hormenu1', 'it');
//$mid->scanTableForMenu('hormenu1');
print $mid->getMenuStructure('hormenu1');
?>
</pre>
