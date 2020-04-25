<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * General purpose error page.
 *
 * @package 	TestLink
 * @copyright 2012,2019 TestLink community
 *
 * @internal revisions
 * @used by: kinow - TICKET 4977 - CSRF - Advisory ID: HTB23088
 *
 **/

require_once('config.inc.php');
require_once('common.php');

/**
 *
 */
function init_args() {

  $args = new stdClass();
  $args->message = 'Rocket Raccoon is watching You';
  $code = isset($_REQUEST['code']) ? $_REQUEST['code'] : 0;

  switch($code) {
    case 1:
      $args->message = 'No CSRFName found, probable invalid request.';
    break;

    case 2:
      $args->message = 'Invalid CSRF token';
    break;

    default:
    break;    
  } 
  
  return $args;
}

/**
 *
 */
function init_gui($args) {
  $gui = new stdClass();
  $gui->message = '';
    
  if (isset($args->message)) {
    $gui->message = $args->message;
  }
    
  return $gui;
}

$templateCfg = templateConfiguration();
$args = init_args();
$gui = init_gui($args);

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->default_template);