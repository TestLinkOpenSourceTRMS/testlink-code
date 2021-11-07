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

@define('HORDE_BASE', dirname(dirname(dirname(__FILE__))) . '/horde');

if (!is_dir(HORDE_BASE)) {
	trigger_error(sprintf('Directory not found: \'%s\'', HORDE_BASE), E_USER_ERROR);
	return 0;
}

include_once HORDE_BASE . '/lib/Horde.php';
include_once HORDE_BASE . '/lib/Secret.php';

class ADODB_Encrypt_Secret {
	/**
	 */
	function write($data, $key) {
		return Secret::write($key, $data);
	}

	/**
	 */
	function read($data, $key) {
		return Secret::read($key, $data);
	}

}

return 1;
