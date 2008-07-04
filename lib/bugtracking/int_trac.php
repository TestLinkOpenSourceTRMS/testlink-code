<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: int_trac.php,v $
 *
 * The interefaces of TestLink - Trac (BTS).
 * TestLink connect to Trac via XML-RPC.
 *
 * The XmlRpcPlugin plugin should be installed in your Trac.
 *
 * @link http://trac.edgewall.org/ "Trac Project"
 * @link http://trac-hacks.swapoff.org/wiki/XmlRpcPlugin/ "Trac XmlRpcPlugin"
 *
 *
 * @version $Revision: 1.4 $
 * @modified $Date: 2008/07/04 02:42:30 $
 *
 * @author Toshiyuki Kawanishi <tosikawa@users.sourceforge.jp>
 * @author Ichiro Okazaki
 *
 **/

/** Interface name */
define('BUG_INTERFACE_CLASSNAME', 'tracInterface');

// This class use XML-RPC.
require_once(TL_ABS_PATH . 'third_party/xml-rpc/class-IXR.php');

class tracInterface extends bugtrackingInterface
{
    var $m_dbHost = null;
    var $m_enterBugURL = null;
	var $m_showBugURL = null;
    
    var $m_dbConnection = null;
    var $m_bConnected = false;
    
    // Trac Variables
    var $m_xmlrpcClient = null;
    var $m_currentTestProjectName = null;

    /**
     * Constructor of bugtrackingInterface
     * put special initialization in here
     * 
     * @version 1.1
     * @author Toshiyuki Kawanishi <tosikawa@users.sourceforge.jp>
     **/
    function tracInterface()
    {
        // Do nothing at constructor
    }

    /**
     * this function establishes the database connection to the 
     * bugtracking system
     *
     * @return bool returns true if the db connection was established and the 
     * db could be selected, false else
     *
     * @version 1.1
     * @author Toshiyuki Kawanishi <tosikawa@users.sourceforge.jp>
     **/
    function connect()
    {
        $this->m_bConnected = true;      
        return $this->m_bConnected;
    }

    /**
     * this function simply returns the state of the db connection 
     *
     * @return bool returns true if the db connection is established, false else
     *
     * @version 1.0
     * @author Toshiyuki Kawanishi <tosikawa@users.sourceforge.jp>
     **/
    function isConnected()
    {
        return $this->m_bConnected;
    }

    /**
     * this function closes the db connection (if any) 
     *
     * @version 1.0
     * @author Toshiyuki Kawanishi <tosikawa@users.sourceforge.jp>
     **/
    function disconnect()
    {
        $this->m_bConnected = false;
    }

    /**
     * this to return the URL to the bugtracking page for viewing 
     * the bug with the given id.
     *
     * @param int id the bug id
     * 
     * @return string returns a complete URL to view the given bug, or false if the bug 
     *             wasnt found
     *
     * @version 1.1
     * @author Toshiyuki Kawanishi <tosikawa@users.sourceforge.jp>
     **/
    function buildViewBugURL($id)
    {
        $this->checkConnectionViaXmarpc();

        $ticketUrl = $this->m_showBugURL . "/$id";
        return $ticketUrl;
    }

    /**
     * overload this to return the status in a readable form for the bug with the given id
     * This function is not directly called by TestLink 
     *
     * @param int id the bug id
     * 
     * @return any returns the status (in a readable form) of the given bug, or false
     *             if the bug is not found
     *
     * @version 1.0
     * @author Toshiyuki Kawanishi <tosikawa@users.sourceforge.jp>
     **/
    function getBugStatusString($id)
    {
        if($this->checkConnectionViaXmarpc() == false) {
            return '';
        }

        if ($this->m_xmlrpcClient->query('ticket.get', $id)) {
            $xmlrpcResponse = $this->m_xmlrpcClient->getResponse();
            $statusString = $xmlrpcResponse[3]['status'];
        }
        else
        {
            $statusString = "Error: Ticket #$id is not registered in Trac.";
        }

        return $statusString;
    }

    /*
     * 
     * FUNCTIONS CALLED BY TestLink:
     * 
     */

    /**
     * default implementation for fetching the bug summary from the 
     * bugtracking system
     *
     * @param int id the bug id
     * 
     * @return string returns the bug summary (if bug is found), or false
     *
     * @version 1.0
     * @author Toshiyuki Kawanishi <tosikawa@users.sourceforge.jp>
     **/
    function getBugSummaryString($id)
    {
        if($this->checkConnectionViaXmarpc() == false) {
            return '';
        }

        $summary_string = "Error: Ticket #$id is not registered in Trac.";
        
        if ($this->m_xmlrpcClient->query('ticket.get', $id)) {
            $xmlrpc_response = $this->m_xmlrpcClient->getResponse();
            $summary_string = "$id. " . $xmlrpc_response[3]['summary'];
        }
        
        return $summary_string;
     }
    
	/**
	 * returns the URL which should be displayed for entering bugs 
	 * 
	 * @return string returns a complete URL 
	 *
	 * @version 1.0
	 *
     * @author Toshiyuki Kawanishi <tosikawa@users.sourceforge.jp>
	 **/
	function getEnterBugURL()
	{
		$this->checkConnectionViaXmarpc();

		return $this->m_enterBugURL;
	}

    /**
     * checks is bug id is present on BTS
     * 
     * @param int id the bug id
     * 
     * @return if the bug id exest it returns true; otherwise false
     *
     * @version 1.0
     * @author Toshiyuki Kawanishi <tosikawa@users.sourceforge.jp>
     **/
    function checkBugID_existence($id)
    {
        $this->checkConnectionViaXmarpc();
        $statusOk = $this->m_xmlrpcClient->query('ticket.get', $id);
        
        return $statusOk;
    }

    /**
     * Check the connection of XML-RPC each Test Project
     * 
     * @param int id the bug id
     * 
     * @return if the specified trac project exest it returns true; otherwise false
     *
     * @version 1.1
	 *
	 *     modified for http://www.testlink.org/mantis/view.php?id=1469
	 *
     * @author Toshiyuki Kawanishi <tosikawa@users.sourceforge.jp>
     **/
    function checkConnectionViaXmarpc()
    {
        global $g_interface_bugs_project_name_mapping;

        $tprojectName = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : 'xx';
 
        if ($this->m_currentTestProjectName != $tprojectName) {
            if(!isset($g_interface_bugs_project_name_mapping[$tprojectName])) {
                $this->m_bConnected = false;
                return false;
            }
            $this->m_currentTestProjectName = $tprojectName;
            $tracProjectName = $g_interface_bugs_project_name_mapping[$tprojectName];
            $this->m_dbHost = BUG_TRACK_DB_HOST . $tracProjectName;
            $this->m_xmlrpcClient = new IXR_Client($this->m_dbHost . '/xmlrpc');
            $this->m_enterBugURL = $this->m_dbHost . BUG_TRACK_ENTER_BUG_HREF;
			$this->m_showBugURL = $this->m_dbHost . BUG_TRACK_HREF;
        }

        return true;
    }
}
?>