<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource  issueTrackerInterface.php
 *
 * Base class for connection to issue tracking interfaces
 * For supporting a bug/issue tracking system this class has to be extended,
 * and all customization should be done in the subclass. 
 *
 * ============= Issue Entity properties on TestLink Context ===================
 *
 * IDHTMLString = string
 * statusCode = can be integer,string depending of ITS
 * statusVerbose = string human readable what user see on ITS GUI
 * statusHTMLString = string can contain additional info ready for HTML
 * summary = string what user see on ITS GUI
 * summaryHTMLString = can contain additional info ready for HTML
 *
 * other properties can be present depending on ITS.
 * =============================================================================
 *
 * @internal revisions
 * @since 1.9.10
 *
 *
**/
require_once(TL_ABS_PATH . "/lib/functions/database.class.php");
require_once(TL_ABS_PATH . "/lib/functions/lang_api.php");

abstract class issueTrackerInterface
{
  // members to store the bugtracking information.
  // Values are set in the actual subclasses
  var $cfg = null;  // simpleXML object
  var $name = null;

  var $tlCharSet = null;
  
  // private vars don't touch
  var $dbConnection = null;  // usable only if interface is done via direct DB access.
  var $dbMsg = '';
  var $connected = false;
  var $interfaceViaDB = false;  // useful for connect/disconnect methods
  var $resolvedStatus;
  
  var $methodOpt = array('buildViewBugLink' => array('addSummary' => false, 'colorByStatus' => false));
  var $guiCfg = array();
  
  /**
   * Construct and connect to BTS.
   * Can be overloaded in specialized class
   *
   * @param str $type (see tlIssueTracker.class.php $systems property)
   **/
  function __construct($type,$config,$name)
  {
    $this->tlCharSet = config_get('charset');
    $this->guiCfg = array('use_decoration' => true); // add [] on summary and statusHTMLString
    $this->name = $name;

    if( $this->setCfg($config) )
    {
      // useful only for integration via DB
      if( !property_exists($this->cfg,'dbcharset') )
      {
        $this->cfg->dbcharset = $this->tlCharSet;
      }
      $this->connect();
    }
    else
    {
      $this->connected = false;
    }
  }

  /**
   *
   **/
  function canCreateViaAPI()
  {
    return true;
  }


  /**
   *
   **/
  function getCfg()
  {
    return $this->cfg;
  }

  /**
   *
   **/
  function setCfg($xmlString)
  {
    $msg = null;
    $signature = 'Source:' . __METHOD__;

    // check for empty string
    if(strlen(trim($xmlString)) == 0)
    {
      // Bye,Bye
      $msg = " - Issue tracker:$this->name - XML Configuration seems to be empty - please check";
      tLog(__METHOD__ . $msg, 'ERROR');  
      return false;
    }
      
    $xmlCfg = "<?xml version='1.0'?> " . $xmlString;
    libxml_use_internal_errors(true);
    try 
    {
      $this->cfg = simplexml_load_string($xmlCfg);
      if (!$this->cfg) 
      {
        $msg = $signature . " - Failure loading XML STRING\n";
        foreach(libxml_get_errors() as $error) 
        {
          $msg .= "\t" . $error->message;
        }
      }
    }
    catch(Exception $e)
    {
      $msg = $signature . " - Exception loading XML STRING\n";
      $msg .= 'Message: ' .$e->getMessage();
    }

    if( !($retval = is_null($msg)) )
    {
      tLog(__METHOD__ . $msg, 'ERROR');  
    }  
    return $retval;
  }

  /**
   *
   **/
  function getMyInterface()
  {
    return $this->cfg->interfacePHP;
  }

