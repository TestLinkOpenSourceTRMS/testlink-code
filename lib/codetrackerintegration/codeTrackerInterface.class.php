<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource  codeTrackerInterface.php
 *
 * Base class for connection to code tracking interfaces
 * For supporting a code tracking system this class has to be extended,
 * and all customization should be done in the subclass. 
 *
 * ============= Issue Entity properties on TestLink Context ===================
 *
 * IDHTMLString = string
 * statusCode = can be integer,string depending of CTS
 * statusVerbose = string human readable what user see on CTS GUI
 * statusHTMLString = string can contain additional info ready for HTML
 * summary = string what user see on CTS GUI
 * summaryHTMLString = can contain additional info ready for HTML
 *
 * other properties can be present depending on CTS.
 * =============================================================================
 *
 * @internal revisions
 * @since 1.9.14
 *
 *
**/
require_once(TL_ABS_PATH . "/lib/functions/database.class.php");
require_once(TL_ABS_PATH . "/lib/functions/lang_api.php");

abstract class codeTrackerInterface
{
  // members to store the codetracking information.
  // Values are set in the actual subclasses
  var $cfg = null;  // simpleXML object
  var $name = null;

  var $tlCharSet = null;
  
  // private vars don't touch
  var $dbConnection = null;  // usable only if interface is done via direct DB access.
  var $dbMsg = '';
  var $connected = false;
  var $interfaceViaDB = false;  // useful for connect/disconnect methods
  
  var $guiCfg = array();

  /**
   * Construct and connect to CTS.
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
  function getCfg()
  {
    return $this->cfg;
  }

  /**
   *
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
      $msg = " - Code tracker:$this->name - XML Configuration seems to be empty - please check";
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

    // From 
    // http://php.net/manual/it/function.unserialize.php#112823
    //
    // After PHP 5.3 an object made by 
    // SimpleXML_Load_String() cannot be serialized.  
    // An attempt to do so will result in a run-time 
    // failure, throwing an exception.  
    //
    // If you store such an object in $_SESSION, 
    // you will get a post-execution error that says this:
    // Fatal error: Uncaught exception 'Exception' 
    // with message 'Serialization of 'SimpleXMLElement' 
    // is not allowed' in [no active file]:0 
    // Stack trace: #0 {main} thrown in [no active file] 
    // on line 0
    //
    // !!!!! The entire contents of the session will be lost.
    // http://stackoverflow.com/questions/1584725/quickly-convert-simplexmlobject-to-stdclass
    $this->cfg = json_decode(json_encode($this->cfg));
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
   * establishes the database connection to the codetracking system
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
       
    // cast everything to string in order to avoid issues
    // @20140604 someone has been issues trying to connect to JIRA on MSSQL    
    $this->cfg->dbtype = strtolower((string)$this->cfg->dbtype);
    $this->cfg->dbhost = (string)$this->cfg->dbhost;
    $this->cfg->dbuser = (string)$this->cfg->dbuser;
    $this->cfg->dbpassword = (string)$this->cfg->dbpassword;
    $this->cfg->dbname = (string)$this->cfg->dbname;

    $this->dbConnection = new database($this->cfg->dbtype);
    $result = $this->dbConnection->connect(false, $this->cfg->dbhost,$this->cfg->dbuser,
                                           $this->cfg->dbpassword, $this->cfg->dbname);

    if (!$result['status'])
    {
      $this->dbConnection = null;
      $connection_args = "(interface: - Host:$this->cfg->dbhost - " . 
                         "DBName: $this->cfg->dbname - User: $this->cfg->dbuser) "; 
      $msg = sprintf(lang_get('CTS_connect_to_database_fails'),$connection_args);
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
   * State of connection to CTS
   *
   * @return bool returns true if connection with CTS is established, false else
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
   * default implementation for generating a link to the codetracking page for viewing
   * the code with the given id in a new page
   *
   * @param mixed project_key
   * @param mixed repository_name
   * @param mixed code_path
   *
   * @return string returns a complete HTML HREF to view the code (if found in db)
   *
   **/
  function buildViewCodeLink($project_key, $repository_name, $code_path, $opt=null)
  {
    $branch_name = null;
    $commit_id = null;
    if (isset($opt['branch']))
    {
      $branch_name = $opt['branch'];
    }
    if (isset($opt['commit_id']))
    {
      $commit_id = $opt['commit_id'];
    }
    $link = "<a href='" . $this->buildViewCodeURL($project_key,$repository_name,$code_path,$branch_name,$commit_id) . "' target='_blank'>";

    $link .= $code_path;

    $link .= "</a>";

    $ret = new stdClass();
    $ret->link = $link;
    $ret->op = true;

    return $ret;
  }

  /**
   * returns the URL which should be displayed for entering code links
   *
   * @return string returns a complete URL
   *
   **/
  function getEnterCodeURL()
  {
    return $this->cfg->uricreate;
  }


  /**
   * Returns URL to the codetracking page for viewing ticket
   *
   * @param mixed project_key
   * @param mixed repository_name
   * @param mixed code_path
   * @param mixed branch_name
   * 
   * @return string 
   **/
  function buildViewCodeURL($project_key, $repository_name, $code_path, $branch_name=null, $commit_id=null)
  {
    $codeURL = $this->cfg->uriview . $project_key . '/repos/' . $repository_name .
               '/browse/' . $code_path;

    //commit_id has priority over branch name
    if (!is_null($commit_id))
    {
      $codeURL .= '?at=' . $commit_id;
    }
    else if (!is_null($branch_name))
    {
      $codeURL .= '?at=refs%2Fheads%2F' . $branch_name;
    }

    return $codeURL;
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

}
