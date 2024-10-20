<?php
/**
 * ADOdb Session Management
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
 */

if (!function_exists('mcrypt_encrypt')) {
	trigger_error('Mcrypt functions are not available', E_USER_ERROR);
	return 0;
}

/**
 */
class ADODB_Encrypt_MCrypt {
	/**
	 */
	var $_cipher;

	/**
	 */
	var $_mode;

	/**
	 */
	var $_source;

	/**
	 */
	function getCipher() {
		return $this->_cipher;
	}

	/**
	 */
	function setCipher($cipher) {
		$this->_cipher = $cipher;
	}

	/**
	 */
	function getMode() {
		return $this->_mode;
	}

	/**
	 */
	function setMode($mode) {
		$this->_mode = $mode;
	}

	/**
	 */
	function getSource() {
		return $this->_source;
	}

	/**
	 */
	function setSource($source) {
		$this->_source = $source;
	}

	/**
	 */
	function __construct($cipher = null, $mode = null, $source = null) {
		if (!$cipher) {
			$cipher = MCRYPT_RIJNDAEL_256;
		}
		if (!$mode) {
			$mode = MCRYPT_MODE_ECB;
		}
		if (!$source) {
			$source = MCRYPT_RAND;
		}

		$this->_cipher = $cipher;
		$this->_mode = $mode;
		$this->_source = $source;
	}

	/**
	 */
	function write($data, $key) {
		$iv_size = mcrypt_get_iv_size($this->_cipher, $this->_mode);
		$iv = mcrypt_create_iv($iv_size, $this->_source);
		return mcrypt_encrypt($this->_cipher, $key, $data, $this->_mode, $iv);
	}

	/**
	 */
	function read($data, $key) {
		$iv_size = mcrypt_get_iv_size($this->_cipher, $this->_mode);
		$iv = mcrypt_create_iv($iv_size, $this->_source);
		$rv = mcrypt_decrypt($this->_cipher, $key, $data, $this->_mode, $iv);
		return rtrim($rv, "\0");
	}

}

return 1;