  /**
   * return the maximum length in chars of a issue id
   * used on TestLink GUI
   *
   * @return int the maximum length of a bugID
   */
  function getBugIDMaxLength()
  {
    // CRITIC: 
    // related to execution_bugs table, you can not make it
    // greater WITHOUT changing table structure.  
    // 
    return 64;  
  }

  
  /**
   * establishes the database connection to the bugtracking system
   *
   * @return bool returns true if the db connection was established and the
   * db could be selected, false else
   *
   **/
  function connect()
  {
    if (is_null($this->cfg->dbhost) || is_null($this->cfg->dbuser))
    {
      return false;
    }
       
    $this->cfg->dbtype = strtolower((string)$this->cfg->dbtype);
    $this->dbConnection = new database($this->cfg->dbtype);
    $result = $this->dbConnection->connect(false, $this->cfg->dbhost,$this->cfg->dbuser,
                         $this->cfg->dbpassword, $this->cfg->dbname);

    if (!$result['status'])
    {
      $this->dbConnection = null;
      $connection_args = "(interface: - Host:$this->cfg->dbhost - " . 
                         "DBName: $this->cfg->dbname - User: $this->cfg->dbuser) "; 
      $msg = sprintf(lang_get('BTS_connect_to_database_fails'),$connection_args);
      tLog($msg  . $result['dbms_msg'], 'ERROR');
    }
    elseif ($this->cfg->dbtype == 'mysql')
    {
      if ($this->cfg->dbcharset == 'UTF-8')
      {
        $r = $this->dbConnection->exec_query("SET CHARACTER SET utf8");
        $r = $this->dbConnection->exec_query("SET NAMES utf8");
        $r = $this->dbConnection->exec_query("SET collation_connection = 'utf8_general_ci'");
      }
      else
      {
        $r = $this->dbConnection->exec_query("SET CHARACTER SET " . $this->cfg->dbcharset);
        $r = $this->dbConnection->exec_query("SET NAMES ". $this->cfg->dbcharset);
      }
    }

    $this->connected = $result['status'] ? true : false;

    return $this->connected;
  }
  
  /**
   * State of connection to BTS
   *
   * @return bool returns true if connection with BTS is established, false else
   *
   **/
  function isConnected()
  {
  
    return ($this->connected && 
        ((!$this->interfaceViaDB ) || is_object($this->dbConnection)) ? 1 : 0);
  }

  /**
   * Closes the db connection (if any)
   *
   **/
  function disconnect()
  {
    if ($this->isConnected() && $this->interfaceViaDB)
    {
      $this->dbConnection->close();
    }
    $this->connected = false;
    $this->dbConnection = null;
  }



  /**
   * checks a issue id for validity (NUMERIC)
   *
   * @return bool returns true if the bugid has the right format, false else
   **/
  function checkBugIDSyntaxNumeric($issueID)
  {
    $valid = true;  
    $forbidden_chars = '/\D/i';  
    if (preg_match($forbidden_chars, $issueID))
    {
      $valid = false; 
    }
    else 
    {
      $valid = (intval($issueID) > 0);  
    }
    return $valid;
  }

  /**
   * checks id for validity (STRING)
   *
   * @param string issueID
   *
   * @return bool returns true if the bugid has the right format, false else
   **/
  function checkBugIDSyntaxString($issueID)
  {
    $status_ok = !(trim($issueID) == "");
    if($status_ok)
    {
      $forbidden_chars = '/[!|�%&()\/=?]/';
      if (preg_match($forbidden_chars, $issueID))
      {
        $status_ok = false;
      }
    }
    return $status_ok;
  }


