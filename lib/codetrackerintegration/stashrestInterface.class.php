<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	stashrestInterface.class.php
 * @author      Uwe Kirst
 *
**/
require_once(TL_ABS_PATH . "/third_party/stash-rest/RestRequest.php");
require_once(TL_ABS_PATH . "/third_party/stash-rest/Stash.php");
class stashrestInterface extends codeTrackerInterface
{
  const NOPROJECTKEY = 'e18b741e13b2b1b09f2ac85615e37bae';
  private $APIClient;
  private $stashCfg;

  /**
   * Construct and connect to BTS.
   *
   * @param str $type (see tlCodeTracker.class.php $systems property)
   * @param xml $cfg
   **/
  function __construct($type,$config,$name)
  {
    $this->name = $name;
    $this->interfaceViaDB = false;

    if($this->setCfg($config) && $this->checkCfg())
    {
      $this->completeCfg();
      $this->connect();
      $this->guiCfg = array('use_decoration' => true);
    } 
  }

  /**
   *
   * check for configuration attributes than can be provided on
   * user configuration, but that can be considered standard.
   * If they are MISSING we will use 'these carved on the stone values' 
   * in order	to simplify configuration.
   * 
   *
   **/
  function completeCfg()
  {
    $base = trim($this->cfg->uribase,"/") . '/'; // be sure no double // at end

    if( !property_exists($this->cfg,'uriapi') )
    {
      $this->cfg->uriapi = $base . 'rest/api/1.0/';
    }

    if( !property_exists($this->cfg,'uriview') )
    {
      $this->cfg->uriview = $base . 'projects/';
    }
      
    if( !property_exists($this->cfg,'uricreate') )
    {
      $this->cfg->uricreate = $base . '';
    }
  }

  /**
   * useful for testing 
   *
   *
   **/
  function getAPIClient()
  {
    return $this->APIClient;
  }

  /**
   * returns the URL which should be displayed for entering code links
   *
   * @return string returns a complete URL
   *
   **/
  function getEnterCodeURL()
  {
    return $this->cfg->uricreate . 'projects';
  }

