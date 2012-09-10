<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  configCheck.test.php
 * @author Francisco Mancardi
 *
 *
 */
require_once('../../../config.inc.php');
require_once('common.php');
require_once('configCheck.test.php');

// needed to have db
testlinkInitPage($db);

echo "<h1>configCheck.php - poor's man Unit Test</h1>";
echo 'get_home_url() => ' . get_home_url() . '<br>';

$tlCfg->config_check_warning_mode = 'SCREEN';
$tlCfg->attachments->repository->type = TL_REPOSITORY_TYPE_FS;
$tlCfg->attachments->repository->path = '/var/ff';
echo 'getSecurityNotes() => ';
echo '<pre>';
var_dump(getSecurityNotes($db));
echo '</pre>';
?>