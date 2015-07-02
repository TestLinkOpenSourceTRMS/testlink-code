<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource  gforgesoapInterface.class.php
 * @author Francisco Mancardi
 *
 *
 * @internal IMPORTANT NOTICE
 *       we use issueID on methods signature, to make clear that this ID 
 *       is HOW issue in identified on Issue Tracker System, 
 *       not how is identified internally at DB level on TestLink
 *
 * @internal revisions
 * @since 1.9.10
 *
**/
class gforgesoapInterface extends issueTrackerInterface
{

  protected $APIClient;
  protected $authToken;
  protected $statusDomain = array();
  protected $l18n;
  protected $labels = array('duedate' => 'its_duedate_with_separator');
  
  private $soapOpt = array("connection_timeout" => 1, 'exceptions' => 1);
  
  /**
   * Construct and connect to BTS.
   *
   * @param str $type (see tlIssueTracker.class.php $systems property)
   * @param xml $cfg
   **/
  function __construct($type,$config,$name)
  {
    $this->name = $name;
    $this->interfaceViaDB = false;
    if( !$this->setCfg($config) )
    {
      return false;
    }  
    
    $this->completeCfg();
    $this->connect();
  }

  /**
   *
   * check for configuration attributes than can be provided on
   * user configuration, but that can be considered standard.
   * If they are MISSING we will use 'these carved on the stone values' 
   * in order to simplify configuration.
   *
   *
   **/
  function completeCfg()
  {
    $base = trim($this->cfg->uribase,"/") . '/' ;
      if( !property_exists($this->cfg,'uriwsdl') )
      {
        $this->cfg->uriwsdl = $base . 'gf/xmlcompatibility/soap5/?wsdl';
    }
    
      if( !property_exists($this->cfg,'uriview') )
      {
        $this->cfg->uriview = $base . 'browse/';
    }
      
      if( !property_exists($this->cfg,'uricreate') )
      {
        $this->cfg->uricreate = $base . 'gf/';
    }     
  }


    function getAuthToken()
    {
    return $this->authToken;
    }


  /**
   * status code (integer) for issueID 
   *
   * 
   * @param string issueID
   *
   * @return 
   **/
  public function getIssueStatusCode($issueID)
  {
    $issue = $this->getIssue($issueID);
    return !is_null($issue) ? $issue->statusCode : false;
  }

    
  /**
   * Returns status in a readable form (HTML context) for the bug with the given id
   *
   * @param string issueID
   * 
   * @return string 
   *
   **/
  function getIssueStatusVerbose($issueID)
  {
        $issue = $this->getIssue($issueID);
    return !is_null($issue) ? $issue->statusVerbose : null;
  }
  
  
  /**
   *
   * @param string issueID
   * 
   * @return string returns the bug summary if bug is found, else null
   **/
    function getIssueSummary($issueID)
    {
        $issue = $this->getIssue($issueID);
    return !is_null($issue) ? $issue->summary : null;
    }
  
