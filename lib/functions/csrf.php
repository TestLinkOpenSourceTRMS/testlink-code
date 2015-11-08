<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * functions related to CSRF security.
 *
 * The original can be found on the link below.
 *
 * https://www.owasp.org/index.php/PHP_CSRF_Guard
 * CSRF - Advisory ID: HTB23088
 *
 *
 * @package TestLink
 * @author TestLink Community
 * @copyright 2012,2015 TestLink community
 * @link http://www.testlink.org
 *
 * @internal revisions
 * @since 1.9.15
 **/

// ATTENTION/CRITIC
// Go to file bottom to see code executed on file include


/**
 * Stores key/value in session.
 *
 * @return true if item was stored in session, otherwise false
 */
function store_in_session($key,$value)
{
  if (isset($_SESSION))
  {
    $_SESSION[$key]=$value;
    return true;
  }
  return false;
}

/**
 * Removes a key from session.
 *
 * @param unknown_type $key
 * @return true if item was removed, otherwise false
 */
function unset_session($key)
{
  if (isset($_SESSION))
  {
    $_SESSION[$key]= null;
    unset($_SESSION[$key]);
    return true;
  }
  return false;
}

/**
 * Gets a value from session by its key. If the session cannot be found, it 
 * return false.
 *
 * @param unknown_type $key
 * @return unknown|boolean
 */
function get_from_session($key)
{
  // if there no session data, no CSRF risk
  return isset($_SESSION) ? $_SESSION[$key] : false;
}

/**
 * Generates a CSRF token for a unique form name
 *
 * @param unknown_type $unique_form_name unique form name
 * @return CSRF token
 */
function csrfguard_generate_token($unique_form_name)
{
  if (function_exists("hash_algos") and in_array("sha512",hash_algos()))
  {
    $token=hash("sha512",mt_rand(0,mt_getrandmax()));
  }
  else
  {
    $token= null;
    for ($idx=0;$idx<128;++$idx)
    {
      $r=mt_rand(0,35);
      if ($r<26)
      {
        $c=chr(ord('a')+$r);
      }
      else
      {
        $c=chr(ord('0')+$r-26);
      }
      $token.=$c;
    }
  }
  store_in_session($unique_form_name,$token);
  return $token;
}

/**
 * Validates a CSRF token, given a unique form name.
 *
 * @param unknown_type $unique_form_name unique form name
 * @param unknown_type $token_value value of the CSRF token
 * @return true if token is valid, otherwise false
 */
function csrfguard_validate_token($unique_form_name,$token_value)
{
  $token=get_from_session($unique_form_name);
  if ($token===false)
  {
    return true;
  }
  elseif ($token==$token_value)
  {
    $result=true;
  }
  else
  {
    $result=false;
  }
  unset_session($unique_form_name);
  return $result;
}

/**
 * Replaces via regex the content of a HTML form, adding extra hidden fields 
 * for CSRF security.
 * <p>
 * In case you would like to skip this, you can add a <em>nocsrf</em> field.
 *
 * @param unknown_type $form_data_html HTML content
 * @return html content with modified forms that include CSRF hidden tokens
 */
function csrfguard_replace_forms($form_data_html)
{
  $count=preg_match_all("/<form(.*?)>(.*?)<\\/form>/is",$form_data_html,$matches,PREG_SET_ORDER);
  if (is_array($matches))
  {
    foreach ($matches as $m)
    {
      if (strpos($m[1],"nocsrf")!==false) 
      {
        continue;
      }
      $name="CSRFGuard_".mt_rand(0,mt_getrandmax());
      $token= csrfguard_generate_token($name);
      $form_data_html=str_replace($m[0],
                      "<form{$m[1]}>
                       <input type='hidden' name='CSRFName' id='CSRFName' value='{$name}' />
                       <input type='hidden' name='CSRFToken' id='CSRFToken' value='{$token}' />{$m[2]}</form>",$form_data_html);
    }
  }
  return $form_data_html;
}

/**
 * Applies CSRF filter on Smarty template content. Can be 
 * used as a output filter.
 *
 * @param string $source
 * @param Smarty $smarty
 * @return CSRF filtered content
 */
function smarty_csrf_filter($source, &$smarty) 
{
  return csrfguard_replace_forms($source);
}

/**
 * Validates the CSRF tokens found in $_POST variable. Raoses user 
 * errors if the token is not found or invalid.
 *
 * @return true if validated correctly, otherwise false
 */
function csrfguard_start()
{
  if (count($_POST))
  {
    if (!isset($_POST['CSRFName']))
    {
      //trigger_error("No CSRFName found, probable invalid request.",E_USER_ERROR);
      //return false;
      redirect($_SESSION['basehref'] . 'error.php?message=No CSRFName found, probable invalid request.');
      exit();
    }

    // 20151107 
    $name = trim($_POST['CSRFName']);
    $token = trim($_POST['CSRFToken']);
    $good = (strlen($name) > 0 && strlen($token) > 0);

    if (!$good || !csrfguard_validate_token($name, $token))
    {
      //trigger_error("Invalid CSRF token.",E_USER_ERROR);
      //return false;
      redirect($_SESSION['basehref'] . 'error.php?message=Invalid CSRF token.');
      exit();
    }
  }
}

// this way is runned always
// Need to understand if this is needed
//  
doSessionStart(false);
// csrfguard_start();