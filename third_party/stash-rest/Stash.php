<?php
/**
 * STASH Rest Client
 *
 * @author     Uwe Kirst <uwe_kirst@mentor.com>
 *
 */

namespace StashApi;

class Stash
{

    protected $host;
    protected $username;
    protected $password;
    protected $proxy;

    /**
     * Having properties for saving connection config
     * is needed because on TestLink I've implemented
     * poor's man caching on $_SESSION, and then I need
     * to save connection info in THIS OBJECT, because
     * is not recreated.
     *
     */
    public function __construct(array $cfg = array())
    {
        if( !is_null($cfg) )
        {
            $k2trim = array('username','password','host');
            foreach( $k2trim as $tg )
            {
              $this->$tg = (isset($cfg[$tg])) ? trim($cfg[$tg]) : null;
            }    
            $this->proxy = isset($cfg['proxy']) ? $cfg['proxy'] : null;

        }    
        $this->request = new RestRequest();
        $this->request->username = $this->username; 
        $this->request->password = $this->password;
        $this->request->proxy = $this->proxy;

        $this->configCheck();
    
        $this->host = trim($this->host,"/") . '/'; 
        if( ($last = $this->host[strlen($this->host)-1]) != '/' )
        {
            $this->host .= '/';
        }

    }

    /**
     *
     */
    private function configCheck()
    {
        if(is_null($this->host) || $this->host == '')
        {
            throw new \Exception('Missing or Empty host (url to API) - unable to continue');      
        }    
        if(is_null($this->request->username) || $this->request->username == '' )
        {
            throw new \Exception('Missing or Empty username - unable to continue');      
        }    
        if(is_null($this->request->password) || $this->request->password == '')
        {
            throw new \Exception('Missing or Empty password - unable to continue');      
        }    
    }

    /**
     *
     */
    public function testLogin()
    {
        $user = $this->getUser($this->request->username);
        if (!empty($user) && $this->request->lastRequestStatus()) {
            return true;
        }

        return false;
    }

    /**
     * https://developer.atlassian.com/static/rest/stash/latest/stash-rest.html#idp299120
     */
    public function getUser($username,$keepConnection=false)
    {
        $this->request->openConnect($this->host . 'users/' . $username, 'GET');
        $this->request->execute($keepConnection);
        $user = json_decode($this->request->getResponseBody());

        return $user;
    }

    /**
     * get available projects
     *
     * @return mixed
     */
    public function getProjects($keepConnection=false)
    {
        $this->request->openConnect($this->host . 'projects?limit=1000', 'GET');
        $this->request->execute($keepConnection);
        $items = json_decode($this->request->getResponseBody());
        return $items;
    }

    /**
     * get available repos
     *
     * @return mixed
     */
    public function getRepos($projectKey,$repoName=null,$keepConnection=false)
    {
        if (is_null($repoName))
        {
          $this->request->openConnect($this->host . 'projects/' . $projectKey . '/repos?limit=1000', 'GET');
        }
        else
        {
          $this->request->openConnect($this->host . 'projects/' . $projectKey . '/repos/' . $repoName . '?limit=1000', 'GET');
        }
        $this->request->execute($keepConnection);
        $items = json_decode($this->request->getResponseBody());
        return $items;
    }

   /**
     * get available files and directories within a repository
     *
     * @return mixed
     */
    public function getRepoContent($projectKey,$repoName,$path='',$branch='',$commit_id='',$type=false,$keepConnection=false)
    {
        $cmd = $this->host . 'projects/' . $projectKey . '/repos/' . $repoName . '/browse';
        if (!is_null($path) && $path != '')
        {
          if(substr($path,0,1) != "/")
          {
            $cmd .= "/";
          }
          $cmd .= $path;
        }
        //commit_id has priority over branch name
        if(!is_null($commit_id) && $commit_id != '')
        {
          $at = $commit_id;
        }
        else
        {
          $at = $branch;
        }
        $cmd .= '?at=' .$at . '&type=' . $type;

        $this->request->openConnect($cmd, 'GET');
        $this->request->execute($keepConnection);
        $items = json_decode($this->request->getResponseBody()); 
        return $items;
    }

    /**
     * get available branches of a specific repository
     *
     * @return mixed
     */
    public function getBranches($projectKey,$repoName,$keepConnection=false)
    {
        $this->request->openConnect($this->host . 'projects/' . $projectKey . '/repos/' .
                                    $repoName .'/branches?limit=1000', 'GET');
        $this->request->execute($keepConnection);
        $items = json_decode($this->request->getResponseBody());
        return $items;
    }

    /**
     * get available commits of a specific repository
     *
     * @return mixed
     */
    public function getCommits($projectKey,$repoName,$branchName=null,$keepConnection=false)
    {
        $requestStr = $this->host . 'projects/' . $projectKey . '/repos/' . $repoName .'/commits?limit=50';
        if(!is_null($branchName) && $branchName != '')
        {
          $requestStr .= '&until=' . $branchName;
        }
        $this->request->openConnect($requestStr, 'GET');
        $this->request->execute($keepConnection);
        $items = json_decode($this->request->getResponseBody());
        return $items;
    }

    /**
     * get specified project
     *
     * @return mixed
     */
    public function getProject($projectKey,$keepConnection=false)
    {
        $uri = $this->host . "project/{$projectKey}";
        $this->request->openConnect($uri, 'GET');
        $this->request->execute($keepConnection);

        $obj = json_decode($this->request->getResponseBody()); 
        if(!is_null($obj))
        {
            if(property_exists($obj, 'errorMessages'))
            {
                // ATTENTION \Exception in order to use PHP object.
                $msg = "Error Processing Request - " . __METHOD__ . ' ' .
                       implode('/', $obj->errorMessages);
                throw new \Exception($msg, 999);
            }    
        }
        return $obj;    
    }

}
