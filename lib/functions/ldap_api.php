<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  ldap_api.php
 * 
 * @author This piece of software has been copied and adapted from:
 * @author franciscom (code adaptation)
 *
 * LDAP API (authentication)
 *
 *
 *
 */
use LdapRecord\Connection;
use LdapRecord\Query\Filter\Parser;

function ldap_connect_bind($authCfg, $p_binddn = '', $p_password = '') 
{
  $ldapBindOp = new stdClass();
  $ldapBindOp->status = LDAP_BIND_OK;
  $ldapBindOp->handler = null;
  $ldapBindOp->info  = 'LDAP CONNECT OK';

  $username = ($p_binddn == '' ? $authCfg['ldap_bind_dn'] : $p_binddn);
  $password = ($p_password == '' ? $authCfg['ldap_bind_passwd'] : $p_password);  
  $connCfg = [
    'hosts' => [$authCfg['ldap_server']],
    'port' => (int)$authCfg['ldap_port'],
    'base_dn' => $authCfg['ldap_root_dn'],
    'username' => $username,
    'password' => $password
  ];
  $connOK = false;
  try {
    $ldapBindOp->handler = new Connection($connCfg);
    $ldapBindOp->handler->connect();
    $connOK = true; 
  } catch (\LdapRecord\Auth\BindException $e) {
    $error = $e->getDetailedError();
    $ldapBindOp->status = ERROR_LDAP_BIND_FAILED;
    $ldapBindOp->info = 'Error Code:' . $error->getErrorCode() .
                        ' / Error Message:' . $error->getErrorMessage() . 
                        ' / Diagnostic Message:' . (string) $error->getDiagnosticMessage();
  }
  return $ldapBindOp;
}


// ----------------------------------------------------------------------------
// Attempt to authenticate the user against the LDAP directory
function ldap_authenticate( $p_login_name, $p_password ) 
{
  $t_authenticated = new stdClass();
  $t_authenticated->status_ok = false;
  $t_authenticated->status_code = null;
  $t_authenticated->status_verbose = '';
  $t_authenticated->ldap_index = -1;

  # if password is empty and ldap allows anonymous login, then
  # the user will be able to login, hence, we need to check
  # for this special case.
  if ( is_blank( $p_password ) ) 
  {
    return $t_authenticated;
  }

  // Go ahead
  $t_username = $p_login_name;
  $authCfg = config_get('authentication');
  foreach($authCfg['ldap'] as $server_idx => $ldapCfg) {

    $t_authenticated->ldap_index = $server_idx;
    $ldapConnect = ldap_connect_bind($ldapCfg);
    $connOK = true;

    if ($ldapConnect->status != LDAP_BIND_OK) {
      $connOK = false;
      $t_authenticated->status_ok = false;
      $t_authenticated->status_code = $ldapConnect->status;
      $t_authenticated->status_verbose = $ldapConnect->info;
    }
    
    if ($connOK) {
      // search to find the distinguish name
      $filters = str_replace('%login%',$p_login_name,$ldapCfg['ldap_filter']);
      // Testlink01$$
      $rs = (array)$ldapConnect->handler->query()->rawFilter($filters)->get();
      foreach($rs as $item) {
        if ($ldapConnect->handler->auth()->attempt($item['dn'],$p_password)) {
          $t_authenticated->status_ok = true;
          $t_authenticated->status_code = 'OK';
          $t_authenticated->status_verbose = 'OK';
          break;    
        }
      }
    }
  }  
  return $t_authenticated;
}


/**
 * Escapes the LDAP string to disallow injection.
 *
 * @param string $p_string The string to escape.
 * @return string The escaped string.
 */
function ldap_escape_string( $p_string ) 
{
  $t_find = array( '\\', '*', '(', ')', '/', "\x00" );
  $t_replace = array( '\5c', '\2a', '\28', '\29', '\2f', '\00' );

  $t_string = str_replace( $t_find, $t_replace, $p_string );

  return $t_string;
}

/**
 *
 * Gets the value of a specific field from LDAP given the user name and LDAP field name.
 *
 * @param string $p_username The user name.
 * @param string $p_field struct where 
 * 
 */
function ldap_get_field_from_username($ldapCfg, $p_username, $p_field_set) {

  $ldapConnect = ldap_connect_bind($ldapCfg);
  if ($ldapConnect->status != LDAP_BIND_OK) {
    return null;
  }

  $filters = str_replace('%login%',$p_username,$ldapCfg['ldap_filter']);
  $rs = (array)$ldapConnect->handler->query()->select($p_field_set)->rawFilter($filters)->get();
  $rs = $rs[0];  
  
  $fieldsValue = new stdClass();
  foreach($p_field_set as $t_ldap_field_name) {
    $fieldsValue->$t_ldap_field_name = null;
    if (isset($rs[$t_ldap_field_name])) {
      $fieldsValue->$t_ldap_field_name = $rs[$t_ldap_field_name][0];
    }
  }

  return $fieldsValue;
}
