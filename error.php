<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * General purpose error page.
 *
 * @package 	TestLink
 * @copyright 	2012 TestLink community
 *
 * @internal revisions
 * @since 1.9.4
 *  20120703 - kinow - TICKET 4977 - CSRF - Advisory ID: HTB23088
 *
 **/

require_once('config.inc.php');
require_once('common.php');

function init_args()
{
    $iParams = array(
        'message' => array(tlInputParameter::STRING_N,0,255)
    );
    $pParams = R_PARAMS($iParams);
    $args = new stdClass();
    if(isset($pParams['message'])) {
        $args->message = $pParams['message'];
    }
    return $args;
}

function init_gui($args)
{
    $gui = new stdClass();
    if(isset($args->message))
    {
        $gui->message = $args->message;
    } else {
        $gui->message = '';
    }

    return $gui;
}

$templateCfg = templateConfiguration();
$args = init_args();
$gui = init_gui($args);

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->default_template);

?>
