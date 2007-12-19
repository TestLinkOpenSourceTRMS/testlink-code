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
 * @version $Revision: 1.2 $
 * @modified $Date: 2007/12/19 18:27:06 $
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
    var $dbHost = BUG_TRACK_DB_HOST;
    var $enterBugURL = BUG_TRACK_ENTER_BUG_HREF;
    
    var $dbConnection = null;
    var $bConnected = false;
    
    // Trac Variables
    var $xmlrpcClient = null;

    /**
     * Constructor of bugtrackingInterface
     * put special initialization in here
     * 
     * @version 1.0
     * @author Toshiyuki Kawanishi <tosikawa@users.sourceforge.jp>
     **/
    function tracInterface()
    {
        $this->xmlrpcClient = new IXR_Client($this->dbHost);
    }

    /**
     * this function establishes the database connection to the 
     * bugtracking system
     *
     * @return bool returns true if the db connection was established and the 
     * db could be selected, false else
     *
     * @version 1.0
     * @author Toshiyuki Kawanishi <tosikawa@users.sourceforge.jp>
     **/
    function connect()
    {
        if ($this->xmlrpcClient->query('system.getAPIVersion')) {
            $this->bConnected = true;
        }
        else {
            $this->bConnected = false;
        }
        return $this->bConnected;
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
        return $this->bConnected;
    }

    /**
     * this function closes the db connection (if any) 
     *
     * @version 1.0
     * @author Toshiyuki Kawanishi <tosikawa@users.sourceforge.jp>
     **/
    function disconnect()
    {
        $this->bConnected = false;
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
     * @version 1.0
     * @author Toshiyuki Kawanishi <tosikawa@users.sourceforge.jp>
     **/
    function buildViewBugURL($id)
    {
        $ticket_url = $this->enterBugURL . "/$id";
        return $ticket_url;
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
        if ($this->xmlrpcClient->query('ticket.get', $id)) {
            $xmlrpc_response = $this->xmlrpcClient->getResponse();
            $status_string = $xmlrpc_response[3]['status'];
        }
        else
        {
            $status_string = "Error: Ticket #$id is not registered in Trac.";
        }
        
        return $status_string;
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
        $summary_string = "Error: Ticket #$id is not registered in Trac.";
        
        if ($this->xmlrpcClient->query('ticket.get', $id)) {
            $xmlrpc_response = $this->xmlrpcClient->getResponse();
            $summary_string = $xmlrpc_response[3]['summary'];
        }
        
        return $summary_string;
    }
    
    /**
     * checks is bug id is present on BTS
     * 
     * @return bool 
     **/
    function checkBugID_existence($id)
    {
      $status_ok = 1;
      return $status_ok;
    }
}
?>
