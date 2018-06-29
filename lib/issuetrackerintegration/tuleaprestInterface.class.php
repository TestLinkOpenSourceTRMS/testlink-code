<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource tuleaprestInterface.class.php
 * @author Claudia Dabu (Communication & Systems)
 *
 * @internal revisions
 * @since 1.9.14
 *
 **/

require_once(TL_ABS_PATH . "/third_party/tuleap-php-api/lib/tuleap-rest-api.php");

class tuleaprestInterface extends issueTrackerInterface
{
    private $APIClient;

    private $trackerID;
    private $URIBase;

    
    /**
     * Construct and connect to BTS.
     * Can be overloaded in specialized class
     *
     * @param str $type (see tlIssueTracker.class.php $systems property)
     **/
    function __construct($type,$config,$name)
    {
        $this->name = $name;
        $this->interfaceViaDB = false;
        $this->defaultResolvedStatus = array();
        $this->defaultResolvedStatus[] = 'Invalid';
        $this->defaultResolvedStatus[] = 'Wont Fix';
        $this->defaultResolvedStatus[] = 'Fixed';
        $this->defaultResolvedStatus[] = 'Works for me';
        $this->defaultResolvedStatus[] = 'Duplicate';
        $this->methodOpt = array('buildViewBugLink' => array('addSummary' => true, 'colorByStatus' => false));
        $this->connected = false;

        if( !$this->setCfg($config) )
        {
            return false;
        } else {
          // check the tracker ID
          if (property_exists($this->cfg, 'tracker')) {
            $this->trackerID = trim((string) $this->cfg->tracker);
            if ( strlen($this->trackerID) > 0
                 && ! $this->checkTrackerIDSyntax($this->trackerID) ) {
              return false;
            }
          } else {
            // tracker ID may be absent (bug creation from Testlink will not be possible)
            $this->trackerID = "";
          }

          // check the base URI
          if (property_exists($this->cfg, 'uribase')) {
            $this->URIBase = trim((string) $this->cfg->uribase);
            if ( strlen($this->URIBase) > 0
                 && ! $this->checkURLSyntax($this->URIBase) ) {
              return false;
            }
          } else {
            return false;
          }
        }

        $this->completeCfg();
        $this->connect();
        $this->setResolvedStatusCfg();

    }

    /**
     * checks a tracker id for validity (a numeric value)
     *
     * @param string tracker ID
     *
     * @return bool returns true if the tracker id has the right format
     **/
    private function checkTrackerIDSyntax($trackerID)
    {
      $valid = true;
      $blackList = '/\D/i';
      if (preg_match($blackList, $trackerID)) {
        $valid = false;
      } else {
        $valid = (intval($trackerID) > 0);
      }

      return $valid;
    }

