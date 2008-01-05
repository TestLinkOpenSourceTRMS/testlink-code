<?php
/*
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: ldap_api.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2008/01/05 17:56:29 $ by $Author: franciscom $
 *
 * This piece of software has been copied and adapted from:
 
 # Mantis - a php based bugtracking system
 # Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 # Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
 # This program is distributed under the terms and conditions of the GPL
 # See the README and LICENSE files for details
 ###########################################################################
 # LDAP API
 ###########################################################################
*/
	
	
  
 	# --------------------
	# Connect and bind to the LDAP directory
	function ldap_connect_bind( $p_binddn = '', $p_password = '' ) {

    $ret->status=0;
		$ret->handler=null;
	  
		$t_ldap_server	= config_get( 'ldap_server' );
		$t_ldap_port	= config_get( 'ldap_port' );

		$t_ds = ldap_connect ( $t_ldap_server, $t_ldap_port );
		
		// BUGID 1247
		ldap_set_option($t_ds, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($t_ds, LDAP_OPT_REFERRALS, 0);

		if ( $t_ds > 0 ) {
		  
		  $ret->handler=$t_ds;
	  
			# If no Bind DN and Password is set, attempt to login as the configured
			#  Bind DN.
			if ( is_blank( $p_binddn ) && is_blank( $p_password ) ) {
				$p_binddn	= config_get( 'ldap_bind_dn', '' );
				$p_password	= config_get( 'ldap_bind_passwd', '' );
			}

			if ( !is_blank( $p_binddn ) && !is_blank( $p_password ) ) {
				$t_br = ldap_bind( $t_ds, $p_binddn, $p_password );
			} else {
				# Either the Bind DN or the Password are empty, so attempt an anonymous bind.
				$t_br = ldap_bind( $t_ds );
			}
			if ( !$t_br ) {
        $ret->status=ERROR_LDAP_BIND_FAILED;
			}
		} else {
			$ret->status=ERROR_LDAP_SERVER_CONNECT_FAILED;
		}

    
		return $ret;
	}

	# --------------------
	# Attempt to authenticate the user against the LDAP directory
	function ldap_authenticate( $p_login_name, $p_password ) {

		# if password is empty and ldap allows anonymous login, then
		# the user will be able to login, hence, we need to check
		# for this special case.
		if ( is_blank( $p_password ) ) {
			return false;
		}

		$t_ldap_organization	= config_get( 'ldap_organization' );
		$t_ldap_root_dn			= config_get( 'ldap_root_dn' );

		$t_username      	= $p_login_name;
		$t_ldap_uid_field	= config_get( 'ldap_uid_field', 'uid' ) ;
		$t_search_filter 	= "(&$t_ldap_organization($t_ldap_uid_field=$t_username))";
		$t_search_attrs  	= array( $t_ldap_uid_field, 'dn' );
		$t_connect       	= ldap_connect_bind();

    if( !is_null($t_connect->handler) )
    {
        $t_ds = $t_connect->handler;
        
    		# Search for the user id
    		$t_sr	= ldap_search( $t_ds, $t_ldap_root_dn, $t_search_filter, $t_search_attrs );
    		$t_info	= ldap_get_entries( $t_ds, $t_sr );
    
    		$t_authenticated->status_ok = false;
        $t_authenticated->status_code = ERROR_LDAP_AUTH_FAILED;
        
        
    		if ( $t_info ) {
    			# Try to authenticate to each until we get a match
    			for ( $i = 0 ; $i < $t_info['count'] ; $i++ ) {
    				$t_dn = $t_info[$i]['dn'];
    
    				# Attempt to bind with the DN and password
    				if ( @ldap_bind( $t_ds, $t_dn, $p_password ) ) {
    					$t_authenticated->status_ok = true;
    					break; # Don't need to go any further
    				}
    			}
    		}
    
    		ldap_free_result( $t_sr );
    		ldap_unbind( $t_ds );
    }
    else
    {
       $t_authenticated->status_ok = false;
       $t_authenticated->status_code = $t_connect->status;
    }
    return $t_authenticated;
	}

?>
