<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource  mantisdbInterface.class.php
 * @since 1.9.4
 *
 * @internal revision
 * @since 1.9.10
 *
**/
class mantisdbInterface extends issueTrackerInterface
{
  private $code_status = array(10 => 'new',20 => 'feedback',30 => 'acknowledged',
                               40 => 'confirmed',50 => 'assigned',80 => 'resolved',90 => 'closed');
                              
  private $status_color = array('new'          => '#ffa0a0', # red,
                                'feedback'     => '#ff50a8', # purple
                                'acknowledged' => '#ffd850', # orange
                                'confirmed'    => '#ffffb0', # yellow
                                'assigned'     => '#c8c8ff', # blue
                                'resolved'     => '#cceedd', # buish-green
                                'closed'       => '#e8e8e8'); # light gray

  var $defaultResolvedStatus;


  /**
   * Construct and connect to BTS.
   *
   * @param str $type (see tlIssueTracker.class.php $systems property)
   * @param xml $cfg
   **/
  function __construct($type,$config,$name)
  {
    
    parent::__construct($type,$config,$name);
    if( !$this->isConnected() )
    {
      return false;
    } 

    $this->interfaceViaDB = true;
    $this->defaultResolvedStatus = array();
    $this->defaultResolvedStatus[] = array('code' => 80, 'verbose' => 'resolved');
    $this->defaultResolvedStatus[] = array('code' => 90, 'verbose' => 'closed');
    
    $this->setResolvedStatusCfg();
    
    $this->methodOpt['buildViewBugLink'] = array('addSummary' => true, 'colorByStatus' => true);
    $this->guiCfg = array('use_decoration' => true);
    if( property_exists($this->cfg, 'statuscfg') )
    {
      $this->setStatusCfg();
    }
  }

  
  /**
   * Return the URL to the bugtracking page for viewing 
   * the bug with the given id. 
   *
   * @param int id the bug id
   * 
   * @return string returns a complete URL to view the bug
   **/
  function buildViewBugURL($id)
  {
    return $this->cfg->uriview . urlencode($id);
  }

  /**
   *
   **/
  function getIssue($id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    if (!$this->isConnected())
    {
      return false;
    }
    $sql = "/* $debugMsg */ SELECT id,status,summary FROM mantis_bug_table " .
           " WHERE id=" . intval($id);
    
    $rs = $this->dbConnection->fetchRowsIntoMap($sql,'id');
    $issue = null;
    if( !is_null($rs) ) 
    {
      $issueOnMantisDB = current($rs);
      $issue = new stdClass();
      $issue->IDHTMLString = "<b>{$id} : </b>";
      $issue->summaryHTMLString = $issueOnMantisDB['summary'];
	  $issue->id = $issueOnMantisDB['id'];
      $issue->summary = $issueOnMantisDB['summary'];
      $issue->statusCode = $issueOnMantisDB['status']; 
      $issue->isResolved = isset($this->resolvedStatus->byCode[$issue->statusCode]); 

      if( isset($this->code_status[$issue->statusCode]) )
      {
        $issue->statusVerbose = $this->code_status[$issue->statusCode];
      }
      else
      {
        // give info to user on Event Viewer
        $msg = lang_get('MANTIS_status_not_configured');
        $msg = sprintf($msg,$issueOnMantisDB['status']);
        logWarningEvent($msg,"MANTIS INTEGRATION");
        $issue->statusVerbose = 'custom_undefined_on_tl';
      } 

      $issue->statusHTMLString = $this->buildStatusHTMLString($issue->statusVerbose);
      $issue->statusColor = isset($this->status_color[$issue->statusVerbose]) ? 
      $this->status_color[$issue->statusVerbose] : 'white';
      
    }
    return $issue;  
  }


  
  /**
   * Returns the status of the bug with the given id
   * this function is not directly called by TestLink. 
   *
   * @return string returns the status of the given bug (if found in the db), or false else
   **/
  function getBugStatus($id)
  {
    if (!$this->isConnected())
    {
      return false;
    }
    $issue = $this->getIssue($id);
    return (!is_null($issue) && $issue) ? $issue->statusVerbose : null;
  }

 
    /**
   * checks is bug id is present on BTS
   * 
   * @return integer returns 1 if the bug with the given id exists 
   **/
  function checkBugIDExistence($id)
  {
    $status_ok = 0; 
    $query = "SELECT status FROM mantis_bug_table WHERE id='" . $id ."'";
    $result = $this->dbConnection->exec_query($query);
    if ($result && ($this->dbConnection->num_rows($result) == 1))
    {
      $status_ok = 1;    
    }
    return $status_ok;
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
    return $this->checkBugIDSyntaxNumeric($issueID);
  }

