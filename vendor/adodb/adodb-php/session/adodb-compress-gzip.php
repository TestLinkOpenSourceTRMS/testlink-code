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

if (!function_exists('gzcompress')) {
	trigger_error('gzip functions are not available', E_USER_ERROR);
	return 0;
}

/*
*/
class ADODB_Compress_Gzip {
	/**
	 */
	var $_level = null;

	/**
	 */
	var $_min_length = 1;

	/**
	 */
	function getLevel() {
		return $this->_level;
	}

	/**
	 */
	function setLevel($level) {
		assert($level >= 0);
		assert($level <= 9);
		$this->_level = (int) $level;
	}

	/**
	 */
	function getMinLength() {
		return $this->_min_length;
	}

	/**
	 */
	function setMinLength($min_length) {
		assert($min_length >= 0);
		$this->_min_length = (int) $min_length;
	}

	/**
	 */
	function __construct($level = null, $min_length = null) {
		if (!is_null($level)) {
			$this->setLevel($level);
		}

		if (!is_null($min_length)) {
			$this->setMinLength($min_length);
		}
	}

	/**
	 */
	function write($data, $key) {
		if (strlen($data) < $this->_min_length) {
			return $data;
		}

		if (!is_null($this->_level)) {
			return gzcompress($data, $this->_level);
		} else {
			return gzcompress($data);
		}
	}

	/**
	 */
	function read($data, $key) {
		return $data ? gzuncompress($data) : $data;
	}

}

return 1;
