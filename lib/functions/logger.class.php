<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Log Functions
 *
 * A great way to debug is through logging. It's even easier if you can leave
 * the log messages through your code and turn them on and off with a single command.
 * To facilitate this we will create a number of logging functions.
 *
 * @package TestLink
 * @author Andreas Morsing
 * @copyright 2005-2009, TestLink community 
 * @version CVS: $Id: logger.class.php,v 1.49 2010/08/23 21:43:24 franciscom Exp $
 * @link http://www.teamst.org
 * @since 1.8
 * 
 * @internal Revisions:
 * 
 *  20100823 - franciscom - BUGID 3656 - reopened crash when using table prefix
 *	20100808 - franciscom - BUGID 3656 - crash on some DBMS due to Transactions instead of transactions
 *	20091005 - amitkhullar - improved function getEventsFor() - BUG 2862
 *	20090603 - franciscom - adding table prefix management
 *	20080517 - franciscom - exclude mktime() logs 
 *	20080316 - franciscom - added getEnableLoggingStatus() methods
 *	                        refactored access to enable logging status info.
 *	                        refactored enable/disable logging
 *	                        Added code to configure individual loggers using new config $g_loggerCfg
 *	20080315 - franciscom - discovered bug on tlTransaction->writeToDB thanks to POSTGRES
 *	                        watchPHPErrors() - added new error to suppress
 *	20080216 - franciscom - limit length of entryPoint
 *
 **/
 
/**
 * @package TestLink
 */
class tlLogger extends tlObject
{
	/** 
	 * Log levels
	 * There are 5 logging levels available. Log messages will only be displayed
	 * if they are at a level less verbose than that currently set. So, we can turn
	 * on logging with the following command:
	 *
	 */
	const ERROR = 1;
	const WARNING = 2;
 	const INFO = 4;
	const DEBUG = 8;
	const AUDIT = 16;

	static $logLevels = null;
	static $revertedLogLevels = null;

    /** @var boolean to enable/disable loggin for all loggers */
	protected $doLogging = true;

 	// must be changed is db field len changes
 	const ENTRYPOINT_MAX_LEN = 45;

	//the one and only logger of TesTLink
	private static $s_instance;

	//all transactions, at the moment there is only one transaction supported,
	//could be extended if we need more
	protected $transactions = null;

	//the logger which are controlled
	protected $loggers = null;

	//log only event which pass the filter,
	/** @TODO SCHLUNDUS: should use $g_log_level */
	protected $logLevelFilter = null;

	protected $eventManager;
    
	public function __construct(&$db)
	{
		parent::__construct();

		$this->loggers['db'] = new tlDBLogger($db);
		$this->loggers['file'] = new tlFileLogger();

		$this->setLogLevelFilter(self::ERROR | self::WARNING | self::AUDIT);

		$this->eventManager = tlEventManager::create($db);
	}

	public function __destruct()
	{
		parent::__destruct();
	}
	public function getAuditEventsFor($objectIDs = null,$objectTypes = null,$activityCodes = null,
	                                  $limit = -1,$startTime = null,$endTime = null, $users = null)
	{
		return $this->eventManager->getEventsFor(tlLogger::AUDIT,$objectIDs,$objectTypes,$activityCodes,
		                                         $limit,$startTime,$endTime,$users);
	}
	public function getEventsFor($logLevels = null,$objectIDs = null,$objectTypes = null,
	                             $activityCodes = null,$limit = -1,$startTime = null,
	                             $endTime = null, $users = null)
	{
		return $this->eventManager->getEventsFor($logLevels,$objectIDs,$objectTypes,$activityCodes,
		                                          $limit,$startTime,$endTime,$users);
	}
	
	public function deleteEventsFor($logLevels = null,$startTime = null)
	{
		return $this->eventManager->deleteEventsFor($logLevels,$startTime);
	}
	