  /**
   *
   *
   **/
  function buildStatusHTMLString($statusVerbose)
  {
    $str = '';
    if ($statusVerbose !== false)
    {
      // status values depends on your mantis configuration at config_inc.php in $g_status_enum_string, 
      // below is the default:
      //'10:new,20:feedback,30:acknowledged,40:confirmed,50:assigned,80:resolved,90:closed'
      // With this replace if user configure status on mantis with blank we do not have problems
      //
      $tlStatus = str_replace(" ", "_", $statusVerbose);
      $str = lang_get('issue_status_' . $tlStatus);
      if($this->guiCfg['use_decoration'])
      {
        $str = "[" . $str . "] "; 
      }
    }
    return $str;
  }


  function getMyInterface()
  {
    return $this->cfg->interfacePHP;
  }



  public static function getCfgTemplate()
  {
    
    $template = "<!-- Template " . __CLASS__ . " -->\n" .
                "<issuetracker>\n" .
                "<dbhost>DATABASE SERVER NAME</dbhost>\n" .
                "<dbname>DATABASE NAME</dbname>\n" .
                "<dbtype>mysql</dbtype>\n" .
                "<dbuser>USER</dbuser>\n" .
                "<dbpassword>PASSWORD</dbpassword>\n" .
                "<uriview>http://localhost:8080/development/mantisbt-1.2.5/view.php?id=</uriview>\n" .
                "<uricreate>http://localhost:8080/development/mantisbt-1.2.5/</uricreate>\n" .
                "<!-- Optional -->\n" .
                "<statuscfg>\n" .
                "<status><code>10</code><verbose>new</verbose><color>#ffa0a0</color></status>\n" .
                "<status><code>20</code><verbose>feedback</verbose><color>#ff50a8</color></status>\n" .
                "<status><code>30</code><verbose>acknowledged</verbose><color>#ffd850</color></status>\n" .
                "<status><code>40</code><verbose>confirmed</verbose><color>#ffffb0</color></status>\n" .
                "<status><code>50</code><verbose>assigned</verbose><color>#c8c8ff</color></status>\n" .
                "<status><code>80</code><verbose>resolved</verbose><color>#cceedd</color></status>\n" .
                "<status><code>90</code><verbose>closed</verbose><color>#e8e8e8</color></status>\n" .
                "</statuscfg>\n" . 
                "<!-- Configure This if you want NON STANDARD BEHAIVOUR for considered issue resolved -->\n" .
                "<resolvedstatus>\n" .
                "<status><code>80</code><verbose>resolved</verbose></status>\n" .
                "<status><code>90</code><verbose>closed</verbose></status>\n" .
                "</resolvedstatus>\n" .
                "</issuetracker>\n";
                "</issuetracker>\n";
    return $template;
  }

  public function setStatusCfg()
  {
    $statusCfg = (array)$this->cfg->statuscfg;
    foreach($statusCfg['status'] as $cfx)
    {
      $e = (array)$cfx;
      $this->code_status[$e['code']] = $e['verbose'];
      $this->status_color[$e['verbose']] = $e['color'];
    }
  }


  public function getCodeStatus()
  {
      return $this->code_status;
  }

  public function getStatusColor()
  {
      return $this->status_color;
  }

}
?>