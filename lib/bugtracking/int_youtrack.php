<?php
/**
 * Created by PhpStorm.
 * User: Sergey Andreev (sergey.andreev@jetbrains.com])
 * Date: Jun 17, 2010
 */


define('BUG_INTERFACE_CLASSNAME', "youtrackInterface");

class youtrackInterface extends bugtrackingInterface {

    //members to store the bugtracking information, these values are
    //set in the actual subclasses of this class
    var $dbHost = null;
    var $dbName = null;
    var $dbUser = null;
    var $dbPass = null;
    var $dbType = null;
    var $showBugURL = null;
    var $enterBugURL = null;
    var $dbCharSet = null;
    var $tlCharSet = null;

    //members to work with YouTrack
    var $username = YOUTRACK_USERNAME;
    var $password = YOUTRACK_PASSWORD;
    var $baseURL = YOUTRACK_URL;
    var $urlRest = null;
    var $urlStates = null;
    var $urlLogin = null;
    var $urlIssue = null;
    var $urlCreateIssue = null;
    var $urlGetIssue = null;
    var $cookie = null;
    var $statusMap = array();

    //private vars don't touch
    var $dbConnection = null;
    var $Connected = false;

    /*
     *
     * FUNCTIONS NOT CALLED BY TestLink (helpers):
     *
     **/

    /**
     * Constructor of youtrackInterface
     * Defines all needed variables, performs login (stores recieved cookies), and gets info abour available statuses
     *
     * @return void
     */
    function youtrackInterface() {
        $this->urlRest = $this->baseURL . 'rest/';
        $this->urlStates = $this->urlRest . BUG_TRACK_REST_STATES;
        $this->urlLogin = $this->urlRest . BUG_TRACK_REST_LOGIN;
        $this->urlIssue = $this->urlRest . BUG_TRACK_SHOW_ISSUE;
        $this->urlCreateIssue = $this->urlIssue;
        $this->urlGetIssue = $this->urlIssue;
        $this->showBugURL = $this->baseURL . BUG_TRACK_SHOW_ISSUE;
        $this->enterBugURL = $this->baseURL . BUG_TRACK_NEW_ISSUE;

        try {
            $this->session = curl_init($this->urlLogin);
            $this->login();
            $this->getStatuses();
        }
        catch (Exception $e) {
            tLog($e->getMessage(), 'ERROR');
        }

    }

    /**
     * This method perform login to YouTrack
     * with Username and Password from config file (youtrack.php.ini)
     *
     * @return resource
     */
    function login() {
        $loginData = 'login=' . $this->username . '&password=' . $this->password;

        try {
            $this->session = curl_init();
            curl_setopt($this->session, CURLOPT_URL, $this->urlLogin);
            curl_setopt($this->session, CURLOPT_POST, true);
            curl_setopt($this->session, CURLOPT_POSTFIELDS, $loginData);
            curl_setopt($this->session, CURLOPT_HEADER, false);
            curl_setopt($this->session, CURLOPT_HEADERFUNCTION, array($this, 'readHeader'));
            curl_setopt($this->session, CURLOPT_RETURNTRANSFER, true);
            curl_close($this->session);

            return $this->session;
        }
        catch (Exception $e) {
            tLog($e->getMessage(), 'WARNING');
        }
    }

    /**
     * This method defines cookies owner User
     *
     * @return mixed xml that describes cookies owner
     */
    function getUserName() {
        $tmp = $this->urlRest . 'user/current';
        try {
            $session = curl_init();
            curl_setopt($session, CURLOPT_URL, $tmp);
            curl_setopt($session, CURLOPT_POST, false);
            curl_setopt($session, CURLOPT_HEADER, false);
            curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($session, CURLOPT_COOKIE, $this->cookie);
            curl_setopt($session, CURLOPT_VERBOSE, true);
            $response = curl_exec($session);
            curl_close($session);
        }
        catch (Exception $e) {
            tLog($e->getMessage(), 'WARNING');
        }

        return $response;
    }