	/**
	 * set the log level filter, only events which matches the filter can pass
	 * can be combination of any of the tlLogger::LogLevels
	 */
	public function setLogLevelFilter($filter)
	{
		$this->logLevelFilter = $filter;
		foreach($this->loggers as $key => $loggerObj)
		{
			$this->loggers[$key]->setLogLevelFilter($filter);
		}
		return tl::OK;
	}

	/**
	 * disable logging
	 * 
	 * @param TBD $logger (optional) default null = all loggers
	 *            string representing a list of keys to access loggers map.
	 * 
	 */
	public function disableLogging($logger = null)
	{
	    if(is_null($logger))
	    {
	    	$this->doLogging = false;
		}
		else
		{
			$loggerSet = explode(",",$logger);
			foreach($loggerSet as $idx => $loggerKey)
			{
			   $this->loggers[$loggerKey]->disableLogging();
			}
		}
	}

	/**
	 * enable logging
	 * 
	 * @param TBD $logger (optional) default null = all loggers
	 *            string representing a list of keys to access loggers map.
	 * 
	 */
	public function enableLogging($logger = null)
	{
	    if(is_null($logger))
	    {
	 	    $this->doLogging = false;
		}
		else
		{
		    $loggerSet = explode(",",$logger);
		    foreach($loggerSet as $idx => $loggerKey)
		    {
		        $this->loggers[$loggerKey]->enableLogging();
		    }
		}
	}

	public function getEnableLoggingStatus($logger = null)
	{
		$status=is_null($logger) ? $this->doLogging : $this->loggers[$logger]->getEnableLoggingStatus();
		return $status;
		
		// if(is_null($logger))
		// 	return $this->doLogging;
		// else
		// 	return $this->loggers[$logger]->getEnableLoggingStatus();
	}

	/**
	 * returns the transaction with the specified name, null else
	 */
	public function getTransaction($name = "DEFAULT")
	{
		if (isset($this->transactions[$name]))
		{
			return $this->transactions[$name];
		}
		return null;
	}
	
	/**
	 * create the logger for TestLink
	 * @param resource &$db reference to database handler
	 */
	static public function create(&$db)
    {
        if (!isset(self::$s_instance))
		{
			//create the logging instance
			self::$logLevels = array (self::DEBUG => 'DEBUG', self::INFO => 'INFO',
							          self::WARNING => 'WARNING', self::ERROR => 'ERROR',
							          self::AUDIT => 'AUDIT');
			self::$revertedLogLevels = array_flip(self::$logLevels);
            $c = __CLASS__;
            self::$s_instance = new $c($db);
        }
        return self::$s_instance;
    }


	/**
	 * starts a transaction
	 * 
	 * @internal 
	 * rev: 20080216 - franciscom - entrypoint len limiting
	 */
	public function startTransaction($name = "DEFAULT",$entryPoint = null,$userID = null)
	{
		//if we have already a transaction with this name, return
		if (isset($transactions[$name]))
			return tl::ERROR;
		if (is_null($entryPoint))
			$entryPoint = $_SERVER['SCRIPT_NAME'];

		if(strlen($entryPoint) > self::ENTRYPOINT_MAX_LEN)
		{
			// Important information is at end of string
			$entryPoint = substr($entryPoint,-self::ENTRYPOINT_MAX_LEN);

			// After limiting we can get thinks like:
			//     l18/head_20080216/lib/project/projectEdit.php
			// in these cases is better (IMHO) write:
			//     /head_20080216/lib/project/projectEdit.php
			//
			// search first /
			$mypos = strpos($entryPoint,"/");
			if(($mypos !== FALSE) && $mypos)
				$entryPoint = substr($entryPoint,$mypos);
		}

		if (is_null($userID))
			$userID = isset($_SESSION['currentUser']) ? $_SESSION['currentUser']->dbID : 0;
		$sessionID = $userID ? session_id() : null;

		$t = new tlTransaction($this->db);
		$this->transactions[$name] = &$t;
		$t->initialize($this->loggers,$entryPoint,$name,$userID,$sessionID);

		return $this->transactions[$name];
	}

