<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	gitlabrepoInterface.class.php
 * @author      Francisco Mancardi
 *
**/
require_once(TL_ABS_PATH . 
  "/third_party/gitlab-php-api/lib/gitlab-rest-api.php");

class gitlabreporestInterface extends codeTrackerInterface
{
  private $APIClient;
  private $repoCfg;

  /**
   * Construct and connect to Repository
   *
   * @param str $type (see tlCodeTracker.class.php $systems property)
   * @param xml $cfg
   **/
  function __construct($type,$config,$name)
  {
    $this->name = $name;
    if ($this->setCfg($config) && $this->checkCfg()) {
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
   * establishes connection to the system
   *
   * @return bool 
   *
   **/
  function connect()
  {
    $processCatch = false;

    try {
      // CRITIC NOTICE for developers
      // $this->cfg is a simpleXML Object, then seems very conservative and safe
      // to cast properties BEFORE using it.
      $redUrl = (string)trim($this->cfg->uribase);
      $redAK = (string)trim($this->cfg->apikey);
      $projectId = (string)trim($this->cfg->projectidentifier); //TODO: check integer value
      $pxy = new stdClass();
      $pxy->proxy = config_get('proxy');
      $this->APIClient = new gitlab($redUrl,$redAK,$projectId, $pxy);

      //DEBUG var_dump($redUrl,$redAK,$projectId, $pxy);
      // to undestand if connection is OK, I will ask for projects.
      // I've tried to ask for users but get always ERROR from gitlab (not able to understand why).
      try {
        $items = $this->APIClient->getProjects();
        $this->connected = count($items) > 0 ? true : false;
        unset($items);
      } catch(Exception $e) {
        $processCatch = true;
      }
    } catch(Exception $e) {
      $processCatch = true;
    }
    
    if ($processCatch) {
      $logDetails = '';
      foreach (array('uribase','apikey') as $v) {
        $logDetails .= "$v={$this->cfg->$v} / "; 
      }
      $logDetails = trim($logDetails,'/ ');
      $this->connected = false;
      tLog(__METHOD__ . " [$logDetails] " . $e->getMessage(), 'ERROR');
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
    $pk = 'projectidentifier';
    if (property_exists($this->cfg, $pk)) {
      $pk = trim((string)($this->cfg->$pk));
      if ($pk == '') {
        $status_ok = false;
        $msg = __CLASS__ . " - Empty configuration: <$pk>";
      }  
    }  

    if (!$status_ok) {
      tLog(__METHOD__ . ' / ' . $msg , 'ERROR');
    }  
    return $status_ok;
  }


}