  /**
   * default implementation for generating a link to the bugtracking page for viewing
   * the bug with the given id in a new page
   *
   * @param int id the bug id
   *
   * @return string returns a complete HTML HREF to view the bug (if found in db)
   *
   **/
  function buildViewBugLink($issueID, $opt=null)
  {
    $my['opt'] = $this->methodOpt[__FUNCTION__];
    $my['opt'] = array_merge($my['opt'],(array)$opt);

    $link = "<a href='" . $this->buildViewBugURL($issueID) . "' target='_blank'>";

    $issue = $this->getIssue($issueID);
    
    if( is_null($issue) || !is_object($issue) )
    {
      $link = '';
      return $link;
    }
    
    $useIconv = property_exists($this->cfg,'dbcharset');
    if($useIconv)
    {
      $link .= iconv((string)$this->cfg->dbcharset,$this->tlCharSet,$issue->IDHTMLString);
    }
    else
    {
      $link .= $issue->IDHTMLString;
    }
    
    if (!is_null($issue->statusHTMLString))
    {
      if($useIconv)
      {
        $link .= iconv((string)$this->cfg->dbcharset,$this->tlCharSet,$issue->statusHTMLString);
      }
      else
      {
        $link .= $issue->statusHTMLString;
      }
    }
    else
    {
      $link .= $issueID;
    }

    if($my['opt']['addSummary'])
    {
      if (!is_null($issue->summaryHTMLString))
      {
        $link .= " : ";
        if($useIconv)
        {
          $link .= iconv((string)$this->cfg->dbcharset,$this->tlCharSet,$issue->summaryHTMLString);
        }
        else
        {
          $link .= $issue->summaryHTMLString;
        }
      }
    }
    $link .= "</a>";

    if($my['opt']['colorByStatus'] && property_exists($issue,'statusColor') )
    {
      $title = lang_get('access_to_bts');  
      $link = "<div  title=\"{$title}\" style=\"display: inline; background: $issue->statusColor;\">$link</div>";
    }
    
    $ret = new stdClass();
    $ret->link = $link;
    $ret->isResolved = $issue->isResolved;

    if( isset($my['opt']['raw']) && !is_null(isset($my['opt']['raw'])) )
    {
      foreach($my['opt']['raw'] as $attr)
      {
      	if(property_exists($issue, $attr))
      	{
          $ret->$attr = $issue->$attr;
      	}
      }  
    }
    return $ret;
  }

  /**
   * returns the URL which should be displayed for entering bugs
   *
   * @return string returns a complete URL
   *
   **/
  function getEnterBugURL()
  {
    return $this->cfg->uricreate;
  }


  /**
   * Returns URL to the bugtracking page for viewing ticket
   *
   * @param mixed issueID 
   *        depending of BTS issueID can be a number (e.g. Mantis)
   *        or a string (e.g. JIRA)
   * 
   * @return string 
   **/
  function buildViewBugURL($issueID)
  {
    return $this->cfg->uriview . urlencode($issueID);
  }

  
  /**
   * status code (always integer??) for issueID 
   *
   * @param issueID  according to BTS can be number or string
   *
   * @return 
   **/
  public function getIssueStatusCode($issueID)
  {
    $issue = $this->getIssue($issueID);
    return (!is_null($issue) && is_object($issue))? $issue->statusCode : false;
  }


  /**
   * Returns status in a readable form (HTML context) for the bug with the given id
   *
   * @param issueID  according to BTS can be number or string
   * 
   * @return string 
   *
   **/
  function getIssueStatusVerbose($issueID)
  {
    $issue = $this->getIssue($issueID);
    return (!is_null($issue) && is_object($issue))? $issue->statusVerbose : false;
  }
  


  /**
   *
   * @param issueID  according to BTS can be number or string
   * 
   * @return string returns the bug summary if bug is found, else null
   **/
  function getIssueSummary($issueID)
  {
    $issue = $this->getIssue($issueID);
    return (!is_null($issue) && is_object($issue))? $issue->summary : null;
  }


  // How to Force Extending class to define this STATIC method ?
  // KO abstract public static function getCfgTemplate();
  public static function getCfgTemplate() 
  {
    throw new RuntimeException("Unimplemented - YOU must implement it in YOUR interface Class");
  }

  /**
   *
   **/
  public static function checkEnv()
  {
    $ret = array();
    $ret['status'] = true;
    $ret['msg'] = 'OK';
    return $ret;
  }


  /**
   *
   **/
  public function setResolvedStatusCfg()
  {
    if( property_exists($this->cfg,'resolvedstatus') )
    {
      $statusCfg = (array)$this->cfg->resolvedstatus;
    }
    else
    {
      $statusCfg['status'] = $this->defaultResolvedStatus;
    }
    $this->resolvedStatus = new stdClass();
    foreach($statusCfg['status'] as $cfx)
    {
      $e = (array)$cfx;
      $this->resolvedStatus->byCode[$e['code']] = $e['verbose'];
    }
    $this->resolvedStatus->byName = array_flip($this->resolvedStatus->byCode);
  }
  
  /**
   *
   **/
  public function getResolvedStatusCfg()
  {
    return $this->resolvedStatus;
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
   * @param issueID (can be number of string according to specific BTS)
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

 
}