	/**
	 * ends a transaction
	 */
	public function endTransaction($name = "DEFAULT")
	{
		if (!isset($this->transactions[$name]))
		{
			return tl::ERROR;
		}
		
		$this->transactions[$name]->close();
		unset($this->transactions[$name]);
	}
}


/**
 * transaction class
 * @package 	TestLink
 * 
 */
class tlTransaction extends tlDBObject
{
	//the attached loggers
	protected $loggers = null;

	public $name = null;
	public $entryPoint = null;
	public $startTime = null;
	public $endTime = null;
	public $duration = null;
	public $userID = null;
	public $sessionID = null;

	protected $events = null;
   
	public function __construct(&$db)
	{
		parent::__construct($db);
	}

	public function initialize(&$logger,$entryPoint,$name,$userID,$sessionID)
	{
		$this->loggers = $logger;
		$this->name = $name;
		$this->entryPoint = $entryPoint;
		$this->startTime = time();
		$this->userID = $userID;
		$this->sessionID = $sessionID;
		$this->writeTransaction($this);
		tlTimingStart($name);
	}

	public function __destruct()
	{
		if (!is_null($this->name))
			$this->close();
		parent::__destruct();
	}

	public function _clean($options = self::TLOBJ_O_SEARCH_BY_ID)
	{
		$this->loggers = null;
		$this->name = null;
		$this->entryPoint = null;
		$this->startTime = null;
		$this->userID = null;
		$this->sessionID = null;
		if (!($options & self::TLOBJ_O_SEARCH_BY_ID))
			$this->dbID = null;
	}
	
	/*
		closes the transaction
	*/
	public function close()
	{
		$this->endTime = time();
		tlTimingStop($this->name);
		$this->duration = tlTimingCurrent($this->name);
		$result = $this->writeTransaction($this);
		$this->name = null;

		return $result;
	}

	//add an event to the transaction the last arguments are proposed for holding information about the objects
	public function add($logLevel,$description,$source = null,$activityCode = null,$objectID = null,$objectType = null)
	{
		$e = new tlEvent();
		$e->initialize($this->dbID,$this->userID,$this->sessionID,$logLevel,$description,
		               $source,$activityCode,$objectID,$objectType);
		$this->writeEvent($e);
		$this->events[] = $e;

		return tl::OK;
	}
	
	public function readFromDB(&$db,$options = self::TLOBJ_O_SEARCH_BY_ID)
	{
		$this->_clean($options);
		$query = " SELECT id,entry_point,start_time,end_time,user_id,session_id " .
		         " FROM {$this->tables['transactions']} ";
		$clauses = null;
		if ($options & self::TLOBJ_O_SEARCH_BY_ID)
			$clauses[] = "id = {$this->dbID}";
		if ($clauses)
			$query .= " WHERE " . implode(" AND ",$clauses);
		$info = $db->fetchFirstRow($query);
		if ($info)
		{
			$this->dbID = $info['id'];
			$this->entry_point = $info['entry_point'];
			$this->startTime = $info['start_time'];
			$this->endTime = $info['end_time'];
			$this->userID = $info['user_id'];
			$this->sessionID = $info['session_id'];
		}
		return $info ? tl::OK : tl::ERROR;
	}
	
	public function writeToDB(&$db)
	{
		if (!$this->dbID)
		{
			$entryPoint = $db->prepare_string($this->entryPoint);
			$startTime = $db->prepare_int(time());
			$endTime = $db->prepare_int(0);
			$userID = $db->prepare_int($this->userID);
			$sessionID = "NULL";
			if (!is_null($this->sessionID))
			{
				$sessionID = "'".$db->prepare_string($this->sessionID)."'";
            }
            
			$query = "INSERT INTO {$this->tables['transactions']} " .
			         "(entry_point,start_time,end_time,user_id,session_id) " .
			         "VALUES ('{$entryPoint}',{$startTime},{$endTime},{$userID},{$sessionID})";
			$result = $db->exec_query($query);
			if ($result)
			{
				$this->dbID = $db->insert_id($this->tables['transactions']);
			}	
		}
		else
		{
			$endTime = $db->prepare_int(time());
			$query = "UPDATE {$this->tables['transactions']} SET end_time = {$endTime} WHERE id = {$this->dbID}";
			$result = $db->exec_query($query);
		}
		return $result ? tl::OK : tl::ERROR;
	}
	
