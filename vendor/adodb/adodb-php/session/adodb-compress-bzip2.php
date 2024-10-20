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
 * @author Ross Smith <adodb@netebb.com>
 */

if (!function_exists('bzcompress')) {
	trigger_error('bzip2 functions are not available', E_USER_ERROR);
	return 0;
}

/**
 */
class ADODB_Compress_Bzip2 {
	/**
	 */
	var $_block_size = null;

	/**
	 */
	var $_work_level = null;

	/**
	 */
	var $_min_length = 1;

	/**
	 */
	function getBlockSize() {
		return $this->_block_size;
	}

	/**
	 */
	function setBlockSize($block_size) {
		assert($block_size >= 1);
		assert($block_size <= 9);
		$this->_block_size = (int) $block_size;
	}

	/**
	 */
	function getWorkLevel() {
		return $this->_work_level;
	}

	/**
	 */
	function setWorkLevel($work_level) {
		assert($work_level >= 0);
		assert($work_level <= 250);
		$this->_work_level = (int) $work_level;
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
	function __construct($block_size = null, $work_level = null, $min_length = null) {
		if (!is_null($block_size)) {
			$this->setBlockSize($block_size);
		}

		if (!is_null($work_level)) {
			$this->setWorkLevel($work_level);
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

		if (!is_null($this->_block_size)) {
			if (!is_null($this->_work_level)) {
				return bzcompress($data, $this->_block_size, $this->_work_level);
			} else {
				return bzcompress($data, $this->_block_size);
			}
		}

		return bzcompress($data);
	}

	/**
	 */
	function read($data, $key) {
		return $data ? bzdecompress($data) : $data;
	}

}

return 1;
