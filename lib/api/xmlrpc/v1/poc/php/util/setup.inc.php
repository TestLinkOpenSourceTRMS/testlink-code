<?php
/* 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * @filesource  setup.inc.php
 * @Author      francisco.mancardi@gmail.com
 */

$css = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'style.css';
?>

<html>
<head>
  <title>TestLink XMLRPC Proof Of Concept</title>
  <style type="text/css">
  @import 
    url('<?php echo $css ?>');
  </style>


  <script type="text/javascript">
  // This code has been obtained from backbase examples pages
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
define("THIRD_PARTY_CODE","../../../../../../../third_party");
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
$dummy = explode('poc',$target);
$server_url = $prefix . $dummy[0] . "xmlrpc.php";

echo '<h1>TestLink XML-RPC API - POC Samples Runner</h1><br />';
echo "<b>XMLRPC Server URL {$server_url} </b><br /><br />";