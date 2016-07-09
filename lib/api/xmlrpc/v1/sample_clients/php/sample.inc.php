<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: sample.inc.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2010/02/01 17:59:07 $ by $Author: franciscom $
 * @Author: francisco.mancardi@gmail.com
 *
 * rev: 
 */
?>

<html>
<head>
<title><?php echo $tl_and_version ?></title>
        <style type="text/css">
             @import url('./css/style.css');
        </style>

<script type="text/javascript">
// This code has been obtained from backbase examples pages
//
var DetailController = {
	storedDetail : '',

	toggle : function(id){
		if(this.storedDetail && this.storedDetail != id) 
		{
		  document.getElementById(this.storedDetail).style.display = 'none';
		}
		this.storedDetail = id;
		var style = document.getElementById(id).style;
		if(style.display == 'block') 
		{
		  style.display = 'none';
		}
		else
		{
		  style.display = 'block';
		} 
		return false;
	}
};
</script>
</head>

<?php
 /** 
  * Need the IXR class for client
  */
define("THIRD_PARTY_CODE","../../../../../../third_party");
require_once THIRD_PARTY_CODE . '/xml-rpc/class-IXR.php';
require_once THIRD_PARTY_CODE . '/dBug/dBug.php';
if( isset($_SERVER['HTTP_REFERER']) )
{
    $target = $_SERVER['HTTP_REFERER'];
    $prefix = '';
}
else
{
    $target = $_SERVER['REQUEST_URI'];
    $prefix = "http://" . $_SERVER['HTTP_HOST'] . ":" . $_SERVER['SERVER_PORT'];
} 
$dummy=explode('sample_clients',$target);
$server_url=$prefix . $dummy[0] . "xmlrpc.php";

echo '<h1>Test Link XML-RPC API - PHP Samples </h1><br />';

// substitute your Dev Key Here
define("DEV_KEY", "dev01");
if( DEV_KEY == "dev01" )
{
    echo '<h1>Attention: DEVKEY is still setted to demo value (' . DEV_KEY . ')</h1>';
    echo 'Please check if this VALUE is defined for a user on yout DB Installation<b>';
    echo '<hr>';
}
?> 