    /**
     * This method grabs cookies from response on Login operation
     * and stores them to reuse in other requests
     *
     * @param  $string - HTTP response headers
     * @return void
     */
    function readHeader($string) {
        if (!strncmp($string, "Set-Cookie:", 11)) {
            $cookie_str = trim(substr($string, 11, -1));
            $this->cookie = explode("\n", $cookie_str);
            $this->cookie = explode('=', $this->cookie[0]);
            $cookie_name = trim(array_shift($this->cookie));
            $cookiearr[$cookie_name] = trim(implode('=', $this->cookie));
        }
        $this->cookie = "";
        if (trim($string) == "") {
            foreach ($cookiearr as $key => $value)
            {
                $this->cookie .= "$key=$value; ";
            }
        }
    }

    /**
     * This method gets issue from YouTrack by its ID
     *
     * @param  $id - issue ID in YouTrack
     * @return xml formatted issue or error response
     */

    function getIssue($id) {
        $this->session = curl_init();
        $get_issue_url = $this->urlGetIssue . $id;

        try {
            curl_setopt($this->session, CURLOPT_URL, $get_issue_url);
            curl_setopt($this->session, CURLOPT_POST, false);
            curl_setopt($this->session, CURLOPT_HEADER, false);
            curl_setopt($this->session, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->session, CURLOPT_COOKIE, $this->cookie);
            curl_setopt($this->session, CURLOPT_VERBOSE, false);

            $response = curl_exec($this->session);
            curl_close($this->session);
        }
        catch (Exception $e) {
            tLog($e->getMessage(), 'WARNING');
        }

        return $response;
    }

    function isIssueExists($id) {
        $this->session = curl_init();
        $get_issue_exists = $this->urlGetIssue . $id . "/exists";

        try {
            curl_setopt($this->session, CURLOPT_URL, $get_issue_exists);
            curl_setopt($this->session, CURLOPT_POST, false);
            curl_setopt($this->session, CURLOPT_HEADER, false);
            curl_setopt($this->session, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->session, CURLOPT_COOKIE, $this->cookie);
            curl_setopt($this->session, CURLOPT_VERBOSE, false);

            $response = curl_exec($this->session);
            curl_close($this->session);
        }
        catch (Exception $e) {
            tLog($e->getMessage(), 'WARNING');
        }

        return $response;
    }

    /**
     * This methods gets list of available Statuses from YouTrack
     * @return void
     */
    function getStatuses() {
        try {
            $session = curl_init();
            curl_setopt($session, CURLOPT_URL, $this->urlStates);
            curl_setopt($session, CURLOPT_POST, false);
            curl_setopt($session, CURLOPT_HEADER, false);
            curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($session, CURLOPT_COOKIE, $this->cookie);
            curl_setopt($session, CURLOPT_VERBOSE, false);

            $response = curl_exec($session);
            curl_close($session);
        }
        catch (Exception $e) {
            tLog($e->getMessage(), 'WARNING');
        }

        try {
            $xml = new SimpleXMLElement($response);
            foreach ($xml->state as $state)
            {
                $this->statusMap[(string) $state['name']] = (string) $state['resolved'];
            }
        }
        catch (Exception $e) {
            tLog($e->getMessage(), 'WARNING');
        }
    }

    /**
     * Mock method overloaded from the bugtrackingInterface.
     * This function fakes establishing the database connection to the
     * bugtracking system.
     *
     **/
    function connect() {
        $this->Connected = true;
        return $this->Connected;
    }

    /**
     * Mock method overloaded from the bugtrackingInterface.
     * This function returns the fake state of the db connection
     *
     **/
    function isConnected() {
        return $this->Connected;
    }

    /**
     * Mock method overloaded from the bugtrackingInterface.
     * This function imitates closing the db connection (if any)
     *
     **/
    function disconnect() {
        $this->Connected = false;
    }

    /**
     * Method overloaded from the bugtrackingInterface.
     * Returns the URL to the bugtracking page for viewing
     * the bug with the given ID.
     * This function is not directly called by TestLink at the moment
     *
     * @param bug ID in YouTrack
     *
     * @return string returns a complete URL to view the given bug, or false if the bug
     *             wasn't found
     *
     **/
    function buildViewBugURL($id) {
        return ($this->showBugURL . $id);
    }

