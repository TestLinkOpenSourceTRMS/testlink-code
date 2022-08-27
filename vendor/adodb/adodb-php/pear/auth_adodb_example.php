<?php
/**
 * PEAR Auth example
 *
 * NOTE: The ADOdb and PEAR directories MUST be in your PHP include_path!
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
//
require_once "Auth/Auth.php";

function loginFunction() {
?>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
    <input type="text" name="username">
    <input type="password" name="password">
    <input type="submit">
    </form>
<?php
}

$dsn = 'mysql://username:password@hostname/database';
// To use encrypted passwords, change cryptType to 'md5'
$params = array('dsn' => $dsn, 'table' => 'auth', 'cryptType' => 'none',
                'usernamecol' => 'username', 'passwordcol' => 'password');
$a = new Auth("ADOdb", $params, "loginFunction");
$a->start();

if ($a->getAuth()) {
    echo "Success";
    // * The output of your site goes here.
}
