<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * @filesource  displayMgr.php
 * @author      Kevin Levy
 * 
 * @internal revisions
 * @since 1.9.8
 *
 */
require_once('email_api.php');
require_once('../../cfg/reports.cfg.php');

/**
 * 
 *
 */
function generateHtmlEmail(&$smarty, $template_file, $mailCfg)
{
  // same objet that is returned by email_send
  $op = new stdClass();
  $op->status_ok = true;
  $op->msg = 'ok';
  
  $html_report = $smarty->fetch($template_file);
  if( ! property_exists($mailCfg,'from') )
  {
    $mailCfg->from = $_SESSION['currentUser']->emailAddress;
  }
  if( ! property_exists($mailCfg,'to') )
  {
    $mailCfg->to = $mailCfg->from;
  }
  
  if($mailCfg->to == "")
  {
    $op->status_ok = false;
    $op->msg = lang_get("error_sendreport_no_email_credentials");
  }
  else
  {
    // TICKET 6905: Link to test case is still raw link (no title) in email(HTML) type of test report
    // array('strip_email_links' => false)
    $op = email_send( $mailCfg->from, $mailCfg->to, $mailCfg->subject, 
                      $html_report, $mailCfg->cc, null,false,true,
                      array('strip_email_links' => false));

    if($op->status_ok)
    {
      $op->msg = sprintf(lang_get('mail_sent_to'), $mailCfg->to);
    }
  }
  return $op;
}


/**
 * 
 *
 */
function displayReport($template_file, &$smarty, $doc_format, $mailCfg = null)
{

  switch($doc_format)
  {
    case FORMAT_HTML:
    case FORMAT_ODT:
    case FORMAT_ODS:
    case FORMAT_XLS:
    case FORMAT_MSWORD:
    case FORMAT_PDF:
      flushHttpHeader($doc_format, $doc_kind = 0);
    break;  

    case FORMAT_MAIL_HTML:
      $op = generateHtmlEmail($smarty, $template_file,  $mailCfg);
      
      switch($template_file)
      {
        case 'results/resultsGeneral.tpl'; 
         flushHttpHeader(FORMAT_HTML, $doc_kind = 0);
         $message =   
           

         $mf->msg = $op->status_ok ? '' : lang_get('send_mail_ko');
         $mf->msg .= ' ' . $op->msg;
         $mf->title = ''; //$mailCfg->subject;
         $smarty->assign('mailFeedBack',$mf);
        break;   

        default:
          $message = $op->status_ok ? '' : lang_get('send_mail_ko');  
          $smarty = new TLSmarty();
          $smarty->assign('message', $message . ' ' . $op->msg);
          $smarty->assign('title', $mailCfg->subject);
          $template_file = "emailSent.tpl";
        break;   
      }
    break;
  } 

  $smarty->display($template_file);
}


/**
 * Generate HTML header and send it to browser
 * @param string $format identifier of document format; value must be in $tlCfg->reports_formats
 * @param integer $doc_kind Magic number of document kind; see consts.inc.php for list 
 *    (for example: DOC_TEST_PLAN_DESIGN)
 * @author havlatm
 */
function flushHttpHeader($format, $doc_kind = 0)
{
  $file_extensions = config_get('reports_file_extension');
  $reports_applications = config_get('reports_applications');

  switch($doc_kind)
  {
    case DOC_TEST_SPEC: 
      $kind_acronym = '_test_spec'; 
    break;
    
    case DOC_TEST_PLAN_DESIGN: 
      $kind_acronym = '_test_plan'; 
    break;

    case DOC_TEST_PLAN_EXECUTION: 
      $kind_acronym = '_test_report';
    break;
    
    case DOC_REQ_SPEC: 
      $kind_acronym = '_req_spec'; 
    break;
    
    default: 
      $kind_acronym = '';
    break;  
  }
  

  if ($format == FORMAT_MAIL_HTML)
  {
    tLog('flushHttpHeader> Invalid format: '.$format, 'ERROR');
  }
  
  $filename = isset($_SESSION['testprojectPrefix']) ? $_SESSION['testprojectPrefix'] : '';
  $filename .= $kind_acronym . '-' . date('Y-m-d') . '.' . $file_extensions[$format];
  tLog('Flush HTTP header for '.$format); 

  
  $contentType = isset($reports_applications[$format]) ? $reports_applications[$format] : 'text/html';
  $contentType .= (is_null($format) || $format=='') ? '' : ("; name='Testlink_" . $format ."'") ;
  header("Content-type: {$contentType}");
  header("Content-Description: TestLink - Generated Document (see " . __FUNCTION__ . ")" );
  if( (!is_null($format) && $format != '') && $format != FORMAT_HTML )
  {
    header("Content-Disposition: attachment; filename=$filename");
  }  
  flush();
}