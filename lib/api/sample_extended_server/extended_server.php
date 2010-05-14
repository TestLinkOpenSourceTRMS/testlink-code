<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * Filename $RCSfile: extended_server.php,v $
 *
 * Contribution - example of XMLRPC server extended using TestLink XMLRPC server
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2010/05/14 20:07:40 $ by $Author: franciscom $
 * @author rtessier
 * @package	TestlinkAPI
 */
require_once("lib/api/xmlrpc.class.php");

class SampleXMLRPCServer extends TestlinkXMLRPCServer {
    public function __construct() {
        openlog("testlink", LOG_ODELAY, LOG_LOCAL1);
        $callbacks = array('tl.getTestSuiteIDByName' => 'this:getTestSuiteIDByName',
                           'tl.uploadStats' => 'this:uploadStats'
);
        parent::__construct($callbacks);
    }

    private function  _getTestSuiteByName($args) {
        $status_ok=false;
        $testSuiteName = $args[self::$testSuiteNameParamName];

        $result = $this->tsuiteMgr->get_by_name($testSuiteName);

        $num = sizeof($result);
        if ($num == 0) {
            $msg = $msg_prefix . sprintf("Name %s does not belong to a test suite present on system!", $testSuiteName);
            $this->errors[] = new IXR_Error(8004, $msg);
        } else {
            // Check project id
            foreach ($result as $row) {
                $projectid = $this->tsuiteMgr->getTestProjectFromTestSuite($row['id']);
                if ($projectid == $args[self::$testProjectIDParamName]) {
                    $result[0] = $row;
                    $status_ok=true;
                    break;
                }
            }
            
            if (!$status_ok) {
            	$tprojectInfo=$this->tprojectMgr->get_by_id($args[self::$testProjectIDParamName]);
                $msg= sprintf(TESTSUITE_DONOTBELONGTO_TESTPROJECT_STR, $result[0]['id'],
                              $tprojectInfo['name'], $args[self::$testProjectIDParamName]);
                $this->errors[] = new IXR_Error(TESTSUITE_DONOTBELONGTO_TESTPROJECT,$msg_prefix . $msg);
            }
        }        
        
        return $status_ok ? $result : $this->errors;
    }

    public function getTestSuiteIDByName($args) {
        $msg_prefix="(" .__FUNCTION__. ") - ";
        $status_ok=true;
        $this->_setArgs($args);

        $checkFunctions = array('authenticate','checkTestSuiteName');
        $status_ok = $this->_runChecks($checkFunctions,$msg_prefix) && $this->userHasRight("mgt_view_tc");

        if ($status_ok) {
            $keys2check = array(self::$testSuiteNameParamName);

            foreach ($keys2check as $key) {
                if (!$this->_isParamPresent($key)) {
                    $this->errors[] = new IXR_Error(NO_TESTSUITENAME,
                                  $msg_prefix . NO_TESTSUITENAME_STR);
                    $status_ok=false;
                }
            }
        }

        return $status_ok ? $this->_getTestSuiteByName($this->args) : $this->errors;
    }

    public function uploadStats($args) {
        foreach ($args as $key => $value) {
            switch ($key) {
                case devKey:
                    break;
                default:
                    $stat_msg = sprintf("%s: %s = %s\n", 
                                        $_SERVER['REMOTE_ADDR'], $key, $value);
                    syslog(LOG_INFO, $stat_msg);
                    break;
            }
        }
    }
}

$XMLRPCServer = new SampleXMLRPCServer();

?>
