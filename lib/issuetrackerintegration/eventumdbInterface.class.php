<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource  eventumdbInterface.class.php
 * @author Ingo van Lil
**/

class eventumdbInterface extends issueTrackerInterface
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
    if ($this->isConnected())
    {
      $this->interfaceViaDB = true;
      $this->guiCfg = array('use_decoration' => true);
      $this->methodOpt['buildViewBugLink'] = array('addSummary' => true, 'colorByStatus' => true);
    }
  }



  function getIssue($id)
  {
    if (!$this->isConnected())
    {
      return null;
    }

    $sql = "SELECT iss_id AS id, iss_summary, sta_abbreviation, sta_title, sta_color, sta_is_closed " .
           "FROM {$this->cfg->tableprefix}issue " .
           "JOIN {$this->cfg->tableprefix}status ON sta_id = iss_sta_id " .
           "WHERE iss_id = {$id}";
    $rs = $this->dbConnection->fetchRowsIntoMap($sql, 'id');
    $issue = null;

    if (!is_null($rs))
    {
      $issue = new stdClass();

      $issue->id = $id;
      $issue->summary = $rs[$id]['iss_summary'];

      $issue->IDHTMLString = "<b>{$id} : </b>";
      $issue->statusCode = $rs[$id]['sta_abbreviation'];
      $issue->statusVerbose = $rs[$id]['sta_title'];
      $issue->statusHTMLString = $this->buildStatusHTMLString($issue->statusVerbose);
      $issue->statusColor = $rs[$id]['sta_color'];

      $issue->summaryHTMLString = $rs[$id]['iss_summary'];
      $issue->isResolved = $rs[$id]['sta_is_closed'];
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
   * @author francisco.mancardi@gmail.com>
   **/
  function getMyInterface()
  {
    return $this->cfg->interfacePHP;
  }


  /**
   * @author francisco.mancardi@gmail.com>
   **/
  public static function getCfgTemplate()
  {
    $template = "<!-- Template " . __CLASS__ . " -->\n" .
                "<issuetracker>\n" .
                "<dbhost>DATABASE SERVER NAME</dbhost>\n" .
                "<dbname>DATABASE NAME</dbname>\n" .
                "<tableprefix>eventum_</tableprefix>\n" .
                "<dbtype>mysql</dbtype>\n" .
                "<dbuser>USER</dbuser>\n" .
                "<dbpassword>PASSWORD</dbpassword>\n" .
                "<uriview>http://[[eventumserver]]/view.php?id=</uriview>\n" .
                "</issuetracker>\n";
    return $template;
  }

}
