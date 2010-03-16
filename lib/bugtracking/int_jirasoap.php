<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * Filename $RCSfile: int_jirasoap.php,v $
 *
 * @version $Revision: 1.8 $
 * @modified $Date: 2010/03/16 08:33:40 $ $Author: amkhullar $
 *
 * @author amitkhullar <amkhullar@gmail.com>
 *
 * Integration with JIRA Using SOAP Interface.
 *
 * 20100316 - amitkhullar - BugID 3287
 *
**/
/** Interface name */
define('BUG_INTERFACE_CLASSNAME',"jirasoapInterface");
//set_error_handler("exception_error_handler", E_ALL);
class jirasoapInterface extends bugtrackingInterface
{
    var $dbHost = null;
    var $dbConnection = null;
    var $connected = false;
    var $dbCharSet = null;
    var $dbType = null;

	//members to connect through SOAP to the bugtracking information
    var $jiraUsername = BUG_TRACK_USERNAME;
    var $jiraPassword = BUG_TRACK_PASSWORD;
    var $baseURL = BUG_TRACK_HREF;
    //var $bugDetails = BUG_TRACK_DETAILS;
    var $soapURL =  null;
    var $client = null;

    //Variables for storing the Jira Issue Values
    var $checkURL = false;
    protected $status_map = array();
    protected $issue = null;
    protected $issue_status = null;
    protected $issue_status_id = null;
    protected $issue_summary = null;
    protected $issue_due_date = null;


    /**
     * Constructor of bugtrackingInterface
     * put special initialization in here
     *
     * @version 1.0
     * @author amitkhullar <amkhullar@gmail.com>
     **/
    function jirasoapInterface()
    {
        $soapURL =  $this->baseURL . BUG_TRACK_SOAP_HREF;
        $this->showBugURL =  $this->baseURL . BUG_TRACK_SHOW_BUG_HREF;
        $this->enterBugURL = $this->baseURL . BUG_TRACK_ENTER_BUG_HREF;
        // Do nothing at constructor
        $checkURL = $this->is_valid_url(($this->soapURL));
        if (!$checkURL)
        {
            // Get all the status in JIRA
            $this->client = new soapclient($soapURL);
            $soap_token = $this->soap_login();

            $status = array();
            $status = $this->client->getStatuses($soap_token);

            foreach ($status as $key => $pair)
            {
            	$this->status_map[$pair->name]=$pair->id;
            }
        }
        else
        {
            tLog('Connect to Bug Tracker URL fails!!! ', 'ERROR');
        }
    }
    /**
     * establishes the soap connection and logins to the
     * bugtracking system
     *
     * @return token if the login was succesful
     *
     * @version 1.0
     * @author amitkhullar <amkhullar@gmail.com>
     **/
    function soap_login()
    {
        try
        {
            $token = $this->client->login($this->jiraUsername, $this->jiraPassword);
            return $token;
        }
        catch (Exception $e)
        {
         	tLog($e->getMessage(), 'WARNING');
        }
    }
    /**
     * establishes the soap connection to the bugtracking system
     *
     * @return null if issue not found
     *         value  'issue_status_id' if jira_summary is false
     *         value  'issue_status_id','issue_summary','issue_due_date' if jira_summary is true
     *
     * @version 1.0
     * @author amitkhullar <amkhullar@gmail.com>
     **/
    function soap_request($jira_key=null, $jira_summary = false)
    {
        try
        {
            $return_var = null;

            $soap_token = $this->soap_login();
            if (!is_null($jira_key)) // Perform the Get Issue operation
            {
                $issue = $this->client->getIssue($soap_token, $jira_key);

                if (!is_null($issue))
                {
                    $issue_status_id = $issue->status;
                    $issue_summary = $issue->summary;

                    $issue_due_date = !is_null($issue->duedate)? ($issue->duedate): null;

                    if ($jira_summary )// If jira_summary is true return the fileds below
                    {
                        return compact('issue_status_id','issue_summary', 'issue_due_date');
                    }
                    elseif ($issue_status_id) // else just return the issue status id
                    {
                        return $issue_status_id;
                    }
                }
                else
                {
                    return $return_var; //Issue not found so return null
                }

            }
        }
        catch (Exception $e)
        {
         	tLog($jira_key . "-" . $e->getMessage(), 'WARNING');
        }
    }

