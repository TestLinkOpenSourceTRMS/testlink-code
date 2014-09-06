<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	bugzillaxmlrpcInterface.class.php
 * @author Francisco Mancardi
 *
 *
 * @internal revisions
 * @since 1.9.11
 * 20140531 - franciscom - contribution + refactoring adding new support methods
 * 
**/
require_once('Zend/Loader/Autoloader.php');
Zend_Loader_Autoloader::getInstance();

class bugzillaxmlrpcInterface extends issueTrackerInterface
{
  private $APIClient;
  private $issueDefaults;

  /**
   * Construct and connect to BTS.
   *
   * @param str $type (see tlIssueTracker.class.php $systems property)
   * @param xml $cfg
   **/
  function __construct($type,$config,$name)
  {
    $this->interfaceViaDB = false;
    $this->methodOpt['buildViewBugLink'] = array('addSummary' => true, 'colorByStatus' => false);
    $this->guiCfg = array('use_decoration' => true); // add [] on summary
    
    $this->name = $name;
    if( !$this->setCfg($config) )
    {
      return false;
    } 

    $this->completeCfg();
    $this->connect();
    
    // For bugzilla status code is not important.
    // Design Choice make it equal to verbose. Important bugzilla uses UPPERCASE 
    $this->defaultResolvedStatus = array();
    $this->defaultResolvedStatus[] = array('code' => 'RESOLVED', 'verbose' => 'RESOLVED');
    $this->defaultResolvedStatus[] = array('code' => 'VERIFIED', 'verbose' => 'VERIFIED');
    $this->defaultResolvedStatus[] = array('code' => 'CLOSED', 'verbose' => 'CLOSED');
    
    $this->setResolvedStatusCfg();
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
    if( !property_exists($this->cfg,'urixmlrpc') )
    {
      $this->cfg->urixmlrpc = $base . 'xmlrpc.cgi';
    }

    if( !property_exists($this->cfg,'uriview') )
    {
      $this->cfg->uriview = $base . 'show_bug.cgi?id=';
    }
      
    if( !property_exists($this->cfg,'uricreate') )
    {
      $this->cfg->uricreate = $base;
    }
    
    $this->issueDefaults = array('version' => 'unspecified', 'severity' => 'Trivial',
                                 'op_sys' => 'All', 'priority' => 'Normal','platform' => "All",);
    foreach($this->issueDefaults as $prop => $default)
    {
      $this->cfg->$prop = (string)(property_exists($this->cfg,$prop) ? $this->cfg->$prop : $default);
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
      $this->createAPIClient();
      $this->connected = true;
    }
    catch(Exception $e)
    {
      $logDetails = '';
      foreach(array('uribase','apikey') as $v)
      {
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
  *
  **/
	public function getIssue($issueID)
	{
		$issue = null;

    $resp = array();
    $login = $this->login();		
    $resp = array_merge($resp,(array)$login['response']);


		$method = 'Bug.get';
		$args = array(array('ids' => array(intval($issueID)), 'permissive' => true));
		if (isset($login['userToken'])) 
    {
			$args[0]['Bugzilla_token'] = $login['userToken'];
		}
		$resp[$method] = $this->APIClient->call($method, $args);


    $op = $this->logout($login['userToken']);
    $resp = array_merge($resp,(array)$op['response']);


		if(count($resp['Bug.get']['faults']) == 0)
		{
			$issue = new stdClass();
      $issue->id = $issueID;
		  $issue->IDHTMLString = "<b>{$issueID} : </b>";
			$issue->statusCode = $issue->statusVerbose = $resp['Bug.get']['bugs'][0]['status'];
      $issue->isResolved = isset($this->resolvedStatus->byCode[$issue->statusCode]); 

			$issue->statusHTMLString = $this->buildStatusHTMLString($issue->statusVerbose);
			$issue->summary = $issue->summaryHTMLString = $resp['Bug.get']['bugs'][0]['summary'];
		}
    else
	  {
	    tLog(__METHOD__ . ' :: ' . $resp['Bug.get']['faults'][0]['faultString'], 'ERROR');
		}
		return $issue;
	}


	/**
	 * Returns status for issueID
	 *
	 * @param string issueID
	 *
	 * @return 
	 **/
	function getIssueStatusCode($issueID)
	{
		$issue = $this->getIssue($issueID);
		return !is_null($issue) ? $issue->statusCode : false;
	}

	/**
	 * Returns status in a readable form (HTML context) for the bug with the given id
	 *
	 * @param string issueID
	 * 
	 * @return string 
	 *
	 **/
	function getIssueStatusVerbose($issueID)
	{
    return $this->getIssueStatusCode($issueID);
	}

	/**
	 *
	 * @param string issueID
	 * 
	 * @return string 
	 *
	 **/
	function getIssueSummaryHTMLString($issueID)
	{
    $issue = $this->getIssue($issueID);
    $str = $issue->summaryHTMLString;
		if($this->guiCfg['use_decoration'])
		{
			$str = "[" . $str . "] ";	
		}
    return $str;
	}

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
      $status_ok = is_object($issue) && !is_null($issue);
    }
    return $status_ok;
  }


  /**
   * 
   *
   **/
	function createAPIClient()
	{
		// echo __METHOD__ .'<br>';
		try
		{
			$this->APIClient = new Zend_XmlRpc_Client((string)$this->cfg->urixmlrpc);
			$httpClient = new Zend_Http_Client();
			$httpClient->setCookieJar();
			$this->APIClient->setHttpClient($httpClient);
		}
		catch(Exception $e)
		{
			$this->connected = false;
            tLog(__METHOD__ .  $e->getMessage(), 'ERROR');
		}
	}	



    /**
     *
     * @author francisco.mancardi@gmail.com>
     **/
  public static function getCfgTemplate()
  {
    $template = "<!-- Template " . __CLASS__ . " -->\n" .
                "<issuetracker>\n" .
                "<username>USERNAME</username>\n" .
                "<password>PASSWORD</password>\n" .
                "<uribase>http://bugzilla.mozilla.org/</uribase>\n" .
                "<!-- In order to create issues from TestLink, you need to provide this MANDATORY info -->\n".
                "<product>BUGZILLA PRODUCT</product>\n" .					
                "<component>BUGZILLA PRODUCT</component>\n" .
                "<!-- This can be adjusted according Bugzilla installation. -->\n".
                "<!-- COMMENTED SECTION \n" .
                " There are defaults defined in bugzillaxmlrpcInterface.class.php. \n".
                "<version>unspecified</version>\n" .
                "<severity>Trivial</severity>\n" .
                "<op_sys>All</op_sys>\n" .
                "<priority>Normal</priority>\n" .
                "<platform>All</platform> --> \n".
                "</issuetracker>\n";
                
    return $template;
  }
  
  
  function getAccessibleProducts()
  {
    $issue = null;

    $resp = array();
    $login = $this->login();    
    $resp = array_merge($resp,(array)$login['response']);

    $method = 'Product.get_accessible_products';
    $args = array(array());
    if (isset($login['userToken'])) 
    {
      $args[0]['Bugzilla_token'] = $login['userToken'];
    }
    $itemSet = $this->APIClient->call($method, $args);
    
    $op = $this->logout($login['userToken']);
    $resp = array_merge($resp,(array)$op['response']);
    
    return $itemSet;
  }

  /**
   *
   */
  function getProduct($id)
  {
    $issue = null;
    $resp = array();
    $login = $this->login();    
    $resp = array_merge($resp,(array)$login['response']);
    
    $method = 'Product.get';
    $args = array(array('ids' => array(intval($id))));
    if (isset($login['userToken'])) 
    {
      $args[0]['Bugzilla_token'] = $login['userToken'];
    }
    $itemSet = $this->APIClient->call($method,$args);
    
    $op = $this->logout($login['userToken']);
    $resp = array_merge($resp,(array)$op['response']);

    return $itemSet; 	  
  }

    // good info from:
    // http://petehowe.co.uk/2010/example-of-calling-the-bugzilla-api-using-php-zend-framework/
    //
    // From BUGZILLA DOCS
    //
    // Returns
    // A hash with one element, id. This is the id of the newly-filed bug.
    // 
    // Errors
    // 
    // 51 (Invalid Object)
    //     The component you specified is not valid for this Product.
    // 
    // 103 (Invalid Alias)
    //     The alias you specified is invalid for some reason. See the error message for more details.
    //
    // 104 (Invalid Field)
    //     One of the drop-down fields has an invalid value, or a value entered in a text field is too long. 
    //     The error message will have more detail.
    //
    // 105 (Invalid Component)
    //     You didn't specify a component.
    //
    // 106 (Invalid Product)
    //     Either you didn't specify a product, this product doesn't exist, or you don't have permission 
    //     to enter bugs in this product.
    //
    // 107 (Invalid Summary)
    //     You didn't specify a summary for the bug.
    //
    // 504 (Invalid User)
    //     Either the QA Contact, Assignee, or CC lists have some invalid user in them. 
    //     The error message will have more details.
    // 
    
    
  function addIssue($summary,$description)
  {
    $issue = null;
    $resp = array();
    $login = $this->login();    
    $resp = array_merge($resp,(array)$login['response']);
    
    $method = 'Bug.create';
    $issue = array('product' => (string)$this->cfg->product,
                   'component' => (string)$this->cfg->component,
                   'summary' => $summary,
                   'description' => $description);


    foreach($this->issueDefaults as $prop => $default)
    {
      $issue[$prop] = (string)$this->cfg->$prop;
    }
    
    $args = array($issue);
    if (isset($login['userToken'])) 
    {
      $args[0]['Bugzilla_token'] = $login['userToken'];
    }

    
    $op = $this->APIClient->call($method,$args);
    if( ($op['status_ok'] = ($op['id'] > 0)) )
    {
      $op['msg'] = sprintf(lang_get('bugzilla_bug_created'),$summary,$issue['product']);
    }
    else
    {
      $msg = "Create BUGZILLA Ticket FAILURE ";
      $op= array('status_ok' => false, 'id' => -1, 
                 'msg' => $msg . ' - serialized issue:' . serialize($issue));
      tLog($msg, 'WARNING');
    }
      
    $logout = $this->logout($login['userToken']);
    $resp = array_merge($resp,(array)$logout['response']);

    return $op;
  }

 /**
  *
  **/
  function canCreateViaAPI()
  {
    return (property_exists($this->cfg, 'product') && property_exists($this->cfg, 'component'));
  }



 /**
  *
  **/
  private function login()
  {
    $args = array(array('login' => (string)$this->cfg->username, 
                        'password' => (string)$this->cfg->password,'remember' => 1));
    $ret = array();
    $ret['response']['User.login'] = $this->APIClient->call('User.login', $args);
    $ret['userToken'] = $ret['response']['User.login']['token'];
    return $ret;
  }  


 /**
  *
  **/
  private function logout($userToken=null)
  {
    $args = array(array());
    if( !is_null($userToken) ) 
    {
      $args[0]['Bugzilla_token'] = $userToken;
    }

    $ret = array();
    $ret['response']['User.logout'] = $this->APIClient->call('User.logout', $args);
    return $ret;
  }

}