	public function deleteFromDB(&$db)
	{
		return self::handleNotImplementedMethod(__FUNCTION__);
	}

	
	protected function writeEvent(&$e)
	{
		foreach($this->loggers as $key => $loggerObj)
		{
			$this->loggers[$key]->writeEvent($e);
		}
		return tl::OK;
	}

	protected function writeTransaction(&$t)
	{
		foreach($this->loggers as $key => $loggerObj)
		{
			$this->loggers[$key]->writeTransaction($t);
		}
		return tl::OK;
	}

	static public function getByID(&$db,$id,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
	{
		return tlDBObject::createObjectFromDB($db,$id,__CLASS__,tlEvent::TLOBJ_O_SEARCH_BY_ID,$detailLevel);
	}
	
	static public function getByIDs(&$db,$ids,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
	{
		return self::handleNotImplementedMethod(__FUNCTION__);
	}
	
	static public function getAll(&$db,$whereClause = null,$column = null,$orderBy = null,
	                              $detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
	{
		return self::handleNotImplementedMethod(__FUNCTION__);
	}
}

/**
 * @package 	TestLink
 */
class tlEventManager extends tlObjectWithDB
{
	private static $s_instance;
	public function __construct(&$db)
	{
		parent::__construct($db);
	}
	
    public static function create(&$db)
    {
        if (!isset(self::$s_instance))
		{
            $c = __CLASS__;
            self::$s_instance = new $c($db);
        }

        return self::$s_instance;
    }

  /*
    function:

    args:

    returns:
    
    rev: 20080514 - franciscom - added empty() to avoid crash

  */
	public function getEventsFor($logLevels = null,$objectIDs = null,$objectTypes = null,
	                             $activityCodes = null,$limit = -1,$startTime = null,
	                             $endTime = null, $users = null)
	{
		$clauses = null;
        $usersFilter = null;
		if (!is_null($logLevels))
		{
			$logLevels = (array) $logLevels;
			$logLevels = implode(",",$logLevels);
			$clauses[] = "log_level IN ({$logLevels})";
		}
		if (!is_null($objectIDs) && !empty($objectIDs))
		{
			$objectIDs = (array) $objectIDs;
	    	$objectIDs = implode(",",$objectIDs);
	    	$clauses[] = "object_id IN ({$objectIDs})";
		}
		if (!is_null($objectTypes) && !empty($objectTypes) )
		{
			$objectTypes = (array) $objectTypes;
			$objectTypes = $this->db->prepare_string(implode("','",$objectTypes));
			$clauses[] = "object_type IN ('{$objectTypes}')";
		}
		if (!is_null($activityCodes))
		{
			$activityCodes = (array) $activityCodes;
			$activityCodes = "('".implode("','",$activityCodes)."')";
			$clauses[] = "activity IN {$activityCodes}";
		}
		
		if (!is_null($startTime))
		{
			$clauses[] = "fired_at >= {$startTime}";
        }

		if (!is_null($endTime))
		{
			$clauses[] = "fired_at <= {$endTime}";
		}
		
	    if (!is_null($users))
	    {
	    	// BUGID 3656
	        $usersFilter = " JOIN {$this->tables['transactions']}  T ON T.id = E.transaction_id AND T.user_id IN ({$users}) ";
	    }
		$query = "SELECT E.id FROM {$this->tables['events']} E {$usersFilter}";
	    if ($clauses)
	    {
	        $query .= " WHERE " . implode(" AND ",$clauses);
	    }

		$query .= " ORDER BY transaction_id DESC,fired_at DESC";
	
		return tlEvent::createObjectsFromDBbySQL($this->db,$query,'id',"tlEvent",true,
		                                         tlEvent::TLOBJ_O_GET_DETAIL_FULL,$limit);
	}
	
	function deleteEventsFor($logLevels = null,$startTime = null)
	{
		$clauses = null;
		if (!is_null($logLevels))
		{
			$logLevels = (array) $logLevels;
			$logLevels = implode(",",$logLevels);
			$clauses[] = "log_level IN ({$logLevels})";
		}
		if (!is_null($startTime))
		{
			$clauses[] = "fired_at < {$startTime}";
		}
			
		$query = "DELETE FROM {$this->tables['events']} ";
		if ($clauses)
		{
			$query .= " WHERE " . implode(" AND ",$clauses);
		}
		
		$this->db->exec_query($query);
		$query = "SELECT id FROM {$this->tables['transactions']} t " .
		         "WHERE (SELECT COUNT(0) FROM {$this->tables['events']} e WHERE e.transaction_id = t.id) = 0";
		$transIDs = $this->db->fetchColumnsIntoArray($query,"id");
		if ($transIDs)
		{
			$transIDs = implode(",",$transIDs);
			$query = "DELETE FROM {$this->tables['transactions']} WHERE id IN ({$transIDs})";
			$this->db->exec_query($query);
		}
	}
}


/**
 * the event class
 * @package 	TestLink
 */
class tlEvent extends tlDBObject
{
	public $logLevel = null;
	public $description = null;
	public $source = null;
	public $timestamp = null;
	public $userID = null;
	public $sessionID = null;
	public $transactionID = null;
	public $activityCode = null;
	public $objectID = null;
	public $objectType = null;

	public $transaction = null;

    //detail leveles  @TODO DOCUMENT DETAILS OF WHAT ?
	const TLOBJ_O_GET_DETAIL_TRANSACTION = 1;

	public function getLogLevel()
	{
		return tlLogger::$logLevels[$this->logLevel];
	}

	public function __construct($dbID = null)
	{
		parent::__construct($dbID);
	}    
	
	public function _clean($options = self::TLOBJ_O_SEARCH_BY_ID)
	{
		$this->logLevel = null;
		$this->description = null;
		$this->source = null;
		$this->timestamp = null;
		$this->userID = null;
		$this->sessionID = null;
		$this->source = null;
		$this->objectID = null;
		$this->objectType = null;
		$this->transaction = null;
		if (!($options & self::TLOBJ_O_SEARCH_BY_ID))
			$this->dbID = null;
	}

	public function initialize($transactionID,$userID,$sessionID,$logLevel,$description,
	                           $source = null,$activityCode = null,$objectID = null,$objectType = null)
	{
		$this->timestamp = time();

		$this->transactionID = $transactionID;
		$this->userID = $userID;
		$this->sessionID = $sessionID;
		$this->logLevel = $logLevel;
		$this->description = $description;
		$this->source = $source;
		$this->activityCode = $activityCode;
		$this->objectID = $objectID;
		$this->objectType = $objectType;
	}
	public function readFromDB(&$db,$options = self::TLOBJ_O_SEARCH_BY_ID)
	{
		$this->_clean($options);
		$query = " SELECT id,transaction_id,log_level,source,description,fired_at,object_id,object_type,activity " .
		         " FROM {$this->tables['events']} ";
		$clauses = null;
		if ($options & self::TLOBJ_O_SEARCH_BY_ID)
			$clauses[] = "id = {$this->dbID}";
		if ($clauses)
			$query .= " WHERE " . implode(" AND ",$clauses);
		$info = $db->fetchFirstRow($query);
		if ($info)
		{
			$this->dbID = $info['id'];
			$this->transactionID = $info['transaction_id'];
			$this->logLevel = $info['log_level'];
			$this->source = $info['source'];
			$this->description = $info['source'];
			if( ($tmp = tlMetaString::unserialize($info['description'])) )
			{
				$this->description = $tmp;
			}
			$this->timestamp = $info['fired_at'];
			$this->objectID = $info['object_id'];
			$this->objectType = $info['object_type'];
			$this->activityCode = $info['activity'];

			if ($this->transactionID && $options & self::TLOBJ_O_GET_DETAIL_TRANSACTION)
			{
				$this->transaction = tlTransaction::getByID($db,$this->transactionID,self::TLOBJ_O_GET_DETAIL_MINIMUM);
				if ($this->transaction)
				{
					$this->userID = $this->transaction->userID;
					$this->sessionID = $this->transaction->sessionID;
				}
			}
		}
		return $info ? tl::OK : tl::ERROR;
	}

	public function writeToDB(&$db)
	{
		if (!$this->dbID)
		{
			$logLevel = $db->prepare_int($this->logLevel);
			//this event logger supports tlMetaString and normal strings
			if (is_object($this->description))
				$description = $this->description->serialize();
			else
				$description = $this->description;

			$description = $db->prepare_string($description);
			$source = "NULL";
			if (!is_null($this->source))
				$source = "'".$db->prepare_string($this->source)."'";
			$objectType	= "NULL";
			if (!is_null($this->objectType))
				$objectType = "'".$db->prepare_string($this->objectType)."'";
			$activityCode = "NULL";
			if (!is_null($this->activityCode))
				$activityCode = "'".$db->prepare_string($this->activityCode)."'";
			$objectID = "NULL";
			if (!is_null($this->objectID))
				$objectID = $db->prepare_int($this->objectID);
			$firedAt = $db->prepare_int($this->timestamp);
			$transactionID = $db->prepare_int($this->transactionID);

			$query = "INSERT INTO {$this->tables['events']} (transaction_id,log_level,description,source," .
			         "fired_at,object_id,object_type,activity) " .
			         "VALUES ({$transactionID},{$logLevel},'{$description}',{$source}," .
			         "{$firedAt},{$objectID},{$objectType},{$activityCode})";

			$result = $db->exec_query($query);
			if ($result)
			{
				$this->dbID = $db->insert_id($this->tables['events']);
			}	
		}
	}

	public function deleteFromDB(&$db)
	{
		return self::handleNotImplementedMethod(__FUNCTION__);
	}

	static public function getByID(&$db,$id,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
	{
		return tlDBObject::createObjectFromDB($db,$id,__CLASS__,tlEvent::TLOBJ_O_SEARCH_BY_ID,$detailLevel);
	}

	static public function getByIDs(&$db,$ids,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
	{
		return self::handleNotImplementedMethod(__FUNCTION__);
	}

	static public function getAll(&$db,$whereClause = null,$column = null,$orderBy = null,
	                              $detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
	{
		return self::handleNotImplementedMethod(__FUNCTION__);
	}

}


/** 
 * class for logging events to datebase event tables
 * @package 	TestLink
 */
class tlDBLogger extends tlObjectWithDB
{
	protected $logLevelFilter = null;
	protected $pendingTransaction = null;
	protected $doLogging = true;

	public function __construct(&$db)
	{
		parent::__construct($db);
	}

	public function _clean()
	{
		$this->pendingTransaction = null;
	}

	public function disableLogging()
	{
			$this->doLogging = false;
	}

	public function enableLogging()
	{
		$this->doLogging = true;
	}

	public function getEnableLoggingStatus()
	{
		return $this->doLogging;
	}


	public function writeTransaction(&$t)
	{
	  if ($this->getEnableLoggingStatus() == false)
			return tl::OK;
		if (!$this->logLevelFilter)
			return tl::ERROR;
		if ($this->checkDBConnection() < tl::OK)
			return tl::ERROR;
		//if we get a closed transaction without a dbID then the transaction wasn't stored
		//into the db, so we can also ignore this write
		if ($t->endTime)
		{
			$this->pendingTransaction = null;
			if ($t->dbID)
			{
			  $this->disableLogging();
				$t->writeToDb($this->db);
			  $this->enableLogging();
			}
			return tl::OK;
		}
		else
		{
			//the db logger only writes transaction if they have at least one event which should be logged
			//so we store the transaction for later usage
			$this->pendingTransaction = $t;
		}
		return tl::OK;
	}

	public function writeEvent(&$e)
	{
		if (!$this->doLogging)
			return tl::OK;
		if (!($e->logLevel & $this->logLevelFilter))
			return tl::OK;
		if ($this->checkDBConnection() < tl::OK)
			return tl::ERROR;

    // to avoid log, writes related to log logic
		$this->disableLogging();

		//if we have a pending transaction so we could write it now
		if ($this->pendingTransaction)
		{
			$this->pendingTransaction->writeToDb($this->db);
			$e->transactionID = $this->pendingTransaction->dbID;
			$this->pendingTransaction = null;
		}
		$result = $e->writeToDb($this->db);
		$this->enableLogging();
		return $result;
	}

	public function setLogLevelFilter($filter)
	{
		//we should never log DEBUG to db
		$this->logLevelFilter = $filter & ~tlLogger::DEBUG;
	}

	public function checkDBConnection()
	{
		//check if the DB connection is still valid before writing log entries and try to reattach
		if (!$this->db)
		{
			global $db;
			if ($db)
				$this->db = &$db;
		}
		if (!$this->db || !$this->db->db->isConnected())
			return tl::ERROR;
		return tl::OK;
	}

}

/**
 * class for logging events to file
 * @package 	TestLink
 * @TODO watch the logfile size, display warning / shrink it,....
 */
class tlFileLogger extends tlObject
{
	static protected $eventFormatString = "\t[%timestamp][%errorlevel][%sessionid][%source]\n\t\t%description\n";
	static protected $openTransactionFormatString = "[%prefix][%transactionID][%name][%entryPoint][%startTime]\n";
	static protected $closedTransactionFormatString = "[%prefix][%transactionID][%name][%entryPoint][%startTime][%endTime][took %duration secs]\n";
	protected $logLevelFilter = null;

	protected $doLogging = true;


	public function __construct()
	{
		parent::__construct();

	}

	public function _clean()
	{

	}

	public function disableLogging()
	{
			$this->doLogging = false;
	}

	public function enableLogging()
	{
		$this->doLogging = true;
	}

	public function getEnableLoggingStatus()
	{
		return $this->doLogging;
	}



	//SCHLUNDUS: maybe i dont' write the transaction stuff to the file?
	public function writeTransaction(&$t)
	{
	  if ($this->getEnableLoggingStatus() == false)
			return tl::OK;

		if (!$this->logLevelFilter)
			return;

		//build the logfile entry
		$subjects = array("%prefix","%transactionID","%name","%entryPoint","%startTime","%endTime","%duration");
		$bFinished = $t->endTime ? 1 : 0;
		$formatString = $bFinished ? self::$closedTransactionFormatString : self::$openTransactionFormatString;
		$replacements = array($bFinished ? "<<" :">>",
							$t->getObjectID(),
							$t->name,
							$t->entryPoint,
							gmdate("y/M/j H:i:s",$t->startTime),
							$bFinished ? gmdate("y/M/j H:i:s",$t->endTime) : null,
							$t->duration,
						);
		$line = str_replace($subjects,$replacements,$formatString);
		return $this->writeEntry(self::getLogFileName(),$line);
	}
	public function writeEvent(&$e)
	{
		if (!($e->logLevel & $this->logLevelFilter))
			return;
		//this event logger supports tlMetaString and normal strings
		if (is_object($e->description))
			$description = $e->description->localize('en_GB');
		else
			$description = $e->description;

		//build the logfile entry
		$subjects = array("%timestamp","%errorlevel","%source","%description","%sessionid");
		$replacements = array(gmdate("y/M/j H:i:s",$e->timestamp),
								tlLogger::$logLevels[$e->logLevel],
								$e->source,$description,
								$e->sessionID ? $e->sessionID : "<nosession>");
		$line = str_replace($subjects,$replacements,self::$eventFormatString);

		$this->writeEntry(self::getLogFileName(),$line);
		//audits are also logged to a global audits logfile
		if ($e->logLevel == tlLogger::AUDIT)
			$this->writeEntry(self::getAuditLogFileName(),$line);
	}

	protected function writeEntry($fileName,$line)
	{
		$fd = fopen($fileName,'a+');
		if ($fd)
		{
			fputs($fd,$line);
	    	fclose($fd);
		}
	}

	public function setLogLevelFilter($filter)
	{
		$this->logLevelFilter = $filter;
	}


	/**
	 * the logfilename is dynamic and depends of the user and its session
	 *
	 * @return string returns the name of the logfile
	 **/
	static public function getLogFileName()
	{
		global $tlCfg;
		$uID = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;

		return $tlCfg->log_path . 'userlog' . $uID . ".log";
	}

	/**
	 * get the file which should be used audit logging
	 *
	 * @return string returns the name of the logfile
	 **/
	static public function getAuditLogFileName()
	{
		global $tlCfg;
		return $tlCfg->log_path . "audits.log";
	}

	/**
	 * You can empty the log at any time with:
	 *  resetLogFile
	 * @author Andreas Morsing - logfilenames are dynamic
	 */
	static public function resetLogFile()
	{
		@unlink($this->getLogFileName());
	}
}


/**
 * @TODO SCHLUNDUS: idea of a debug "to screen logger", to be defined,
 * @package 	TestLink
 */
class tlHTMLLogger
{

}


//create the global TestLink Logger, and open the initial default transaction
global $g_loggerCfg;
$g_tlLogger = tlLogger::create($db);
if( !is_null($g_loggerCfg) )
{
    foreach($g_loggerCfg as $loggerKey => $cfgValue)
    {
        $pfn=$cfgValue['enable'] ? 'enableLogging' : 'disableLogging';
        $g_tlLogger->$pfn($loggerKey);
    }
}

$g_tlLogger->startTransaction();

set_error_handler("watchPHPErrors");

/**
 * include php errors, warnings and notices to TestLink log
 * 
 * @internal 
 * rev: 20080504 - franciscom - added xmlrpc.inc,xmlrpcs.inc,xmlrpc_wrappers.inc in exclude set
 */
function watchPHPErrors($errno, $errstr, $errfile, $errline)
{
	$errors = array (
			E_USER_NOTICE => "E_USER_NOTICE",
			E_USER_WARNING => "E_USER_WARNING",
			E_USER_NOTICE => "E_USER_NOTICE",
			E_ERROR => "E_ERROR",
			E_WARNING => "E_WARNING",
			E_NOTICE => "E_NOTICE",
			E_STRICT => "E_STRICT"
		);

	if (isset($errors[$errno]))
	{
		// suppress some kind of errors
		// strftime(),strtotime(),date()
		if( ($errno == E_NOTICE && strpos($errstr,"unserialize()") !== false) ||
        	($errno == E_NOTICE && strpos($errstr,"ob_end_clean()") !== false) ||
		    ($errno == E_STRICT && strpos($errstr,"strftime()") !== false) ||
		    ($errno == E_STRICT && strpos($errstr,"mktime()") !== false) ||
		    ($errno == E_STRICT && strpos($errstr,"date()") !== false) ||
		    ($errno == E_STRICT && strpos($errstr,"strtotime()") !== false) ||
        	($errno == E_STRICT && strpos($errfile,"xmlrpc.inc") !== false) ||
        	($errno == E_STRICT && strpos($errfile,"xmlrpcs.inc") !== false) ||
        	($errno == E_STRICT && strpos($errfile,"xmlrpc_wrappers.inc") !== false) ||
		    (strpos($errfile,"Smarty_Compiler.class.php") !== false) ||
        	($errno == E_NOTICE && strpos($errfile,"Config_File.class.php") !== false)
		    )
		{
			return;
		}
		logWarningEvent($errors[$errno]."\n".$errstr." - in ".$errfile." - Line ".$errline,"PHP");
	}
}

/** 
 * we need a save way to shutdown the logger, or the current transaction will not be closed
 */
register_shutdown_function("shutdownLogger");
function shutdownLogger()
{
	global $g_tlLogger;
	if ($g_tlLogger)
		$g_tlLogger->endTransaction();
}
?>