    /**
     * establishes the soap connection to the bugtracking system
     *
     * @return bool returns true if the soap connection was established and the
     * wsdl could be downloaded, false else
     *
     * @version 1.0
     * @author amitkhullar <amkhullar@gmail.com>
     **/
    function connect()
    {

        $this->connected = true;
        return $this->connected;
    }

    /**
     * returns state of the soap connection
     *
     * @return bool true if the soap connection is established, false else
     *
     * @version 1.0
     * @author amitkhullar <amkhullar@gmail.com>
     **/
    function isConnected()
    {
        return $this->connected;
    }

    /**
     * closes the soap connection (if any)
     *
     * @version 1.0
     * @author amitkhullar <amkhullar@gmail.com>
     **/
    function disconnect()
    {
        $this->connected = false;
    }
    /**
     * Return the URL to the bugtracking page for viewing
     * the bug with the given id.
     *
     * @param int id the bug id
     *
     * @return string returns a complete URL to view the bug
     *
     **/
    function buildViewBugURL($id)
    {
        return ($this->showBugURL.$id);
    }


    /**
     * Returns the status in a readable form (HTML context) for the bug with the given id
     *
     * @param int id the bug id
     *
     * @return string returns the status (in a readable form) of the given bug if the bug
     * 		was found, else false
     *
     * @version 1.0
     * @author amitkhullar <amkhullar@gmail.com>
     **/
    function getBugStatusString($id)
    {
        $status_desc = null;
        $jira_id = $this->soap_request($id);

        //if the bug wasn't found the status is null else we simply display the bugID with status
        if (!is_null($jira_id))
        {
            $status_desc = array_search($jira_id, $this->status_map);

			if (strcasecmp($status_desc, 'closed') == 0 || strcasecmp($status_desc, 'resolved') == 0 )
            {
                $status_desc = "<del>" . $status_desc . "</del>";
            }
            $status_desc = "<b>" . $id . ": </b>[" . $status_desc  . "] " ;
        }
        else
        {
            $status_desc = "The BUG Id-".$id." does not exist in BTS";
        }
        return $status_desc;
    }

    /**
     * default implementation for fetching the bug summary from the
     * bugtracking system
     *
     * @param int id the bug id
     *
     * @return string returns the bug summary (if bug is found), or false
     *
     * @version 1.0
     * @author amitkhullar <amkhullar@gmail.com>
     **/
    function getBugSummaryString($id)
    {
        $summary_string = "Error: Summary not found in JIRA for Ticket#$id";
        $jira_summary  = $this->soap_request($id,TRUE);

        if (!is_null($jira_summary))
        {
            extract($jira_summary);
            $summary_string = $issue_summary;

            $due_date = $this->parse_date($issue_due_date);

            $summary_string = $summary_string . '<b> [' . $due_date . ']</b> ';
        }
        return $summary_string;
    }
    /**
     * returns the URL which should be displayed for entering bugs
     *
     * @return string returns a complete URL
     *
     * @version 1.0
     * @author amitkhullar <amkhullar@gmail.com>
     **/
    function parse_date($due_date)
    {
        if (!is_null($due_date))
        {
            $due_date = date_parse($due_date);
            $due_date = ((gmmktime(0, 0, 0, $due_date['month'], $due_date['day'], $due_date['year'])));
            $due_date = gmstrftime("%d %b %Y",($due_date));
            return $due_date ;
        }
        else
        {
            return "No Date";
        }
    }

    /**
     * returns the URL which should be displayed for entering bugs
     *
     * @return string returns a complete URL
     *
     * @version 1.0
     * @author amitkhullar <amkhullar@gmail.com>
     **/
    function getEnterBugURL()
    {
        return $this->enterBugURL;
    }

