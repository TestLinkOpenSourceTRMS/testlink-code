<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename index.php
 *
 * @Author: francisco.mancardi@gmail.com
 *
 * @internal revision 
 * 20110219 - franciscom - order file list using natsort()
 *
 */
require_once 'util.php';
require_once 'sample.inc.php';

if( !defined('DBUG_ON') )
{
	define('DBUG_ON',1);  // ADDED to enable ALWAYS dBug()
}    

$target_dir = '.';
$examples = null;
$prefix2get = 'client';
$prefixlen = strlen('client');

if ($handle = opendir($target_dir)) 
{
	while (false !== ($file = readdir($handle))) 
	{
		clearstatcache();
		if (($file != ".") && ($file != "..")) 
		{
			if (is_file($target_dir . DIRECTORY_SEPARATOR . $file))
			{
				$pinfo = pathinfo($file);
				if( $pinfo['extension'] == 'php' &&  
					substr($pinfo['basename'],0,$prefixlen) == $prefix2get)
				{	                                         
					$examples[] = $file;
				}	
			}    
		}
	}
	closedir($handle);
	if(!is_null($examples))
	{
		natsort($examples);
	}	
}

echo '<br><br>Click on file name to launch sample client<br><br>';
echo '<table>';
foreach($examples as $url2launch)
{
	echo '<tr><td>';
	echo '<a href="' . './' . $url2launch . '">' . $url2launch .  '</a>';
	echo '</td></tr>';
}
echo '</table>'; 

?> 
