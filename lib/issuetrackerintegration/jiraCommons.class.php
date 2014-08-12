<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	jiraCommons.class.php
 * @author Francisco Mancardi
 * @since 1.9.6
 *
 *
 * @internal revisions
**/
class jiraCommons
{

  protected $statusDomain = array();
  protected $l18n;
  protected $labels = array('duedate' => 'its_duedate_with_separator');
  
  var $defaultResolvedStatus;
  var $guiCfg;
  

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
      $status_ok = !is_null($issue) && is_object($issue);
    }
    return $status_ok;
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
   **/
  function buildStatusHTMLString($statusCode)
  {
    $str = $statusCode;
    if($this->guiCfg['use_decoration'])
    {
      $str = "[" . $str . "] ";	
    }
    return $str;
  }

  /**
   *
   **/
  function buildSummaryHTMLString($issue)
  {
    $summary = $issue->summary;
    if(property_exists($issue, 'duedate'))
    {
      $strDueDate = $this->helperParseDate($issue->duedate);
      if( !is_null($strDueDate) )
      { 
        $summary .= "<b> [$strDueDate] </b> ";
      }
    }  
    return $summary;
  }
  
  /**
   *
   **/
  function initDefaultResolvedStatus($statusDomain)
  {          
    $domain = array();
    $itemSet = array('Resolved','Closed'); // Unfortunately case is important
    foreach($itemSet as $st)
    {
      $domain[] = array('code' => $statusDomain[$st], 'verbose' => $st);  
    }                 
    return $domain;
  }
}