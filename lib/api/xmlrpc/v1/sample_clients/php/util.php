<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: util.php,v $
 *
 * @version $Revision: 1.4 $
 * @modified $Date: 2010/07/10 15:04:33 $ by $Author: franciscom $
 * @Author: francisco.mancardi@gmail.com
 *
 * rev: 20100710 - franciscom - runTest() now returns server response.
 */

define('DBUG_ON',1);  // ADDED to enable ALWAYS dBug()

function show_api_db_sample_msg()
{
  echo '<br /><h1>This sample can be runned without changes against sample database testlinkAPI';
  echo ('<br>that you will find on [YOUR TL INSTALLATION DIR]' . '\\docs\\db_sample\\');
  echo '</h1><hr><br />';   
}

function runTest(&$client,$method,$args,$feedback_id=1)
{
  
  echo __FUNCTION__ . '<br>';
  new dBug($args);
  $html_id="result_{$feedback_id}";
    
  $msg_click_to_show="click to show XML-RPC Client Debug Info";
  echo "<br /><a onclick=\"return DetailController.toggle('{$html_id}')\" href=\"nowhere/\">
  <img src='img/icon-foldout.gif' align='top' title='show/hide'>{$msg_click_to_show}</a>";
  echo '<div class="detail-container" id="' . $html_id . '" style="display: none;">';
    
  if(!$client->query("tl.{$method}", $args))
  {
    echo "something went wrong - " . $client->getErrorCode() . " - " . $client->getErrorMessage();      
    $response=null;
  }
  else
  {
    echo 'Call done!<br>';
    $response = $client->getResponse();
  }
  echo "</div><p>";
    
  echo "<br> Result was: ";
  new dBug($response);
  echo "<br>";
  echo "<hr>";
 
  return $response;
} 
