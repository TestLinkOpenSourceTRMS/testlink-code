<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource: keywordsView.php
 *
 * Display list of available keywords.
 */
require_once '../../config.inc.php';
require_once 'common.php';
require_once 'keywordsEnv.php';

testlinkInitPage($db);
$templateCfg = templateConfiguration();
$gui = $args = initArgs($db);

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/**
 * Get input from user and return it in some sort of namespace
 *
 * @param database $dbHandler
 * @return stdClass object returns the arguments for the page
 */
function initArgs(&$dbHandler)
{
    $tproject_id = isset($_REQUEST['tproject_id']) ? $_REQUEST['tproject_id'] : 0;
    $tproject_id = intval($tproject_id);

    if ($tproject_id <= 0) {
        throw new Exception("Error Invalid Test Project ID", 1);
    }

    // Check rights before doing anything else
    // Abort if rights are not enough
    $user = $_SESSION['currentUser'];
    $env['tproject_id'] = $tproject_id;
    $env['tplan_id'] = 0;

    $check = new stdClass();
    $check->items = array(
        'mgt_view_key'
    );
    $check->mode = 'and';
    checkAccess($dbHandler, $user, $env, $check);

    // OK, go ahead
    $args = getKeywordsEnv($dbHandler, $user, $tproject_id);
    $args->tproject_id = $tproject_id;

    $args->dialogName = '';
    $args->bodyOnLoad = $args->bodyOnUnload = '';
    if (isset($_REQUEST['openByKWInc'])) {
        $args->openByOther = 1;
    } else {
        // Probably useless
        $args->openByOther = isset($_REQUEST['openByOther']) ? intval(
            $_REQUEST['openByOther']) : 0;
        if ($args->openByOther !== 0) {
            $args->dialogName = 'kw_dialog';
            $args->bodyOnLoad = "dialog_onLoad($args->dialogName)";
            $args->bodyOnUnload = "dialog_onUnload($args->dialogName)";
        }
    }

    return $args;
}