    /**
     * @internal precondition: TestLink has to be connected to BTS 
     *
   * @param string issueID
     *
     **/
    function getIssue($issueID)
    {
        try
        {
            $issue = $this->APIClient->getTrackerItemFull($this->authToken, $issueID);
            new dBug($issue);
            
            echo 'QTY extra_field_data:' . count($issue->extra_field_data) . '<br>';
            $target = array();
            $dataID = array();
            foreach($issue->extra_field_data as $efd)
            {
              $target[] = $efd->tracker_extra_field_id;
              $dataID[] = $efd->tracker_extra_field_data_id;
            }
            
            $extraFields = $this->APIClient->getTrackerExtraFieldArray($this->authToken, 
                                                $issue->tracker_id, $target);
                                                
            new dBug($extraFields);     
      $idx=0;
      foreach($extraFields as $ef)
      {
        if($ef->field_name == 'Status')
        {
          echo 'Status FOUND on idx=' . $idx . '<br>';
          $ef->tracker_extra_field_data_id = $dataID[$idx];
          $statusObj = $ef;
          break;
        }
        $idx++;
      }
            new dBug($statusObj);
            
            $zz = $this->APIClient->getTrackerExtraField($this->authToken, 
                                          $issue->tracker_id, 
                                          $statusObj->tracker_extra_field_id);

      new dBug($zz);
            
            //$yy = $this->APIClient->getTrackerExtraFieldElementArray($this->authToken, 
            //                                    $issue->tracker_id, (array)$statusObj->tracker_extra_field_id);

            // new dBug($yy);
            // echo $statusObj->tracker_extra_field_data_id;
            // $yy = $this->APIClient->getTrackerExtraFieldData($this->authToken,$issue->tracker_item_id,87191);
      //                         // $statusObj->tracker_extra_field_data_id);

      echo $this->authToken . '<br>';
      // echo '$issue->tracker_item_id:' . $issue->tracker_item_id . '<br>';
      // echo '$statusObj->tracker_extra_field_data_id:' . $statusObj->tracker_extra_field_data_id  . '<br>';
      //echo '$statusObj->tracker_extra_field_id:' . $statusObj->tracker_extra_field_id  . '<br>';
      //echo '<br>';
            //$yy = $this->APIClient->getTrackerExtraFieldDatas($this->authToken,$issue->tracker_item_id,
      //                         $statusObj->tracker_extra_field_id);

      // tracker_item_id:8305
      // tracker_extra_field_id:55108
      // tracker_extra_field_data_id:87191
            $yy = $this->APIClient->getTrackerExtraFieldDatas($this->authToken,8305,55108);
            echo __LINE__;
            new dBug($yy);

            // getTrackerExtraField(string sessionhash, int tracker_id, int tracker_extra_field_id)

            
            // We are going to have a set of standard properties
            $issue->IDHTMLString = "<b>{$issueID} : </b>";
            $issue->statusCode = $issue->status_id;
            $issue->statusVerbose = array_search($issue->statusCode, $this->statusDomain);
      $issue->statusHTMLString = $this->buildStatusHTMLString($issue->statusCode);
      $issue->summaryHTMLString = $this->buildSummaryHTMLString($issue);
        }
        catch (Exception $e)
        {
          tLog("JIRA Ticket ID $issueID - " . $e->getMessage(), 'WARNING');
      $issue = null;
        }
        
        return $issue;
    }


    /**
     * checks id for validity
     *
   * @param string issueID
     *
     * @return bool returns true if the bugid has the right format, false else
     **/
    function checkBugIDSyntax($issueID)
    {
      return $this->checkBugIDSyntaxString($issueID);
    }

    /**
   * @param string issueID
     *
     * @return bool true if issue exists on BTS
     **/
    function checkBugIDExistence($issueID)
    {
        if(($status_ok = $this->checkBugIDSyntax($issueID)))
        {
            $issue = $this->getIssue($issueID);
            $status_ok = !is_null($issue);
        }
        return $status_ok;
    }

    /**
     * establishes connection to the bugtracking system
     *
     * @return bool returns true if the soap connection was established and the
     * wsdl could be downloaded, false else
     *
     **/
    function connect()
    {
    $this->interfaceViaDB = false;
    $op = $this->getClient(array('log' => true));
    if( ($this->connected = $op['connected']) )
    { 
      // OK, we have got WSDL => server is up and we can do SOAP calls, but now we need 
      // to do a simple call with user/password only to understand if we are really connected
      try
      {
        $this->APIClient = $op['client'];
              $this->authToken = $this->APIClient->login((string)$this->cfg->username, (string)$this->cfg->password);

              $this->l18n = init_labels($this->labels);
      }
      catch (SoapFault $f)
      {
        $this->connected = false;
        tLog(__CLASS__ . " - SOAP Fault: (code: {$f->faultcode}, string: {$f->faultstring})","ERROR");
      }
    }
        return $this->connected;
    }

