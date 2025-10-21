<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * Filename $RCSfile: show_help.php,v $
 *
 * @version $Revision: 1.6 $
 * @modified $Date: 2009/05/09 17:59:19 $  $Author: schlundus $
 *
 * manage launch of help pages.
 *
 * rev:
 *     20071102 - franciscom - BUGID 1033
 **/
require_once '../../config.inc.php';
require_once 'common.php';
// start session, need to get right basehref
testlinkInitPage($db);

$args = initArgs();

$smarty = new TLSmarty();
// @TODO security hole, directory traversal possible
$td = TL_ABS_PATH . TL_HELP_RPATH . $args->locale;
$smarty->template_dir = $td;

$smarty->clear_compiled_tpl($args->help . ".html");
$smarty->display($args->help . ".html");

/**
 * Initializes the arguments
 *
 * @return stdClass
 */
function initArgs()
{
    $iParams = array(
        "help" => array(
            tlInputParameter::STRING_N
        ),
        "locale" => array(
            tlInputParameter::STRING_N,
            0,
            10
        )
    );
    $args = new stdClass();
    R_PARAMS($iParams, $args);

    return $args;
}
?>
