<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	jirarestInterface.class.php
 * @author      Francisco Mancardi
 *
 * @see https://developer.atlassian.com/jiradev/api-reference/jira-rest-apis
 * @see https://developer.atlassian.com/jiradev/api-reference/jira-rest-apis/jira-rest-api-tutorials/
 *
 *
 * @internal revisions
 * @since 1.9.16
 *
**/
require_once(TL_ABS_PATH . "/third_party/fayp-jira-rest/RestRequest.php");
require_once(TL_ABS_PATH . "/third_party/fayp-jira-rest/Jira.php");
class jirarestInterface extends issueTrackerInterface
{
  const NOPROJECTKEY = 'e18b741e13b2b1b09f2ac85615e37bae';
  private $APIClient;
  private $issueDefaults;
  private $issueAttr = null;
  private $jiraCfg;

  var $defaultResolvedStatus;
  var $support;

	/**
	 * Construct and connect to BTS.
	 *
	 * @param str $type (see tlIssueTracker.class.php $systems property)
	 * @param xml $cfg
	 **/
	function __construct($type,$config,$name)
	{
    $this->name = $name;
	  $this->interfaceViaDB = false;
    $this->support = new jiraCommons();
    $this->support->guiCfg = array('use_decoration' => true);

	  $this->methodOpt['buildViewBugLink'] = array('addSummary' => true, 'colorByStatus' => false);

    if($this->setCfg($config) && $this->checkCfg())
    {
      $this->completeCfg();
      $this->connect();
      $this->guiCfg = array('use_decoration' => true);

      if( $this->isConnected())
      {  
        $this->setResolvedStatusCfg();  
      }  
    } 
	}

   /**
    *
    */
    function getIssueAttr()
    {
      return $this->issueAttr;
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
      $this->cfg->uriapi = $base . 'rest/api/latest/';
    }

    if( !property_exists($this->cfg,'uriview') )
    {
      $this->cfg->uriview = $base . 'browse/';
    }
      
    if( !property_exists($this->cfg,'uricreate') )
    {
      $this->cfg->uricreate = $base . '';
    }


