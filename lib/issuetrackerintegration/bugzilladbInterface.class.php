<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource  bugzilladbInterface.class.php
 * @author Francisco Mancardi
 *
 *
 * @internal revisions
 * @since 1.9.10
**/

class bugzilladbInterface extends issueTrackerInterface
{

  /**
   * Construct and connect to BTS.
   *
   * @param str $type (see tlIssueTracker.class.php $systems property)
   * @param xml $cfg
   **/
  function __construct($type,$config,$name)
  {
    parent::__construct($type,$config,$name); 
    if( $this->connected )
    { 
      // For bugzilla status code is not important.
      // Design Choice make it equal to verbose. Important bugzilla uses UPPERCASE 
      $this->defaultResolvedStatus = array();
      $this->defaultResolvedStatus[] = array('code' => 'RESOLVED', 'verbose' => 'RESOLVED');
      $this->defaultResolvedStatus[] = array('code' => 'VERIFIED', 'verbose' => 'VERIFIED');
      $this->defaultResolvedStatus[] = array('code' => 'CLOSED', 'verbose' => 'CLOSED');
      
      $this->setResolvedStatusCfg();
      
      
      $this->interfaceViaDB = true;
      $this->guiCfg = array('use_decoration' => true); // add [] on summary
      $this->methodOpt['buildViewBugLink'] = array('addSummary' => true, 'colorByStatus' => false);
    }
  }



  function getissue($id)
  {
    if (!$this->isConnected())
    {
      return null;
    }
    
    $sql = " SELECT bug_id AS id,short_desc AS summary,bug_status AS status" .
           " FROM " . ( !is_null($this->cfg->dbschema) ? " {$this->cfg->dbschema}.bugs " : 'bugs') .
           " WHERE bug_id = '{$id}' ";
    $rs = $this->dbConnection->fetchRowsIntoMap($sql,'id');
    $issue = null;

    if( !is_null($rs) ) 
    {
      $issue = new stdClass();

      $issue->id = $id;                      // useful on spreadsheet export
      $issue->summary = $rs[$id]['summary']; // useful on spreadsheet export
      
      $issue->IDHTMLString = "<b>{$id} : </b>";
      $issue->statusCode = $issue->statusVerbose = $rs[$id]['status']; 
      $issue->statusHTMLString = $this->buildStatusHTMLString($issue->statusVerbose);
      $issue->statusColor = isset($this->status_color[$issue->statusVerbose]) ? 
      $this->status_color[$issue->statusVerbose] : 'white';
  
      $issue->summaryHTMLString = $rs[$id]['summary'];
      $issue->isResolved = isset($this->resolvedStatus->byCode[$issue->statusCode]); 
      
      
    }
    return $issue;  
  }


  /**
   * Returns the status of the bug with the given id
   * this function is not directly called by TestLink. 
   *
   * @return string returns the status of the given bug (if found in the db), or null else
   **/
  function getBugStatus($id)
  {
    if (!$this->isConnected())
    {
      return null;
    }
    $issue = $this->getIssue($id);
    
    return is_null($issue) ? $issue : $issue->statusVerbose;
  }
  
  
  /**
   * checks is bug id is present on BTS
   * 
   * @return integer returns 1 if the bug with the given id exists 
   **/
  function checkBugIDExistence($id)
  {
    $status_ok = 0; 
    $issue = $this->getIssue($id);
    
    return !is_null($issue) ? 1 : 0; 
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
     * @author francisco.mancardi@gmail.com>
     **/
  function getMyInterface()
  {
    return $this->cfg->interfacePHP;
  }


    /**
     *
     * @author francisco.mancardi@gmail.com>
     **/
  public static function getCfgTemplate()
  {
    
    $template = "<!-- Template " . __CLASS__ . " -->\n" .
                "<issuetracker>\n" .
                "<dbhost>DATABASE SERVER NAME</dbhost>\n" .
                "<dbname>DATABASE NAME</dbname>\n" .
                "<dbschema>DATABASE NAME</dbschema>\n" .
                "<dbtype>mysql</dbtype>\n" .
                "<dbuser>USER</dbuser>\n" .
                "<dbpassword>PASSWORD</dbpassword>\n" .
                "<uricreate>http://[bugzillaserver]/bugzilla/</uricreate>\n" .
                "<uriview>http://[bugzillaserver]/bugzilla/show_bug.cgi?id=</uriview>\n" .
                "</issuetracker>\n";
    return $template;
  }


}