    /**
     * 
     *
     **/
  function isConnected()
  {
    return $this->connected;
  }


    /**
     * 
     *
     **/
  function getClient($opt=null)
  {
    // IMPORTANT NOTICE - 2012-01-06 - If you are using XDEBUG, Soap Fault will not work
    $res = array('client' => null, 'connected' => false, 'msg' => 'generic ko');
    $my['opt'] = array('log' => false);
    $my['opt'] = array_merge($my['opt'],(array)$opt);
    
    try
    {
      // IMPORTANT NOTICE
      // $this->cfg is a simpleXML object, then is ABSOLUTELY CRITICAL 
      // DO CAST any member before using it.
      // If we do following call WITHOUT (string) CAST, SoapClient() fails
      // complaining '... wsdl has to be an STRING or null ...'
      $res['client'] = new SoapClient((string)$this->cfg->uriwsdl,$this->soapOpt);
      $res['connected'] = true;
      $res['msg'] = 'iupi!!!';
    }
    catch (SoapFault $f)
    {
      $res['connected'] = false;
      $res['msg'] = "SOAP Fault: (code: {$f->faultcode}, string: {$f->faultstring})";
      if($my['opt']['log'])
      {
        tLog("SOAP Fault: (code: {$f->faultcode}, string: {$f->faultstring})","ERROR");
      } 
    }
    return $res;
  } 




    /**
     *
     * @author francisco.mancardi@gmail.com>
     **/
    private function helperParseDate($date2parse)
    {
      $ret = null;
        if (!is_null($date2parse))
        {
            $ret = date_parse($date2parse);
            $ret = ((gmmktime(0, 0, 0, $ret['month'], $ret['day'], $ret['year'])));
            $ret = $this->l18n['duedate'] . gmstrftime("%d %b %Y",($ret));
        }
        return $ret ;
    }



    /**
     *
     * @author francisco.mancardi@gmail.com>
     **/
  public static function getCfgTemplate()
    {
    $template = "<!-- Template " . __CLASS__ . " -->\n" .
          "<issuetracker>\n" .
          "<username>GFORGE LOGIN NAME</username>\n" .
          "<password>GFORGE PASSWORD</password>\n" .
          "<uribase>http://gforge.org/</uribase>\n" .
          "</issuetracker>\n";
    return $template;
    }

  
    /**
     *
     * @author francisco.mancardi@gmail.com>
     **/
  public function getStatusDomain()
    {
    return $this->statusDomain;
    }

  /**
   *
   **/
  function buildStatusHTMLString($statusCode)
  {
    $str = array_search($statusCode, $this->statusDomain);
    if (strcasecmp($str, 'closed') == 0 || strcasecmp($str, 'resolved') == 0 )
        {
                $str = "<del>" . $str . "</del>";
        }
        return "[{$str}] ";
  }

  /**
   *
   **/
    function buildSummaryHTMLString($issue)
    {
    $summary = $issue->summary;
        $strDueDate = $this->helperParseDate($issue->duedate);
        if( !is_null($strDueDate) )
        { 
          $summary .= "<b> [$strDueDate] </b> ";
        }
        return $summary;
    }

  public static function checkEnv()
  {
    $ret = array();
    $ret['status'] = extension_loaded('soap');
    $ret['msg'] = $ret['status'] ? 'OK' : 'You need to enable SOAP extension';
    return $ret;
  }


/*    
getTrackerItem() 

$issue (object)
tracker_id      371
tracker_item_id   7091
status_id       0
priority      3
submitted_by    14030
open_date       2011-03-31 03:00:00
close_date      2011-04-07 03:00:00
summary       Add support for Gforge6
details       Add support for Gforge As 6.0 (hierarchical tracker items)
last_modified_date  2011-05-26 20:31:40
last_modified_by  14030
sort_order      0
parent_id       0
has_subitems    [empty string]
subitems_count    0    
*/
    
}
?>