    if( property_exists($this->cfg,'attributes') )
    {
      // echo __FUNCTION__ . "::Debug::Step#$step Going To Add attributes <br>";$step++;
      $this->processAttributes();
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
    return $this->checkBugIDSyntaxString($issueID);
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
      $this->jiraCfg = array('username' => (string)trim($this->cfg->username),
                   'password' => (string)trim($this->cfg->password),
                   'host' => (string)trim($this->cfg->uriapi));
  	  
      $this->jiraCfg['proxy'] = config_get('proxy');
      if( !is_null($this->jiraCfg['proxy']) )
      {
        if( is_null($this->jiraCfg['proxy']->host) )
        {
          $this->jiraCfg['proxy'] = null;
        }  
      }  

      $this->APIClient = new JiraApi\Jira($this->jiraCfg);

      $this->connected = $this->APIClient->testLogin();
      if($this->connected && ($this->cfg->projectkey != self::NOPROJECTKEY))
      {
        // Now check if can get info about the project, to understand
        // if at least it exists.
        $pk = trim((string)$this->cfg->projectkey);
        $this->APIClient->getProject($pk);

        $statusSet = $this->APIClient->getStatuses();
        foreach ($statusSet as $statusID => $statusName)
        {
          $this->statusDomain[$statusName] = $statusID;
        }

        $this->defaultResolvedStatus = 
          $this->support->initDefaultResolvedStatus($this->statusDomain);
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
   *
   **/
	public function getIssue($issueID)
	{
	  if (!$this->isConnected())
	  {
        tLog(__METHOD__ . '/Not Connected ', 'ERROR');
		return false;
	  }
		
	  $issue = null;
    try
	  {

			$issue = $this->APIClient->getIssue($issueID);
      
      // IMPORTANT NOTICE
      // $issue->id do not contains ISSUE ID as displayed on GUI, but what seems to be an internal value.
      // $issue->key has what we want.
      // Very strange is how have this worked till today ?? (2015-01-24)
      if(!is_null($issue) && is_object($issue) && !property_exists($issue,'errorMessages'))
      {
     
        // We are going to have a set of standard properties
        $issue->id = $issue->key;
        $issue->summary = $issue->fields->summary;
        $issue->statusCode = $issue->fields->status->id;
        $issue->statusVerbose = $issue->fields->status->name;

        $issue->IDHTMLString = "<b>{$issueID} : </b>";
        $issue->statusHTMLString = $this->support->buildStatusHTMLString($issue->statusVerbose);
        $issue->summaryHTMLString = $this->support->buildSummaryHTMLString($issue);
        $issue->isResolved = isset($this->resolvedStatus->byCode[$issue->statusCode]); 


        /*
        for debug porpouses
        $tlIssue = new stdClass();
        $tlIssue->IDHTMLString = $issue->IDHTMLString;
        $tlIssue->statusCode = $issue->statusCode;
        $tlIssue->statusVerbose = $issue->statusVerbose;
        $tlIssue->statusHTMLString = $issue->statusHTMLString;
        $tlIssue->summaryHTMLString = $issue->summaryHTMLString;
        $tlIssue->isResolved = $issue->isResolved;

        var_dump($tlIssue);
        */
      }
      else
      {
        $issue = null;
      }  
    	
		}
		catch(Exception $e)
		{
      tLog("JIRA Ticket ID $issueID - " . $e->getMessage(), 'WARNING');
      $issue = null;
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
    return $issue->summaryHTMLString;
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

/*
{
    "fields": {
       "project":
       {
          "key": "TEST"
       },
       "summary": "REST ye merry gentlemen.",
       "description": "Creating of an issue using project keys and issue type names using the REST API",
       "issuetype": {
          "name": "Bug"
       }
       "priority": {
        "id": 4
       }

   }
}
*/

  /**
   *
   *
   * JSON example:
   *
   * {
   *  "fields": {
   *    "project": {
   *       "key": "TEST"
   *    },
   *    "summary": "REST ye merry gentlemen.",
   *    "description": "Creating of an issue using project keys and issue type names using the REST API",
   *    "issuetype": {
   *       "name": "Bug"
   *    }
   *  }
   * }
   *
   *
   */
  public function addIssue($summary,$description,$opt=null)
  {
    try
    {
      $issue = array('fields' =>
                     array('project' => array('key' => (string)$this->cfg->projectkey),
                           'summary' => $summary,
                           'description' => $description,
                           'issuetype' => array( 'id' => (int)$this->cfg->issuetype)
                           ));

      $prio = null;
      if(property_exists($this->cfg, 'issuepriority'))
      {
        $prio = $this->cfg->issuepriority;

      }  
      if( !is_null($opt) && property_exists($opt, 'issuePriority') )
      {
        $prio = $opt->issuePriority;
      }
      if( !is_null($prio) )
      {
        // CRITIC: if not casted to string, you will get following error from JIRA
        // "Could not find valid 'id' or 'name' in priority object."
        $issue['fields']['priority'] = array('id' => (string)$prio);
      }    
  

      if(!is_null($this->issueAttr))
      {
        $issue['fields'] = array_merge($issue['fields'],$this->issueAttr);
      }

      if(!is_null($opt))
      {

        // these can have multiple values
        if(property_exists($opt, 'artifactComponent'))
        {
          // YES is plural!!
          $issue['fields']['components'] = array();
          foreach( $opt->artifactComponent as $vv)
          {
            $issue['fields']['components'][] = array('id' => (string)$vv);
          }  
        }

        if(property_exists($opt, 'artifactVersion'))
        {
          // YES is plural!!
          $issue['fields']['versions'] = array();
          foreach( $opt->artifactVersion as $vv)
          {
            $issue['fields']['versions'][] = array('id' => (string)$vv);
          }  
        }



        if(property_exists($opt, 'reporter'))
        {
          $issue['fields']['reporter'] = array('name' => (string)$opt->reporter);
        }

        if(property_exists($opt, 'issueType'))
        {
          $issue['fields']['issuetype'] = array('id' => $opt->issueType);
        }
        

      }  

      $op = $this->APIClient->createIssue($issue);
      $ret = array('status_ok' => false, 'id' => null, 'msg' => 'ko');
      if(!is_null($op))
      {  
        if(isset($op->errors))
        {
          $ret['msg'] = __FUNCTION__ . ":Failure:JIRA Message:\n";
          foreach ($op->errors as $pk => $pv) 
          {
            $ret['msg'] .= "$pk => $pv\n";
          }
        }
        else
        {        
          $ret = array('status_ok' => true, 'id' => $op->key, 
                       'msg' => sprintf(lang_get('jira_bug_created'),$summary,$issue['fields']['project']['key']));
        }  
      }
    }
    catch (Exception $e)
    {
      $msg = "Create JIRA Ticket (REST) FAILURE => " . $e->getMessage();
      tLog($msg, 'WARNING');
      $ret = array('status_ok' => false, 'id' => -1, 'msg' => $msg . ' - serialized issue:' . serialize($issue));
    }
    return $ret;
  }  

  /**
   * on JIRA notes is called comment
   * 
   */
  public function addNote($issueID,$noteText,$opt=null)
  {
    try 
    {
      $op = $this->APIClient->addComment($noteText,$issueID);
      $ret = array('status_ok' => false, 'id' => null, 'msg' => 'ko');
      if(!is_null($op))
      {  
        if(isset($op->errors))
        {
          $ret['msg'] = $op->errors;
        }
        else
        {        
          $ret = array('status_ok' => true, 'id' => $op->key, 
                       'msg' => sprintf(lang_get('jira_comment_added'),$issueID));
        }  
      }
    }
    catch (Exception $e)
    {
      $msg = "Add JIRA Issue Comment (REST) FAILURE => " . $e->getMessage();
      tLog($msg, 'WARNING');
      $ret = array('status_ok' => false, 'id' => -1, 'msg' => $msg . ' - serialized issue:' . serialize($issue));
    }    
    return $ret;
  }
  
  /**
   *
   */
  public function getIssueTypes()
  {
    try
    {
      return $this->APIClient->getIssueTypes();
    }
    catch(Exception $e)
    {
      tLog(__METHOD__ . "  " . $e->getMessage(), 'ERROR');
    }
  }

  /**
   *
   */
  public function getPriorities()
  {
    try
    {
      return $this->APIClient->getPriorities();
    }
    catch(Exception $e)
    {
      tLog(__METHOD__ . "  " . $e->getMessage(), 'ERROR');
    }
  }

  /**
   *
   */
  public function getVersions()
  {
    $items = null;
    try
    {
      $items = $this->APIClient->getVersions((string)$this->cfg->projectkey);
    }
    catch(Exception $e)
    {
      tLog(__METHOD__ . "  " . $e->getMessage(), 'ERROR');
    }    
    return $items;
  }

  /**
   *
   */
  public function getComponents()
  {
    try
    {
      return $this->APIClient->getComponents((string)$this->cfg->projectkey);
    }
    catch(Exception $e)
    {
      tLog(__METHOD__ . "  " . $e->getMessage(), 'ERROR');
    }         
  }


  /**
   *
   */
  public function getCreateIssueFields()
  {
    try
    {
      return $this->APIClient->getCreateIssueFields((string)$this->cfg->projectkey);
    }
    catch(Exception $e)
    {
      tLog(__METHOD__ . "  " . $e->getMessage(), 'ERROR');
    }         
  }



  /**
   *
   */
  public function getIssueTypesForHTMLSelect()
  {
    return array('items' => $this->objectAttrToIDName($this->getIssueTypes()),
                 'isMultiSelect' => false);
  }

  /**
   *
   */
  public function getPrioritiesForHTMLSelect()
  {
    return array('items' => $this->objectAttrToIDName($this->getPriorities()),
                 'isMultiSelect' => false); 
  }

  /**
   *
   */
  public function getVersionsForHTMLSelect()
  {
    $input = array('items' => null,'isMultiSelect' => true);
    $items = $this->getVersions();
    if(!is_null($items))
    {
      $input['items'] = $this->objectAttrToIDName($items);
    }
    else
    {
      $input = null; 
    }  
    return $input;
  }

  /**
   *
   */
  public function getComponentsForHTMLSelect()
  {
    $items = $this->getComponents();
    $input = array('items' => null,'isMultiSelect' => true);
    if(!is_null($items))
    {
      $input['items'] = $this->objectAttrToIDName($items);
    }  
    else
    {
      $input = null; 
    }  
    return $input;
  }

 
  /**
   *
   * 
   */
  private function objectAttrToIDName($attrSet)
  {
    $ret = null;
    if(!is_null($attrSet))
    {
      $ic = count($attrSet);
      for($idx=0; $idx < $ic; $idx++)
      {
        $ret[$attrSet[$idx]->id] = $attrSet[$idx]->name; 
      }  
    }  
    return $ret;    
  }
  




  /**
   *
   * @author francisco.mancardi@gmail.com>
   **/
	public static function getCfgTemplate()
  {
    $tpl = "<!-- Template " . __CLASS__ . " -->\n" .
           "<issuetracker>\n" .
           "<username>JIRA LOGIN NAME</username>\n" .
           "<password>JIRA PASSWORD</password>\n" .
           "<uribase>https://testlink.atlassian.net/</uribase>\n" .
           "<!-- CRITIC - WITH HTTP getIssue() DOES NOT WORK -->\n" .
           "<uriapi>https://testlink.atlassian.net/rest/api/latest/</uriapi>\n" .
           "<uriview>https://testlink.atlassian.net/browse/</uriview>\n" .
           "<userinteraction>1/0</userinteraction>\n" .
           "<!-- 1: User will be able to manage following attributes from GUI -->\n" .
           "<!-- Issue Type, Issue Priority, Affects Versions, Components -->\n" .    
           "<!-- 0: values for attributes will be taken FROM this config XML from GUI -->\n" .
           "\n" .       
           "<!-- Configure This if you want be able TO CREATE ISSUES -->\n" .
           "<projectkey>JIRA PROJECT KEY</projectkey>\n" .
           "<issuetype>JIRA ISSUE TYPE ID</issuetype>\n" .
           "<issuepriority>JIRA ISSUE PRIORITY ID</issuepriority>\n" .
           "<!-- \n" . 
           "  <attributes>\n" . 
           "    <customFieldValues>\n" . 
           "      <customField>\n" . 
           "        <customfieldId>customfield_10800</customfieldId>\n" .
           "        <type>NumberField</type>" .
           "        <values><value>111</value></values>\n" .
           "      </customField>\n" . 
           "\n" .
           "      <customField>\n" . 
           "        <customfieldId>customfield_10900</customfieldId>\n" .
           "        <type>MultiSelect</type>" .
           "        <values><value>Yamaha Factory Racing</value>\n" .
           "                <value>Ducati</value></values>\n" .
           "      </customField>\n" . 
           "\n" .
           "    </customFieldValues>\n" .
           "  </attributes>\n" .
           "-->\n" .
           "</issuetracker>\n";
	  return $tpl;
  }



  /**
   *
   **/
  function canCreateViaAPI()
  {
    $status_ok = false;

    // The VERY Mandatory KEY   
    if( property_exists($this->cfg, 'projectkey') )
    {
      $pk = trim((string)($this->cfg->projectkey));
      $status_ok = ($pk !== '');
    } 
   
    if($status_ok && $this->cfg->userinteraction == 0)
    {
      $status_ok = property_exists($this->cfg, 'issuetype'); 
    }  

    return $status_ok;
  }

  /**
   *
   **/
  function processAttributes()
  {
    $attr = get_object_vars($this->cfg->attributes);
    foreach ($attr as $name => $elem) 
    {
      $name = (string)$name;
      switch($name)
      {
        case 'customFieldValues':
          $this->getCustomFieldsAttribute($name,$elem);
        break;
      }
    }
  }

 /**
  * supported types:
  * (see https://developer.atlassian.com/jiradev/api-reference/
  *            jira-rest-apis/jira-rest-api-tutorials/
  *            jira-rest-api-example-create-issue#
  *            JIRARESTAPIExample-CreateIssue-Exampleofcreatinganissueusingcustomfields)
  *
  * ---------------------------------------------------------  
  * Single Value (simple) Group:
  * ---------------------------------------------------------
  *
  * DatePickerField
  * "customfield_10002": "2011-10-03"
  *
  * DateTimeField
  * "customfield_10003": "2011-10-19T10:29:29.908+1100"  *
  *
  * FreeTextField
  * "customfield_10004": "Free text goes here.  Type away!"
  *
  * NumberField
  * "customfield_10010": 42.07
  *
  * ---------------------------------------------------------  
  * Pair Value (simple) Group:
  * ---------------------------------------------------------
  *
  * RadioButtons
  * "customfield_10012": { "value": "red" }
  *  
  * SelectList
  * "customfield_10013": { "value": "red" }
  *
  * ---------------------------------------------------------
  * Multiple Values (simple) Group:
  * ---------------------------------------------------------
  *
  * Labels  (PHP Array of strings)
  * "customfield_10006": ["examplelabelnumber1", "examplelabelnumber2"]
  *
  *
  * ---------------------------------------------------------
  * Multiple Values (COMPLEX) Group:
  * ---------------------------------------------------------
  *
  * MultiGroupPicker (access key -> name)
  * "customfield_10007": [{ "name": "admins" }, { "name": "jira-dev" }, 
  *                       { "name": "jira-users" }]
  *
  * MultiUserPicker (access key -> name)
  * "customfield_10009": [ {"name": "jsmith" }, {"name": "bjones" }, {"name": "tdurden" }]
  * 
  * MultiSelect (access key -> value)
  * "customfield_10008": [ {"value": "red" }, {"value": "blue" }, {"value": "green" }]
  *
  *

  *
  * 
  **/
  function getCustomFieldsAttribute($name,$objCFSet)
  {
    $cfSet = get_object_vars($objCFSet);
    $cfSet = $cfSet['customField'];    

    foreach ($cfSet as $cf)
    {
      $cf = (array)$cf;    
      $cfJIRAID = $cf['customfieldId']; 
      $valueSet = (array)$cf['values'];        
      $loop2do = count($valueSet);

      $dummy = null;
      $cfType = strtolower((string)$cf['type']);
      switch($cfType)
      {
        case 'numberfield':
          $dummy = (float)$valueSet['value'];
        break;

        case 'datepickerfield':
        case 'datetimefield':
        case 'freetextfield':
          $dummy = (string)$valueSet['value'];
        break;

        case 'radiobutton':
        case 'selectlist':
          // "customfield_10012": { "value": "red" }
          $dummy = array('value' => (string)$valueSet['value']);
        break;

        case 'labels':
          for($vdx=0; $vdx <= $loop2do; $vdx++)
          {
            $dummy[] = (string)$valueSet['value'][$vdx];
          }
        break;
      
        case 'multigrouppicker':
        case 'multiuserpicker':
          // access key -> name
          for($vdx=0; $vdx <= $loop2do; $vdx++)
          {
            $dummy[] = array('name' => (string)$valueSet['value'][$vdx]);
          }
        break;

        case 'multiselect': 
          // access key -> value)
          for($vdx=0; $vdx <= $loop2do; $vdx++)
          {
            $dummy[] = array('value' => (string)$valueSet['value'][$vdx]);
          }
        break;
      }      
      $this->issueAttr[$cfJIRAID] = $dummy; 
    } 
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


  /**
   *
   * If connection fails $this->defaultResolvedStatus is null
   *
   */
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
    $this->resolvedStatus->byCode = array();
    if(!is_null($statusCfg['status']))
    {  
      foreach($statusCfg['status'] as $cfx)
      {
        $e = (array)$cfx;
        $this->resolvedStatus->byCode[$e['code']] = $e['verbose'];
      }
    }
    $this->resolvedStatus->byName = array_flip($this->resolvedStatus->byCode);
  }



}