    /**
     * checks a URL for validity
     *
     * @param string URL
     *
     * @return bool returns true if the param is an URL
     **/
    private function checkURLSyntax($url) {
      return (filter_var($url, FILTER_VALIDATE_URL)
              && stripos($url, "http") === 0);
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
     * Set resolved status
     *
     * @author Aurelien TISNE <aurelien.tisne@c-s.fr>
     **/
    public function setResolvedStatusCfg()
    {
        $statusCfg = $this->getResolvedStatus();
        if (! $statusCfg) {
            if( property_exists($this->cfg, 'resolvedstatus') ) {
              $statusCfg = (array)$this->cfg->resolvedstatus;
            }
            else {
              $statusCfg['status'] = $this->defaultResolvedStatus;
            }
        }

        $this->resolvedStatus = new stdClass();
        foreach($statusCfg['status'] as $cfx) {
            $this->resolvedStatus->byName[] = $cfx;
        }
    }


    /**
     * Get resolved status from ITS
     *
     * @author Aurelien TISNE <aurelien.tisne@csgroup.eu>
     **/
    public function getResolvedStatus()
    {
        if (!$this->isConnected())
             return null;

        if ($this->trackerID == '')
            return null;

        $ret = null;
        try {
            $tracker = $this->APIClient->getTrackerById($this->trackerID);
            if ($tracker) {
                // field ID containing the status semantic
                $statusID = $tracker->semantics->status->field_id;
                // opened values ID
                $statusValuesID = $tracker->semantics->status->value_ids;
                //$ret = array();
                // retrieve the field containing the status semantic
                $status = $this->getField($tracker, $statusID);
                if (! $status )
                    throw new Exception('The field ' . $statusID . ' cannot be found in the tracker "'
                                        . $tracker->label . '" (' . $tracker->id . ').');
                // retrieve the labels of closed status
                $ret['status'] = $this->getClosedLabels($status, $statusValuesID);
                // check that all labels have been found
                if ( count($ret['status']) != (count($status->values) - count($statusValuesID)) )
                    throw new Exception('Some labels was not found.');
            } else
                throw new Exception('The tracker ' . $this->trackerID . ' was not found.');
        } catch(Exception $e) {
            tLog($e->getMessage(),'ERROR');
            $ret = null;
        }

        return $ret;
    }

    /**
     * Retrieve a field from a tracker
     *
     * @param object $tracker A tracker
     * @param string $fieldID A tracker item ID
     *
     * @author Aurelien TISNE <aurelien.tisne@csgroup.eu>
     **/
    private function getField($tracker, $fieldID) {
        $i = count($tracker->fields);
        $field = null;
        while ($i > 0 && ! $field) {
            if ($tracker->fields[$i - 1]->field_id == $fieldID)
                $field = $tracker->fields[$i - 1];
            else
                $i -= 1;
        }

        return $field;
    }

    /**
     * Retrieve labels of closed status values ID from the status field
     *
     * @param object $statusField Tracker field containing the status semantic
     * @param array $valuesID List of opened values ID
     *
     * @author Aurelien TISNE <aurelien.tisne@csgroup.eu>
     **/
    private function getClosedLabels($statusField, $openValuesID) {
        if (! property_exists($statusField, "values"))
            return null;

        $ret = array();
        foreach($statusField->values as $value) {
            if ( ! in_array($value->id, $openValuesID) )
                $ret[] = $value->label;
        }

        return $ret;
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

         $processCatch = false;

         try
         {

             $this->APIClient =  new tuleap((string)trim($this->cfg->uriapi),
                 (string)trim($this->cfg->username), (string)trim($this->cfg->password));

             try
             {
                 $this->connected = $this->APIClient->Connect();

             }
             catch(Exception $e)
             {
                 $processCatch = true;
             }
         }
         catch(Exception $e)
         {
             $processCatch = true;
         }

         if($processCatch)
         {
             $logDetails = '';
             foreach(array('uribase', 'username') as $v)
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
      **/
     function buildStatusHTMLString($status)
     {
     if (in_array($status, $this->resolvedStatus->byName) )// Closed type status
         {
             $str = "<del>" . $status . "</del>";
         }else{
             $str = $status;
         }
         return "[{$str}] ";
     }

     /**
      *
      *
      **/
     function getIssue($issueID)
     {
         if (!$this->isConnected())
         {
             return false;
         }

         try
         {
             $issue = $this->APIClient->getArtifactById((int)$issueID);
             if( !is_null($issue) && is_object($issue) )
             {
                 $issue->IDHTMLString = "<b>{$issueID} : </b>";
                 //$issue->statusCode = $issue->State;
                 $issue->statusVerbose = $issue->status;
                 $issue->statusHTMLString = $this->buildStatusHTMLString($issue->status);
                 $issue->summaryHTMLString = $issue->title;

                 $issue->isResolved = isset($this->resolvedStatus->byName[$issue->statusVerbose]);
             }

         }
         catch(Exception $e)
         {
             tLog($e->getMessage(),'ERROR');
             $issue = null;
         }
         return $issue;
     }


     /**
      * Returns status for issueID
      *
      * @param string issueID
      *
      * @return boolean
      **/
     function getIssueStatusCode($issueID)
     {
         $issue = $this->getIssue($issueID);
         return (!is_null($issue) && is_object($issue))? $issue->statusCode : false;
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
      * Returns a configuration template
      *
      * @return string
      **/
     public static function getCfgTemplate()
     {
         $template = "<!-- Template " . __CLASS__ . " -->\n" .
             "<issuetracker>\n" .
             "<username>TULEAP LOGIN NAME</username>\n" .
             "<password>TULEAP PASSWORD</password>\n" .
             "<!-- URL where the bug tracker can be found -->\n" .
             "<uribase>https://" . $_SERVER['SERVER_NAME'] . "</uribase>\n".
             "<!-- You do not need to configure uriapi, uriview, uricreate  -->\n" .
             "<!-- if you use a standard installation -->\n" .
             "<!-- In this situation, do not copy these three config lines -->\n" .
             "<uriapi>https://" . $_SERVER['SERVER_NAME'] . "/api</uriapi>\n".
             "<uriview>https://" . $_SERVER['SERVER_NAME'] . "/plugins/tracker/?aid=</uriview>\n".
             "<uricreate>https://" . $_SERVER['SERVER_NAME'] . "/plugins/tracker/?func=new-artifact&tracker=TULEAP TRACKER ID</uricreate>\n".
             "<!-- Configure tracker if you want to add issues from TestLink -->\n" .
             "<!-- Give the ID of the Tuleap tracker where Testlink can create -->\n" .
             "<!-- an artefact using the credentials provided above -->\n" .
             "<tracker>TULEAP TRACKER ID</tracker>\n" .
             "<!-- Default resolved status -->\n" .
             "<!-- Resolved status are also retrieved from the tracker configuration -->\n" .
             "<!-- when defined (semantic) -->\n" .
             "<resolvedstatus>\n" .
             "<status>Fixed</status>\n" .
             "<status>Invalid</status>\n" .
             "<status>Wont Fix</status>\n" .
             "<status>Works for me</status>\n" .
             "<status>Duplicate</status>\n" .
             "</resolvedstatus>\n" .
             "</issuetracker>\n";
         return $template;
     }


     /**
      *
      * check for configuration attributes than can be provided on
      * user configuration, but that can be considered standard.
      * If they are MISSING we will use 'these carved on the stone values'
      * in order to simplify configuration.
      *
      *
      **/
     function completeCfg()
     {
         // '/' at uribase name creates issue with API
         $this->URIBase = trim($this->URIBase, "/");

         $base =  $this->URIBase . '/';

         if( !property_exists($this->cfg,'uriapi') )
         {
             $this->cfg->uriapi = $base . 'api';
         }

         if( !property_exists($this->cfg,'uriview') )
         {
             $this->cfg->uriview = $base . 'plugins/tracker/?aid=';
         }

         if( !property_exists($this->cfg,'uricreate') )
         {
           if ( $this->trackerID != "" ) {
             $this->cfg->uricreate = $base . 'plugins/tracker/?tracker='
               . $this->trackerID . '&func=new-artifact';
           } else {
             $this->cfg->uricreate = '';
           }
         }

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
             $status_ok = (!is_null($issue) && is_object($issue));
         }
         return $status_ok;
     }

     /**
      *
      */
     public function addIssue($summary, $description, $opt=null)
     {
         try
         {
             $issue = array('tracker' => (int)$this->trackerID,
                 'summary' => $summary,
                 'description' => $description);

             $op = $this->APIClient->createIssue((int)$this->trackerID, $summary, $description);

             if (is_null($op)) {
               throw new Exception("Something's wrong when creating an artefact");
             } else {
               $ret = array('status_ok' => true, 'id' => (string)$op->id,
                            'msg' => sprintf(lang_get('tuleap_bug_created'), $summary, (string)$op->tracker->project->id));
             }
         }
         catch (Exception $e)
         {
             $msg = "Create artifact FAILURE => " . $e->getMessage();
             tLog($msg, 'WARNING');
             $ret = array('status_ok' => false, 'id' => -1, 'msg' => $msg);
         }
         return $ret;
     }

     /**
      *
      */
     public function addNote($bugId, $noteText, $opt=null)
     {
         if (!$this->isConnected())
             return null;

         try{
             $noteText = "Reporter: " . $opt->reporter . " <" . $opt->reporter_email . ">\n" . $noteText;
             $op =  $this->APIClient->addTrackerArtifactMessage( (int)$bugId, $noteText);
             $ret = array('status_ok' => true,
                          'msg' => sprintf(lang_get('tuleap_bug_comment'), $noteText));
         }catch (Exception $e){
             $msg = "Add note FAILURE for bug " . $bugId . " => " . $e->getMessage();
             tLog($msg, 'WARNING');
             $ret = array('status_ok' => false, 'msg' => $msg);
         }

         return $ret;
     }


     /**
      *
      **/
     function canCreateViaAPI()
     {
         return ($this->trackerID !== '');
     }


}
?>