  /**
   * establishes connection to the bugtracking system
   *
   * @return bool 
   *
   **/
  function connect()
  {
    try
    {
  	  // CRITIC NOTICE for developers
  	  // $this->cfg is a simpleXML Object, then seems very conservative and safe
  	  // to cast properties BEFORE using it.
      $this->stashCfg = array('username' => (string)trim($this->cfg->username),
                   'password' => (string)trim($this->cfg->password),
                   'host' => (string)trim($this->cfg->uriapi));
  	  
      $this->stashCfg['proxy'] = config_get('proxy');
      if( !is_null($this->stashCfg['proxy']) )
      {
        if( is_null($this->stashCfg['proxy']->host) )
        {
          $this->stashCfg['proxy'] = null;
        }  
      }  


      $this->APIClient = new StashApi\Stash($this->stashCfg);

      $this->connected = $this->APIClient->testLogin();
      if($this->connected && ($this->cfg->projectkey != self::NOPROJECTKEY))
      {
        // Now check if can get info about the project, to understand
        // if at least it exists.
        $pk = trim((string)$this->cfg->projectkey);
        $this->APIClient->getProject($pk);
      }  
    }
    catch(Exception $e)
    {
      $this->connected = false;
      tLog(__METHOD__ . "  " . $e->getMessage(), 'ERROR');
    }
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
   */
  public function getProject($projectKey)
  {
    try
    {
      return $this->APIClient->getProject($projectKey);
    }
    catch(Exception $e)
    {
      tLog(__METHOD__ . "  " . $e->getMessage(), 'ERROR');
    }
  }

  /**
   *
   */
  public function getProjects()
  {
    try
    {
      return $this->APIClient->getProjects();
    }
    catch(Exception $e)
    {
      tLog(__METHOD__ . "  " . $e->getMessage(), 'ERROR');
    }
  }

  /**
   *
   */
  public function getProjectsForHTMLSelect()
  {
    $ret = null;
    $projList = $this->getProjects();
    if (property_exists($projList, 'values'))
    {
      $ret = $this->objectAttrToKeyName($projList->values);
    }
    return $ret;
  }

  /**
   *
   */
  public function getRepos($projectKey)
  {
    try
    {
      return $this->APIClient->getRepos($projectKey);
    }
    catch(Exception $e)
    {
      tLog(__METHOD__ . "  " . $e->getMessage(), 'ERROR');
    }
  }

  /**
   *
   */
  public function getReposForHTMLSelect($projectKey)
  {
    $ret = null;
    $repoList = $this->getRepos($projectKey);
    if (property_exists($repoList, 'values'))
    {
      $ret = $this->objectAttrToIDName($repoList->values, 'slug','name');
    }
    return $ret;
  }

  /**
   *
   */
  public function getRepoContent($projectKey,$repoName,$path='',$branch='',$commit_id='',$type=false)
  {
    try
    {
      return $this->APIClient->getRepoContent($projectKey,$repoName,$path,$branch,$commit_id,$type);
    }
    catch(Exception $e)
    {
      tLog(__METHOD__ . "  " . $e->getMessage(), 'ERROR');
    }
  }

  /**
   *
   */
  public function getRepoContentForHTMLSelect($projectKey,$repoName,$path='',$branch='',$type=false)
  {
    $ret = null;
    $contentList = $this->getRepoContent($projectKey,$repoName,$path,$branch,'',$type);
    if (property_exists($contentList, 'children') && property_exists($contentList->children, 'values'))
    {
      if ($path != '' && substr($path,-1) != "/")
      {
        $path .= "/";
      }
      foreach($contentList->children->values as $elem)
      {
        $tmpName = $elem->path->toString;
        $slashPos = strpos($elem->path->toString, '/');
        if ($slashPos !== false)
        {
          $tmpName = substr($tmpName, 0, $slashPos);
        }
        $ret[$tmpName] = array($elem->type,$path);
      }
    }
    return $ret;
  }

  /**
   *
   */
  public function getBranches($projectKey,$repoName)
  {
    try
    {
      return $this->APIClient->getBranches($projectKey,$repoName);
    }
    catch(Exception $e)
    {
      tLog(__METHOD__ . "  " . $e->getMessage(), 'ERROR');
    }
  }

  /**
   *
   */
  public function getBranchesForHTMLSelect($projectKey,$repoName)
  {
    $ret = null;
    $branchList = $this->getBranches($projectKey,$repoName);
    if (property_exists($branchList, 'values'))
    {
      $ret = $this->objectAttrToIDName($branchList->values, 'displayId','displayId');
    }
    return $ret;
  }

  /**
   *
   */
  public function getCommits($projectKey,$repoName,$branchName=null)
  {
    try
    {
      return $this->APIClient->getCommits($projectKey,$repoName,$branchName);
    }
    catch(Exception $e)
    {
      tLog(__METHOD__ . "  " . $e->getMessage(), 'ERROR');
    }
  }

  /**
   *
   */
  public function getCommitsForHTMLSelect($projectKey,$repoName,$branchName)
  {
    $ret = null;
    $commitList = $this->getCommits($projectKey,$repoName,$branchName);
    if (property_exists($commitList, 'values'))
    {
      $dateFormats = config_get('locales_date_format');

      $locale = (isset($_SESSION['locale'])) ? $_SESSION['locale'] : 'en_GB';
      $localesDateFormat = $dateFormats[$locale];

      foreach($commitList->values as $elem)
      {
        $ret[$elem->id] = $elem->displayId . ' (' . strftime($localesDateFormat,
                          ($elem->authorTimestamp / 1000)) . '): ' . $elem->message;
      }
    }
    return $ret;
  }

  /**
   *
   * 
   */
  private function objectAttrToKeyName($attrSet)
  {
    $ret = null;
    if(!is_null($attrSet))
    {
      $ic = count($attrSet);
      for($idx=0; $idx < $ic; $idx++)
      {
        $ret[$attrSet[$idx]->key] = $attrSet[$idx]->name . " (" . $attrSet[$idx]->key . ")"; 
      }  
    }  
    return $ret;    
  }

  /**
   *
   * 
   */
  private function objectAttrToIDName($attrSet,$id='id',$name='name')
  {
    $ret = null;
    if(!is_null($attrSet))
    {
      $ic = count($attrSet);
      for($idx=0; $idx < $ic; $idx++)
      {
        $ret[$attrSet[$idx]->$id] = $attrSet[$idx]->$name;
      }
    }
    return $ret;
  }

  /**
   *
   * 
   */
  private function objectAttrToIDNameKey($attrSet)
  {
    $ret = null;
    if(!is_null($attrSet))
    {
      $ic = count($attrSet);
      for($idx=0; $idx < $ic; $idx++)
      {
        $ret[$attrSet[$idx]->id] = $attrSet[$idx]->name . " (" . $attrSet[$idx]->key . ")";
      }
    }
    return $ret;
  }
  




  /**
   *
   * @author uwe_kirst@mentor.com>
   **/
	public static function getCfgTemplate()
  {
    $tpl = "<!-- Template " . __CLASS__ . " -->\n" .
           "<codetracker>\n" .
           "<username>STASH LOGIN NAME</username>\n" .
           "<password>STASH PASSWORD</password>\n" .
           "<uribase>https://testlink.atlassian.net/</uribase>\n" .
           "<uriapi>https://testlink.atlassian.net/rest/api/1.0/</uriapi>\n" .
           "<uriview>https://testlink.atlassian.net/projects/</uriview>\n" .
           "<projectkey>STASH PROJECT KEY</projectkey>\n" .
           "</codetracker>\n";
	  return $tpl;
  }

  /**
   *
   *
   **/
  function checkCfg()
  {
    $status_ok = true;
    if( property_exists($this->cfg, 'projectkey') )
    {
      $pk = trim((string)($this->cfg->projectkey));
      if($pk == '')
      {
        $status_ok = false;
        $msg = __CLASS__ . ' - Empty configuration: <projectKey>';
      }  
    }  
    else
    {
      // this is oK if user only wants to LINK issues
      $this->cfg->projectkey = self::NOPROJECTKEY;
    }  

    if(!$status_ok)
    {
      tLog(__METHOD__ . ' / ' . $msg , 'ERROR');
    }  
    return $status_ok;
  }


}
