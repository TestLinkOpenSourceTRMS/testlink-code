<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource  reqMgrSystemInterface.class.php
 * @since       1.9.6
 * @internal    based on contribution by Richard van der Pols
 *
 * Base class for connection to requirement management system interfaces
 * For supporting a system this class has to be extended, and all customization
 * should be done in the subclass.
 *
 * @internal revisions
 * @since 1.9.6
 *
**/

abstract class reqMgrSystemInterface
{
  private $connected;
  private $cfg = null;  // simpleXML object
  private $interfaceViaDB = false;  // useful for connect/disconnect methods


  // Variables related to establishing the connection
  private $serverConnection = null;
  private $server = null;
  private $user = null;
  private $password = null;
  
  // Variables related to retrieving and caching the requirements
  private $projects = array();
  private $lastproject = null;
  private $baselines = array();
  private $lastbaseline = null;
  private $requirements = array();
  private $type = null;
  
  // Variables related to requirement modifications during import.
  private  $prefix = "";
  
  /**
   * Will follow same approach used for issue tracking integration,
   * connection will be done when constructing.
   *
   **/
  public function __construct($type,$config)
  {
    if( $this->setCfg($config) )
    {
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
  public function getCfg()
  {
    return $this->cfg;
  }

	/**
	 *
   **/
  public function setCfg($xmlString)
  {
    $msg = null;
    $signature = 'Source:' . __METHOD__;
  
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
    
    return is_null($msg);
  }

  /**
   *
   **/
  public function getMyInterface()
  {
    return $this->cfg->interfacePHP;
  }


  public function isConnected()
  {
    return $this->connected;
  }


  private function connect()
  {
    if (is_null($type))
    {
      return false;
    }
    
    $this->server = $server;
    $this->user = $user;
    $this->password = $password;
    
    return true;
  }
  
  private function disconnect($url)
  {
    if (!is_null($this->server))
    {
      // Need to disconnect from the server somehow
    }
    
    $this->server = null;
    $this->user = null;
    $this->password = null;
    
    return true;
  }
  
  public function getProjects()
  {
    if (is_null($server))
    {
      // There is no connection with the requirement management server.
      return false;
    }

    $this->projects = array();
    $this->lastproject = null;
    $this->lastbaseline = null;
    
    if (count($this->projects) == 0)
    {
      // No projects were found.
      return false;
    }
    return $this->projects;
  }
  
  public function getBaselines($project, $refresh = false)
  {
    if (is_null($serverConnection))
    {
      // There is no connection with the requirement management server.
      return false ;
    }
    
    if (($project != $this->lastproject) || (count($this->baselines) == 0) || $refresh)
    {
      // Retrieve baselines for the specified project.
      $this->lastproject = $project;
      $this->baselines = array();
    }
    else
    {
      // Baselines are already available.
    }
    
    return $this->baselines;
  }
  
  public function getRequirements($project, $baseline, $refresh = false)
  {
    if ($project != $this->lastproject && !$this->getBaselines($project))
    {
        // Baselines for specified projects could not be retrieved.
        return false;
    }
    
    if (($baseline != $this->lastbaseline) || $refresh)
    {
      // Retrieve the set of requirements in case it is a different baseline as last retrieved
      // or the list needs to be refreshed
    }
    
    return $this->requirements;
  }
  
}
?>
