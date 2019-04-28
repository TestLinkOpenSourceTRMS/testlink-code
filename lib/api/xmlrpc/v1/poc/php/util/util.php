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
  $html_id="result_{$feedback_id}";
  $msg_click_to_show="click to show XML-RPC Client Debug Info";
 
  $imgFO = dirname(__FILE__) . DIRECTORY_SEPARATOR . 
           'img' . DIRECTORY_SEPARATOR . 'icon-foldout.gif';
  $imgSRC = ' <img src="' . $imgFO . '"' .
            ' align="top" title="show/hide">'; 

  $onClick = "return DetailController.toggle('{$html_id}')";

  if($client->debug)
  {
    echo '<br>Debug: Inside function: ' . __FUNCTION__ . '<br>';
    new dBug($args);

    echo '<br/><a onclick="' . $onClick . '" href="nowhere/"> ';
    echo $imgSRC . "{$msg_click_to_show} </a>";
  }
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