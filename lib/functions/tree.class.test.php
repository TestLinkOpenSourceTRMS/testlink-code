<?php
  /**
   *  20060615 - kevinlevy - test class which displays function calls
   *  and results into tree.class.php
   */

require_once('../../config.inc.php');
require_once('common.php');
require_once('tree.class.php');

print "<h3>/lib/functions/tree.class.test.php</h3>";
print "author : Kevin Levy <BR>";
print "last updated 20060630 <BR>";
print "<BR>This page displays the functions in /lib/functions/tree.class.php and examples of their usage.  This page will first call an initialization method, then the testplan class will be instantiated, then we will retrieve the current testplan and testproject ids.  Once this initial information has been gathered, each method of the testplan class will be used and we will inspect the results.<BR>";

print "============================================== <BR> ";

print "<h3>MUST BE DONE 1st : initialize the page and \$db reference</h3>";
print "testlinkInitPage(\$db) <BR>";
testlinkInitPage($db);

print "============================================== <BR> ";
print "<h3>Instantiate the testplan object using the \$db reference</h3>";
print "\$tp = new testplan(\$db)<BR>";
$tp = new testplan($db);

print "============================================== <BR> ";

print "<h3>Many of the values used by the methods can be retrieve from \$_SESSION</h3>";
print "contents of the \$_SESSION object : <BR>";
print_r($_SESSION);
print "<BR>";

print "============================================== <BR>";

?>
