<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Downloads the attachment by a given id
 *
 * @filesource attachmentdownload.php
 *
 */
@ob_end_clean();
require_once('../../config.inc.php');
require_once('../functions/common.php');
require_once('../functions/attachments.inc.php');

// This way can be called without _SESSION, this is useful for reports
testlinkInitPage($db,false,true);

$args = init_args($db);
if ($args->id)
{
  $attachmentRepository = tlAttachmentRepository::create($db);
  $attachmentInfo = $attachmentRepository->getAttachmentInfo($args->id);

  if ($attachmentInfo) {
    switch ($args->opmode) 
    {
      case 'API':
        // want to check if apikey provided is right for attachment context
        // - test project api key:
        //   is needed to get attachments for:
        //   test specifications
        //
        // - test plan api key:
        //   is needed to get attacments for:
        //   test case executions
        //   test specifications  ( access to parent data - OK!)
        //   
        // What kind of attachments I've got ?
        $doIt = false;
        $attContext = $attachmentInfo['fk_table'];
        switch($attContext)
        {
          case 'executions':
            // check apikey
            // 1. has to be a test plan key
            // 2. execution must belong to the test plan.
            $item = getEntityByAPIKey($db,$args->apikey,'testplan');
            if( !is_null($item) )
            {
              $tables = tlObjectWithDB::getDBTables(array('executions'));
              $sql = "SELECT testplan_id FROM {$tables['executions']} " .
                     "WHERE id = " . intval($attachmentInfo['fk_id']);

              $rs = $db->get_recordset($sql);
              if(!is_null($rs))
              {
                if($rs['0']['testplan_id'] == $item['id'])
                {
                  // GOOD !
                  $doIt = true;
                }  
              }       
            }  
          break;
        }
      break;
      
      case 'GUI':
      default:   
        $doIt = true;
      break;
    }


    if( $doIt )
    {
      $content = '';
      $getContent = true;
      if( $args->opmode !== 'API' && $args->skipCheck !== 0 && $args->skipCheck !== false)
      {
        if( $args->skipCheck != hash('sha256',$attachmentInfo['file_name']) )
        {
          $getContent = false;
        }  
      }  

      if($getContent)
      {
        $content = $attachmentRepository->getAttachmentContent($args->id,$attachmentInfo);
      }  

      if ($content != "" )
      {
        @ob_end_clean();
        header('Pragma: public');
        header("Cache-Control: ");
        if (!(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" && preg_match("/MSIE/",$_SERVER["HTTP_USER_AGENT"])))
        { 
          header('Pragma: no-cache');
        }
        header('Content-Type: '.$attachmentInfo['file_type']);
        header('Content-Length: '.$attachmentInfo['file_size']);
        header("Content-Disposition: inline; filename=\"{$attachmentInfo['file_name']}\"");
        header("Content-Description: Download Data");
        echo $content;
        exit();
      }      
    }  
  }
}

$smarty = new TLSmarty();
$smarty->assign('gui',$args);
$smarty->display('attachment404.tpl');

/**
 * @return object returns the arguments for the page
 */
function init_args(&$dbHandler)
{
  // id (attachments.id) of the attachment to be downloaded
  $iParams = array('id' => array(tlInputParameter::INT_N),
                   'apikey' => array(tlInputParameter::STRING_N,64),  
                   'skipCheck' => array(tlInputParameter::STRING_N,1,64));
  
  $args = new stdClass();
  G_PARAMS($iParams,$args);

  $args->light = 'green';
  $args->opmode = 'GUI';
  if( is_null($args->skipCheck) || $args->skipCheck === 0 )
  {
    $args->skipCheck = false;
  }  

  // var_dump($args->skipCheck);die();
  // using apikey lenght to understand apikey type
  // 32 => user api key
  // other => test project or test plan
  $args->apikey = trim($args->apikey);
  $apikeyLenght = strlen($args->apikey);
  if($apikeyLenght > 0)
  {
    $args->opmode = 'API';
    $args->skipCheck = true;
  } 
  return $args;
}

/**
 * @param $db resource the database connection handle
 * @param $user the current active user
 * @return boolean returns true if the page can be accessed
 */
function checkRights(&$db,&$user)
{
  return (config_get("attachments")->enabled);
}