    /**
     * Method overloaded from the bugtrackingInterface.
     * Returns the status of the bug with the given ID.
     * This function is not directly called by TestLink.
     *
     * @param  $id - bug id in YouTrack
     * @return returns the status of the given bug, or "error" if the bug
     *            was not found
     */
    function getBugStatus($id) {
        $issue_status_id = "error";
        try {
            $issueXml = $this->getIssue($id);
            $xml = new SimpleXMLElement($issueXml);
            foreach ($xml->field as $field) {
                switch ((string) $field['name']) {
                    case 'state':
                        $issue_status_id = (string) $field->value;
                        break;
                }
            }
        } catch (Exception $e) {
            tLog($e->getMessage(), 'WARNING');
        }
        return $issue_status_id;
    }

    /**
     * Method overloaded from the bugtrackingInterface.
     * Returns the status in a readable form for the bug with the given ID.
     * This function is not directly called by TestLink.
     *
     * @param bug ID in YouTrack
     *
     * @return returns the status (in a readable form) of the given bug, or "error"
     *             if the bug is not found
     **/
    function getBugStatusString($id) {
        $status_name = "error";
        $status = $this->getBugStatus($id);
        $status_completion = $this->statusMap["$status"];

        if ('false' == $status_completion) {
            $status_name = "<b>" . $id . "</b> (" . $status . ") ";
        }
        elseif ('true' == $status_completion) {
            $status_name = "<del>" . $status . "</del>";
            $status_name = "<b>" . $id . "</b> (" . $status_name . ") ";
        } else {
            $status_name = "Bug " . $id . " does not exist in YouTrack, or you don't have permissions to access.";
        }

        return $status_name;
    }


    /*
     *
     * FUNCTIONS CALLED BY TestLink:
     *
     **/


    /**
     * Method overloaded from the bugtrackingInterface.
     * default implementation for fetching the bug summary from the
     * bugtracking system
     *
     * @param bug ID in YouTrack
     *
     * @return string returns the bug summary (if bug is found), or Error message
     *
     **/
    function getBugSummaryString($id) {
        $issueXml = $this->getIssue($id);

        $issue_summary = "Error: Summary not found in YouTrack for issue $id";

        try {
            $xml = new SimpleXMLElement($issueXml);
            foreach ($xml->field as $field) {
                switch ((string) $field['name']) {
                    case 'summary':
                        $issue_summary = (string) $field->value;
                        break;
                }
            }
        } catch (Exception $e) {
            tLog($e->getMessage(), 'WARNING');
        }
        return $issue_summary;
    }

    /**
     * Method overloaded from the bugtrackingInterface.
     * Checks a bug id for validity
     *
     * @return bool returns true if the bugid has the right format, false else
     **/
    function checkBugID($id) {
        $is_valid = true;
        if (trim($id) == "") {
            $is_valid = false;
        }
        if ($is_valid) {
            $forbidden_chars = '/[/]/';
            if (preg_match($forbidden_chars, $id)) {
                $is_valid = false;
            }
        }
        return $is_valid;
    }

    /**
     * Method overloaded from the bugtrackingInterface.
     * return the maximum length in chars of a bug id
     * @return int the maximum length of a bugID, let it be 32
     */
    function getBugIDMaxLength() {
        return 32;
    }

    /**
     * Method overloaded from the bugtrackingInterface.
     * default implementation for generating a link to the bugtracking page for viewing
     * the bug with the given id in a new page
     *
     * @param bug ID in YouTrack
     *
     * @return string returns a complete URL to view the bug (if found in db)
     **/
    function buildViewBugLink($bugID, $bWithSummary = false) {
        $link = "<a href='" . $this->buildViewBugURL($bugID) . "' target='_blank'>";
        $status = $this->getBugStatusString($bugID);

        if (!is_null($status)) {
            $link .= $status;
        }
        else
            $link .= $bugID;
        if ($bWithSummary) {
            $summary = $this->getBugSummaryString($bugID);

            if (!is_null($summary)) {
                $link .= " : " . $summary;
            }
        }

        $link .= "</a>";

        return $link;
    }

    /**
     * Method overloaded from the bugtrackingInterface.
     * checks if bug id is present on BTS
     * Function has to be overloaded on child classes
     *
     * @return bool
     **/
    function checkBugID_existence($id) {
        try {
            $responseXML = $this->isIssueExists($id);
            if ((stristr($responseXML, "<error>") == TRUE)) {
                return false;
            }
        }
        catch (Exception $e) {
            tLog($e->getMessage(), 'WARNING');
            return false;
        }
        return true;
    }
}

?>