<?php
/**
 * ADOdb Session Management
 *
 * @deprecated
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
 * @author Ari Kuorikoski <ari.kuorikoski@finebyte.com>
 */

class MD5Crypt{
		function keyED($txt,$encrypt_key)
		{
				$encrypt_key = md5($encrypt_key);
				$ctr=0;
				$tmp = "";
				for ($i=0;$i<strlen($txt);$i++){
						if ($ctr==strlen($encrypt_key)) $ctr=0;
						$tmp.= substr($txt,$i,1) ^ substr($encrypt_key,$ctr,1);
						$ctr++;
				}
				return $tmp;
		}

		function Encrypt($txt,$key)
		{
				$encrypt_key = md5(rand(0,32000));
				$ctr=0;
				$tmp = "";
				for ($i=0;$i<strlen($txt);$i++)
				{
				if ($ctr==strlen($encrypt_key)) $ctr=0;
				$tmp.= substr($encrypt_key,$ctr,1) .
				(substr($txt,$i,1) ^ substr($encrypt_key,$ctr,1));
				$ctr++;
				}
				return base64_encode($this->keyED($tmp,$key));
		}

		function Decrypt($txt,$key)
		{
				$txt = $this->keyED(base64_decode($txt),$key);
				$tmp = "";
				for ($i=0;$i<strlen($txt);$i++){
						$md5 = substr($txt,$i,1);
						$i++;
						$tmp.= (substr($txt,$i,1) ^ $md5);
				}
				return $tmp;
		}

		function RandPass()
		{
				$randomPassword = "";
				for($i=0;$i<8;$i++)
				{
						$randnumber = rand(48,120);

						while (($randnumber >= 58 && $randnumber <= 64) || ($randnumber >= 91 && $randnumber <= 96))
						{
								$randnumber = rand(48,120);
						}

						$randomPassword .= chr($randnumber);
				}
				return $randomPassword;
		}

}