    /**
     * default implementation for generating a link to the bugtracking page for viewing
     * the bug with the given id in a new page
     *
     * @param int id the bug id
     *
     * @return string returns a complete URL to view the bug (if found in db)
     *
     * @version 1.0
     * @author Toshiyuki Kawanishi
     * @since 1.8 RC 3
     **/
    function buildViewBugLink($bugID,$bWithSummary = false)
    {
        global $tlCfg;

        $link = "<a href='" .$this->buildViewBugURL($bugID) . "' target='_blank'>";

        $status = $this->getBugStatusString($bugID);

        if (!is_null($status))
        {
            $link .= $status;
        }
        else
        {
            $link .= $bugID;
        }
        if ($bWithSummary)
        {
            $summary = $this->getBugSummaryString($bugID);
            if (!is_null($summary))
            {
                $link .= " - " . $summary;
            }
        }
        $link .= "</a>";
        return $link;
    }
    /**
     * checks a bug id for validity
     *
     * @return bool returns true if the bugid has the right format, false else
     **/
    function checkBugID($id)
    {
        $status_ok = true;
        if(trim($id) == "")
        {
            $status_ok = false;
        }
        if($status_ok)
        {
            $forbidden_chars = '/[!|£%&()\/=?]/';
            if (preg_match($forbidden_chars, $id))
            {
                $status_ok = false;
            }
        }
        return $status_ok;
    }
    /**
     * Overloaded function checks a bug id for existence
     *
     * @return bool returns true if the bugid exists, false else
     **/
    function checkBugID_existence($id)
    {
        $status_ok = 1; //BUG 3287
        $status = $this->checkBugID($id);
        if ($status)
        {
            $issue_exists = $this->getBugStatusString($id);
            if (!is_null($issue_exists))
            {
                if ((stristr($issue_exists, "does not exist") == TRUE))
                {
                    $status_ok = 0;
                }
            }
        }
        return $status_ok;
    }
    /**
     * this function establishes the checks if the url is valid
     *
     * @return bool returns true if the connection was established,
     * else false
     *
     * @version 1.0
     * @author amitkhullar <amkhullar@gmail.com>
     **/
    function is_valid_url($url)
    {
        $url = @parse_url($url);

        if ( !$url)
        {
            return false;
        }

        $url = array_map('trim', $url);
        $url['port'] = (!isset($url['port'])) ? 80 : (int)$url['port'];
        $path = (isset($url['path'])) ? $url['path'] : '';

        if ($path == '')
        {
            $path = '/';
        }

        $path .= ( isset ( $url['query'] ) ) ? "?$url[query]" : '';

        if ( isset ( $url['host'] ) AND $url['host'] != gethostbyname ( $url['host'] ) )
        {
            if ( PHP_VERSION >= 5 )
            {
                $headers = get_headers("$url[scheme]://$url[host]:$url[port]$path");
            }
            else
            {
                $fp = fsockopen($url['host'], $url['port'], $errno, $errstr, 30);

                if (!$fp)
                {
                    return false;
                }
                fputs($fp, "HEAD $path HTTP/1.1\r\nHost: $url[host]\r\n\r\n");
                $headers = fread ( $fp, 128 );
                fclose ( $fp );
            }

            $headers = ( is_array ( $headers ) ) ? implode ( "\n", $headers ) : $headers;
            return ( bool ) preg_match ( '#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers );
        }
        return false;
    }
}

// ##################################################################################
//
// ErrorHandler Manager Class
//
// ##################################################################################
class ErrorHandler extends Exception
{
    protected $severity;

    public function __construct($message, $code, $severity, $filename, $lineno)
    {
        $this->message = $message;
        $this->code = $code;
        $this->severity = $severity;
        $this->file = $filename;
        $this->line = $lineno;
    }

    public function getSeverity()
    {
       	return $this->severity;
    }

    function exception_error_handler($errno, $errstr, $errfile, $errline )
    {
        throw new ErrorHandler($errstr, 0, $errno, $errfile, $errline);
    }

}
?>
