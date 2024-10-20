<?php
/**
 * Storage driver for fetching login data from a database using ADOdb-PHP.
 *
 * This storage driver can use all databases which are supported by the ADBdb
 * abstraction layer to fetch login data.
 * NOTE: The ADOdb directory MUST be in your PHP include_path!
 *
 * This file is part of ADOdb, a Database Abstraction Layer library for PHP.
 *
 * @package ADOdb
 * @link https://adodb.org Project's web site and documentation
 * @link https://github.com/ADOdb/ADOdb Source code and issue tracker
 *
 * The ADOdb Library is dual-licensed, released under both the BSD 3-Clause
 * and the GNU Lesser General Public Licence (LGPL) v2.1 or, at your option,
 * any later version. This means you can use it in proprietary products.
 * See the LICENSE.md file distributed with this source code for details.
 * @license BSD-3-Clause
 * @license LGPL-2.1-or-later
 *
 * @copyright 2000-2013 John Lim
 * @copyright 2014 Damien Regad, Mark Newnham and the ADOdb community
 * @author Martin Jansen <mj@php.net>
 * @author Richard Tango-Lowy <richtl@arscognita.com>
 */

require_once 'Auth/Container.php';
require_once 'adodb.inc.php';
require_once 'adodb-pear.inc.php';
require_once 'adodb-errorpear.inc.php';


class Auth_Container_ADOdb extends Auth_Container
{

    /**
     * Additional options for the storage container
     * @var array
     */
    var $options = array();

    /**
     * DB object
     * @var object
     */
    var $db = null;
    var $dsn = '';

    /**
     * User that is currently selected from the DB.
     * @var string
     */
    var $activeUser = '';

    // {{{ Constructor

    /**
     * Constructor of the container class
     *
     * Initiate connection to the database via PEAR::ADOdb
     *
     * @param  string Connection data or DB object
     * @return object Returns an error object if something went wrong
     */
    function __construct($dsn)
    {
        $this->_setDefaults();

        if (is_array($dsn)) {
            $this->_parseOptions($dsn);

            if (empty($this->options['dsn'])) {
                PEAR::raiseError('No connection parameters specified!');
            }
        } else {
        	// Extract db_type from dsn string.
            $this->options['dsn'] = $dsn;
        }
    }

    // }}}
    // {{{ _connect()

    /**
     * Connect to database by using the given DSN string
     *
     * @access private
     * @param  string DSN string
     * @return mixed  Object on error, otherwise bool
     */
     function _connect($dsn)
    {
        if (is_string($dsn) || is_array($dsn)) {
        	if(!$this->db) {
	        	$this->db = ADONewConnection($dsn);
	    		if( $err = ADODB_Pear_error() ) {
	   	    		return PEAR::raiseError($err);
	    		}
        	}

        } else {
            return PEAR::raiseError('The given dsn was not valid in file ' . __FILE__ . ' at line ' . __LINE__,
                                    41,
                                    PEAR_ERROR_RETURN,
                                    null,
                                    null
                                    );
        }

        if(!$this->db) {
        	return PEAR::raiseError(ADODB_Pear_error());
        } else {
        	return true;
        }
    }

    // }}}
    // {{{ _prepare()

    /**
     * Prepare database connection
     *
     * This function checks if we have already opened a connection to
     * the database. If that's not the case, a new connection is opened.
     *
     * @access private
     * @return mixed True or a DB error object.
     */
    function _prepare()
    {
    	if(!$this->db) {
    		$res = $this->_connect($this->options['dsn']);
    	}
        return true;
    }

    // }}}
    // {{{ query()

    /**
     * Prepare query to the database
     *
     * This function checks if we have already opened a connection to
     * the database. If that's not the case, a new connection is opened.
     * After that the query is passed to the database.
     *
     * @access public
     * @param  string Query string
     * @return mixed  a DB_result object or DB_OK on success, a DB
     *                or PEAR error on failure
     */
    function query($query)
    {
        $err = $this->_prepare();
        if ($err !== true) {
            return $err;
        }
        return $this->db->query($query);
    }

    // }}}
    // {{{ _setDefaults()

    /**
     * Set some default options
     *
     * @access private
     * @return void
     */
    function _setDefaults()
    {
    	$this->options['db_type']	= 'mysql';
        $this->options['table']       = 'auth';
        $this->options['usernamecol'] = 'username';
        $this->options['passwordcol'] = 'password';
        $this->options['dsn']         = '';
        $this->options['db_fields']   = '';
        $this->options['cryptType']   = 'md5';
    }

    // }}}
    // {{{ _parseOptions()

    /**
     * Parse options passed to the container class
     *
     * @access private
     * @param  array
     */
    function _parseOptions($array)
    {
        foreach ($array as $key => $value) {
            if (isset($this->options[$key])) {
                $this->options[$key] = $value;
            }
        }

        /* Include additional fields if they exist */
        if(!empty($this->options['db_fields'])){
            if(is_array($this->options['db_fields'])){
                $this->options['db_fields'] = join(', ', $this->options['db_fields']);
            }
            $this->options['db_fields'] = ', '.$this->options['db_fields'];
        }
    }

    // }}}
    // {{{ fetchData()

    /**
     * Get user information from database
     *
     * This function uses the given username to fetch
     * the corresponding login data from the database
     * table. If an account that matches the passed username
     * and password is found, the function returns true.
     * Otherwise it returns false.
     *
     * @param   string Username
     * @param   string Password
     * @return  mixed  Error object or boolean
     */
    function fetchData($username, $password)
    {
        // Prepare for a database query
        $err = $this->_prepare();
        if ($err !== true) {
            return PEAR::raiseError($err->getMessage(), $err->getCode());
        }

        // Find if db_fields contains a *, i so assume all col are selected
        if(strstr($this->options['db_fields'], '*')){
            $sql_from = "*";
        }
        else{
            $sql_from = $this->options['usernamecol'] . ", ".$this->options['passwordcol'].$this->options['db_fields'];
        }

        $query = "SELECT ".$sql_from.
                " FROM ".$this->options['table'].
                " WHERE ".$this->options['usernamecol']." = " . $this->db->Quote($username);

        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
        $rset = $this->db->Execute( $query );
        $res = $rset->fetchRow();

        if (DB::isError($res)) {
            return PEAR::raiseError($res->getMessage(), $res->getCode());
        }
        if (!is_array($res)) {
            $this->activeUser = '';
            return false;
        }
        if ($this->verifyPassword(trim($password, "\r\n"),
                                  trim($res[$this->options['passwordcol']], "\r\n"),
                                  $this->options['cryptType'])) {
            // Store additional field values in the session
            foreach ($res as $key => $value) {
                if ($key == $this->options['passwordcol'] ||
                    $key == $this->options['usernamecol']) {
                    continue;
                }
                // Use reference to the auth object if exists
                // This is because the auth session variable can change so a static call to setAuthData does not make sense
                if(is_object($this->_auth_obj)){
                    $this->_auth_obj->setAuthData($key, $value);
                } else {
                    Auth::setAuthData($key, $value);
                }
            }

            return true;
        }

        $this->activeUser = $res[$this->options['usernamecol']];
        return false;
    }

    // }}}
    // {{{ listUsers()

    function listUsers()
    {
        $err = $this->_prepare();
        if ($err !== true) {
            return PEAR::raiseError($err->getMessage(), $err->getCode());
        }

        $retVal = array();

        // Find if db_fileds contains a *, i so assume all col are selected
        if(strstr($this->options['db_fields'], '*')){
            $sql_from = "*";
        }
        else{
            $sql_from = $this->options['usernamecol'] . ", ".$this->options['passwordcol'].$this->options['db_fields'];
        }

        $query = sprintf("SELECT %s FROM %s",
                         $sql_from,
                         $this->options['table']
                         );
        $res = $this->db->getAll($query, null, DB_FETCHMODE_ASSOC);

        if (DB::isError($res)) {
            return PEAR::raiseError($res->getMessage(), $res->getCode());
        } else {
            foreach ($res as $user) {
                $user['username'] = $user[$this->options['usernamecol']];
                $retVal[] = $user;
            }
        }
        return $retVal;
    }

    // }}}
    // {{{ addUser()

    /**
     * Add user to the storage container
     *
     * @access public
     * @param  string Username
     * @param  string Password
     * @param  mixed  Additional information that are stored in the DB
     *
     * @return mixed True on success, otherwise error object
     */
    function addUser($username, $password, $additional = "")
    {
        if (function_exists($this->options['cryptType'])) {
            $cryptFunction = $this->options['cryptType'];
        } else {
            $cryptFunction = 'md5';
        }

        $additional_key   = '';
        $additional_value = '';

        if (is_array($additional)) {
            foreach ($additional as $key => $value) {
                $additional_key .= ', ' . $key;
                $additional_value .= ", '" . $value . "'";
            }
        }

        $query = sprintf("INSERT INTO %s (%s, %s%s) VALUES ('%s', '%s'%s)",
                         $this->options['table'],
                         $this->options['usernamecol'],
                         $this->options['passwordcol'],
                         $additional_key,
                         $username,
                         $cryptFunction($password),
                         $additional_value
                         );

        $res = $this->query($query);

        if (DB::isError($res)) {
           return PEAR::raiseError($res->getMessage(), $res->getCode());
        } else {
          return true;
        }
    }

    // }}}
    // {{{ removeUser()

    /**
     * Remove user from the storage container
     *
     * @access public
     * @param  string Username
     *
     * @return mixed True on success, otherwise error object
     */
    function removeUser($username)
    {
        $query = sprintf("DELETE FROM %s WHERE %s = '%s'",
                         $this->options['table'],
                         $this->options['usernamecol'],
                         $username
                         );

        $res = $this->query($query);

        if (DB::isError($res)) {
           return PEAR::raiseError($res->getMessage(), $res->getCode());
        } else {
          return true;
        }
    }

    // }}}
}

function showDbg( $string ) {
	print "
-- $string</P>";
}
function dump( $var, $str, $vardump = false ) {
	print "<H4>$str</H4><pre>";
	( !$vardump ) ? ( print_r( $var )) : ( var_dump( $var ));
	print "</pre>";
}
