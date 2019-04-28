<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Log Functions
 *
 * A great way to debug is through logging. 
 * It's even easier if you can leave the log messages through your code and 
 * turn them on and off with a single command.
 *
 * IMPORTANTE DEVELOPMENT NOTICE:
 * logger Object is created when this file is included.
 * ($g_tlLogger = tlLogger::create($db);)
 *
 * @package     TestLink
 * @author      Andreas Morsing
 * @copyright   2005-2016, TestLink community 
 * @filesource  logger.class.php
 * @link        http://www.testlink.org
 * @since       1.8
 * 
 * @internal revisions
 * @since 1.9.15
 **/
 
/**
 * @package TestLink
 */
require_once('email_api.php');
class tlLogger extends tlObject
{

   // must be changed is db field len changes
   const ENTRYPOINT_MAX_LEN = 45;

  /** 
   * Log levels VALUES
   * Log messages will only be displayed if they level is present in 
   * config option array $tlCfg->loggerFilter.
   * Example:
   *       Configuring on your custom_config.inc.php
   *  
   *       $tlCfg->loggerFilter = array('DEBUG','AUDIT','WARNING','ERROR');
   *
   *       Will write to event viewer ALSO 'DEBUG' event
   *
   */
  const NONE = 0;
  const ERROR = 1;
  const WARNING = 2;
  const INFO = 4;
  const DEBUG = 8;
  const AUDIT = 16;
  const L18N = 32;
  

  /** 
   * @var array logLevels, key log level code, value log level string
     *
     */
  static $logLevels = null;

  /** 
   * @var array logLevelsStringCode, key log level string, value log level code  
   *
   */
  static $logLevelsStringCode = null;

  /** @var boolean to enable/disable loging for all loggers */
  protected $doLogging = true;

  // the one and only logger of TesTLink
  private static $s_instance;

  // all transactions, at the moment there is only one transaction supported,
  // could be extended if we need more
  protected $transactions = null;

  // the logger which are controlled
  protected $loggers = null;


  protected $eventManager;
  protected $loggerTypeClass = array('db' => null, 'file' => null, 'mail' => null);
  protected $loggerTypeDomain;
    
  public function __construct(&$db)
  {
    parent::__construct();
    
    $this->loggerTypeDomain = array_flip(array_keys($this->loggerTypeClass));
    foreach($this->loggerTypeClass as $id => $className)
    {
      $class2call = $className;
      if( is_null($className) )
      {
        $class2call = 'tl' . strtoupper($id) . 'Logger';
      } 
      $this->loggers[$id] = new $class2call($db);
    }
    
    // CRITICAL - this controls logLevel that is written to db.
    // IMHO using this config we will also change what is displayed in Event Viewer GUI
    $this->setLogLevelFilter(self::ERROR | self::WARNING | self::AUDIT | self::L18N);
    $this->loggers['mail']->setLogLevelFilter(self::ERROR | self::WARNING);
    
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
   * Set the log level filter, only events which matches the filter can pass.
   * $filter: Can be combination of any of the tlLogger::LogLevels
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
   * 
   * 
   */
  public function getLogLevelFilter($opt='raw')
  {
    $ret = array();
    if($opt == 'raw')
    {
      foreach($this->loggers as $type => $loggerObj)
      {
        $ret[$type] = $loggerObj->logLevelFilter;
      }
    }
    else
    {
      foreach($this->loggers as $type => $loggerObj)
      {
        $human = null;
        foreach(self::$logLevels as $code => $verbose)
        {
          if($loggerObj->logLevelFilter & $code)
          {
            $human[$code] = $verbose; 
          }    
        }
        if( !is_null($human) )
        {
          asort($human);
        }
        $ret[$type] = $human;
      }
    }     
    return $ret;
  }


  /**
   * @param verboseForLogger
   *        map with following keys: 'all' + $this->loggerTypeClass
   * 
   */
  public function setLogLevelFilterFromVerbose($verboseForLogger)
  {

    if( !is_null($verboseForLogger) )
    {
      $itemSet = (array)$verboseForLogger;
      foreach($itemSet as $loggerType => $dummy)
      {
        $filter = 0;
        foreach($dummy as $verboseLevel) 
        {
          if( isset(self::$logLevelsStringCode[$verboseLevel]) )
          {
            $filter = $filter | self::$logLevelsStringCode[$verboseLevel];
          }  
        }
        
        switch($loggerType)
        {
          case 'all':
            $this->setLogLevelFilter($filter);  
          break;
          
          default:
            if( isset($this->loggerTypeDomain[$loggerType]) )
            {
              $this->loggers[$loggerType]->setLogLevelFilter($filter);  
            }
          break;
        }      
      }
    }  
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

  /**
   *
   *
   */
  public function getEnableLoggingStatus($logger = null)
  {
    $status = is_null($logger) ? $this->doLogging : $this->loggers[$logger]->getEnableLoggingStatus();
    return $status;
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
      // create the logging instance
      self::$logLevels = array(self::DEBUG => 'DEBUG', self::INFO => 'INFO',
                               self::WARNING => 'WARNING', self::ERROR => 'ERROR',
                               self::AUDIT => 'AUDIT',  self::L18N => 'L18N');

      self::$logLevelsStringCode = array_flip(self::$logLevels);

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
    // if we have already a transaction with this name, return
    if (isset($transactions[$name]))
    {
      return tl::ERROR;
    }  
    
    if (is_null($entryPoint))
    {
      $entryPoint = $_SERVER['SCRIPT_NAME'];
    }

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
      {
        $entryPoint = substr($entryPoint,$mypos);
      }  
    }

    if(is_null($userID))
    {
      $userID = isset($_SESSION['currentUser']) ? intval($_SESSION['currentUser']->dbID) : 0;
    }
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

  /**
   *
   */
  function setDB(&$db)
  {
    $this->loggers['db']->setDB($db);
  }

}


/**
 * transaction class
 * @package   TestLink
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
    {  
      $this->close();
    }
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
    {
      $this->dbID = null;
    }  
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
	if( $source == 'GUI' && isset($_SESSION['testprojectID']) ){
		$source = $source . ' - ' . lang_get('TestProject') . ' ID : ' . $_SESSION['testprojectID'];
	}
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
    {
      $clauses[] = "id = " . intval($this->dbID);
    }
    
    if ($clauses)
    {
      $query .= " WHERE " . implode(" AND ",$clauses);
    }
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
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
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
            
      $query = "/* $debugMsg */ INSERT INTO {$this->tables['transactions']} " .
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
      $query = " /* $debugMsg */ " .
               " UPDATE {$this->tables['transactions']} SET end_time = {$endTime} " . 
               " WHERE id = " . intval($this->dbID);
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
 * @package   TestLink
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
      $usersFilter = " JOIN {$this->tables['transactions']}  T " .
                     " ON T.id = E.transaction_id AND T.user_id IN ({$users}) ";
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
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
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

   
    // TICKET 5464: DB Access error after deleting events from Event view (SQL server 2008)
    // Original implementation was done getting list of transactions without event,
    // and then creating an IN SQL CLAUSE.When
    // Unfortunately MSSQL (and may be other DBMS) has a limit in amount of elements present
    // on this kind of clause, and this causes an issue.
    // To be fair it would be very difficult to catch this error while testing (at least IMHO).
    //
    // While testing with MySQL another issue was found.
    // MySQL does not allow the table you're deleting from be used in a subquery for the condition.
    // 
    // Solution was found on:
    // http://stackoverflow.com/questions/4471277/mysql-delete-from-with-subquery-as-condition
    //
    // $subsql = " SELECT id FROM ( SELECT id FROM {$this->tables['transactions']} t " .
    //          " WHERE (SELECT COUNT(0) FROM {$this->tables['events']} e WHERE e.transaction_id = t.id) = 0) XX";
    // $query = " DELETE FROM {$this->tables['transactions']} WHERE id IN ( {$subsql} )";
    //
    
    // 20160320 - it's not clear why sometimes databaseType property does not exist
    //            this is a quick & dirty fix.
    if( property_exists($this->db,'databaseType') && 
        !is_null($this->db->databaseType) )
    {
      $alias4del = '';
      switch($this->db->databaseType)
      {
        case 'postgres7':
        case 'postgres8':
          $alias4del = '';
        break;

        case 'mysql':
        case 'mysqli':
        default:
          $alias4del = 'T';
        break;
      }

      // 201501114 - help by TurboP
      $query = "/* $debugMsg */ " . 
               " DELETE $alias4del FROM {$this->tables['transactions']} $alias4del " .
               " WHERE NOT EXISTS " .
               " (SELECT EV.id FROM {$this->tables['events']} EV " .
               "  WHERE EV.transaction_id = {$alias4del}.id) ";
      $this->db->exec_query($query);
    }  
  }
}


/**
 * the event class
 * @package   TestLink
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

  //detail levels  @TODO DOCUMENT DETAILS OF WHAT ?
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
    {
      $this->dbID = null;
    }  
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
    {
      $clauses[] = "id = {$this->dbID}";
    }
    
    if ($clauses)
    {
      $query .= " WHERE " . implode(" AND ",$clauses);
    }
    
    $info = $db->fetchFirstRow($query);
    if ($info)
    {
      $this->dbID = $info['id'];
      $this->transactionID = $info['transaction_id'];
      $this->logLevel = $info['log_level'];
      $this->source = $info['source'];
      $this->description = $info['source'];
      $this->timestamp = $info['fired_at'];
      $this->objectID = $info['object_id'];
      $this->objectType = $info['object_type'];
      $this->activityCode = $info['activity'];
      if( ($tmp = tlMetaString::unserialize($info['description'])) )
      {
        $this->description = $tmp;
      }

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
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    if (!$this->dbID)
    {
      $logLevel = $db->prepare_int($this->logLevel);
      $firedAt = $db->prepare_int($this->timestamp);
      $transactionID = $db->prepare_int($this->transactionID);
      
      // this event logger supports tlMetaString and normal strings
      $dummy = is_object($this->description) ? $this->description->serialize() : $this->description;
      $description = $db->prepare_string($dummy);

      $local = new stdClass();
      $local->objectID = !is_null($this->objectID) ? $db->prepare_int($this->objectID) : 0;

      $str2loop = array('source','objectType','activityCode');
      foreach($str2loop as $tg)
      {
        $local->$tg = !is_null($this->$tg) ? ("'" . $db->prepare_string($this->$tg) . "'" ) : 'NULL';
      }


      $query = "/* $debugMsg */ " .
               "INSERT INTO {$this->tables['events']} (transaction_id,log_level,description,source," .
               "fired_at,object_id,object_type,activity) " .
               "VALUES ({$transactionID},{$logLevel},'{$description}',{$local->source}," .
               "{$firedAt},{$local->objectID},{$local->objectType},{$local->activityCode})";

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
 * @package   TestLink
 */
class tlDBLogger extends tlObjectWithDB
{
  var $logLevelFilter = null;
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
    {
      return tl::OK;
    }
      
    if (!$this->logLevelFilter)
    {
      return tl::ERROR;
    }
    
    if ($this->checkDBConnection() < tl::OK)
    {
      return tl::ERROR;
    }
    
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
    {
      return tl::OK;
    }
    
    if (!($e->logLevel & $this->logLevelFilter))
    {
      return tl::OK;
    }
    
    if ($this->checkDBConnection() < tl::OK)
    {
      return tl::ERROR;
    }
    
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
    // we should never log DEBUG to db ?
    // $this->logLevelFilter = $filter & ~tlLogger::DEBUG;
    $this->logLevelFilter = $filter;
  }

  public function checkDBConnection()
  {
    // check if the DB connection is still valid before 
    // writing log entries and try to reattach
    if (!$this->db)
    {
      global $db;
      if ($db)
      {
        $this->db = &$db;
      }  
        
    }
    if (!$this->db || !$this->db->db->isConnected())
    {
      return tl::ERROR;
    }  
      
    return tl::OK;
  }

}

/**
 * class for logging events to file
 * @package   TestLink
 * @TODO watch the logfile size, display warning / shrink it,....
 */
class tlFileLogger extends tlObject
{
  static protected $eventFormatString = "\t[%timestamp][%errorlevel][%sessionid][%source]\n\t\t%description\n";

  static protected $openTransactionFormatString = "[%prefix][%transactionID][%name][%entryPoint][%startTime]\n";

  static protected $closedTransactionFormatString = "[%prefix][%transactionID][%name][%entryPoint][%startTime][%endTime][took %duration secs]\n";

  static $gmdateMask = "y/M/j H:i:s";

  var $logLevelFilter = null;

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


  public function writeTransaction(&$t)
  {
    if ($this->getEnableLoggingStatus() == false)
    {
      return tl::OK;
    }  
    
    if (!$this->logLevelFilter)
    {
      return;
    }

    //build the logfile entry
    $subjects = array("%prefix","%transactionID","%name","%entryPoint","%startTime","%endTime","%duration");

    $bFinished = $t->endTime ? 1 : 0;
    $formatString = $bFinished ? self::$closedTransactionFormatString : 
                    self::$openTransactionFormatString;
    $replacements = array($bFinished ? "<<" :">>", 
                          $t->getObjectID(), $t->name, $t->entryPoint,
                          gmdate(self::$gmdateMask,$t->startTime),
                          $bFinished ? gmdate(self::$gmdateMask,$t->endTime) : null,
                          $t->duration,);
    $line = str_replace($subjects,$replacements,$formatString);
    return $this->writeEntry(self::getLogFileName(),$line);
  }

  /**
   *
   */ 
  public function writeEvent(&$e)
  {
    if (!($e->logLevel & $this->logLevelFilter))
    {
      return;
    }
    
    // this event logger supports tlMetaString and normal strings
    if (is_object($e->description))
    {
      $description = $e->description->localize('en_GB');
    }
    else
    {
      $description = $e->description;
    }
    
    // build the logfile entry
    $subjects = array("%timestamp","%errorlevel","%source","%description","%sessionid");
    $replacements = array(gmdate(self::$gmdateMask,$e->timestamp),
                          tlLogger::$logLevels[$e->logLevel],
                          $e->source,$description,
                          $e->sessionID ? $e->sessionID : "<nosession>");
    $line = str_replace($subjects,$replacements,self::$eventFormatString);

    $this->writeEntry(self::getLogFileName(),$line);

    // audits are also logged to a global audits logfile
    if ($e->logLevel == tlLogger::AUDIT)
    {
      $this->writeEntry(self::getAuditLogFileName(),$line);
    }  
  }

  protected function writeEntry($fileName,$line)
  {
    // 20120817 - franciscom
    // need to silence this because during installation we can be in a situation
    // where we are not able to write the file, due to security changes we have done
    // @see http://mantis.testlink.org/view.php?id=5147
    // @see http://mantis.testlink.org/view.php?id=5148
    // @see http://mantis.testlink.org/view.php?id=4977
    // @see http://mantis.testlink.org/view.php?id=4906
    @$fd = fopen($fileName,'a+');
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
 * @package   TestLink
 */
class tlHTMLLogger
{

}

/** 
 * class for logging events to email
 * @package   TestLink
 */
class tlMailLogger extends tlObjectWithDB
{

  var $logLevelFilter = null;
  
  static protected $eventFormatString = "\t[%timestamp][%errorlevel][%sessionid][%source]\n\t\t%description\n";

  protected $doLogging = true;
  
  private $sendto_email;
  private $from_email;
  private $return_path_email;
  private $configIsOK;
  
  
  public function __construct(&$db)
  {
    parent::__construct($db);
    $this->sendto_email = config_get('tl_admin_email');
    $this->from_email = config_get('from_email');
    $this->return_path_email = config_get('return_path_email');
  
    // now we need to check if we have all needed configuration
    $key2check = array('sendto_email','from_email','return_path_email');
    $regex2match = config_get('validation_cfg')->user_email_valid_regex_php;
    $this->configIsOK = true;
    foreach($key2check as $emailKey)
    {
      $matches = array();
      $this->$emailKey = trim($this->$emailKey);
      if (is_blank($this->$emailKey) || !preg_match($regex2match,$this->$emailKey,$matches))
      {
        $this->configIsOK = false;
        break;
      }  
    }
  
  }
    
  public function getMailCfg()
  {
    $key2ret = array('sendto_email','from_email','return_path_email');
    $cfg = array();
    foreach($key2ret as $key)
    {
      $cfg[$key] = $this->$key;
    }
    return $cfg;
  }

  /**
   *
   */
  public function writeEvent(&$event)
  {
    if (!$this->doLogging)
    {
      return tl::OK;
    }
    
    if (!($event->logLevel & $this->logLevelFilter))
    {
      return tl::OK;
    }
    
    if (!$this->configIsOK)
    {
      return tl::ERROR;
    }

    // this event logger supports tlMetaString and normal strings
    if (is_object($event->description))
    {
      $description = $event->description->localize('en_GB');
    }
    else
    {
      $description = $event->description;
    }


      // to avoid log writes related to log logic
    $this->disableLogging();

    // build the logfile entry
    $subjects = array("%timestamp","%errorlevel","%source","%description","%sessionid");
    
    $verboseTimeStamp = gmdate(self::$gmdateMask,$event->timestamp);
    $replacements = array($verboseTimeStamp,
                          tlLogger::$logLevels[$event->logLevel],
                          $event->source,$description,
                          $event->sessionID ? $event->sessionID : "<nosession>");
    $email_body = str_replace($subjects,$replacements,self::$eventFormatString);

    try
    {
      $mail_subject = $verboseTimeStamp . lang_get('mail_logger_email_subject');
      $mail_subject .= isset($_SESSION['basehref']) ?  $_SESSION['basehref'] : config_get('instance_name');
      email_send($this->from_email, $this->sendto_email, $mail_subject, $email_body);
    }
    catch (Exception $exceptionObj)
    {
      // do nothing
      return tl::KO;
    }


    $this->enableLogging();
    return tl::OK;

  }


  public function writeTransaction(&$t)
  {
    return tl::OK;
  }
  
  public function setLogLevelFilter($filter)
  {
    $this->logLevelFilter = $filter;
  }

  public function enableLogging()
  {
    $this->doLogging = true;
  }

  public function disableLogging()
  {
    $this->doLogging = false;
  }
  
}



/**
 * include php errors, warnings and notices to TestLink log
 * 
 * @internal 
 *
 * Important Notice:
 * when using Smarty3 on demo.testlink.org, this kind of error started to appear
 *
 * Warning: filemtime(): stat failed for /path/to/smarty/cache/3ab50a623e65185c49bf17c63c90cc56070ea85c.one.tpl.php 
 * in /path/to/smarty/libs/sysplugins/smarty_resource.php
 * 
 * According to Smarty documentation: 
 * This means that your application registered a custom error hander (using set_error_handler()) 
 * which is not respecting the given $errno as it should. 
 * If, for whatever reason, this is the desired behaviour of your custom error handler, please call muteExpectedErrors() 
 * after you've registered your custom error handler. 
 *
 * @20130815 my choice is: (strpos($errfile,"Warning: filemtime()") !== false)
 */
function watchPHPErrors($errno, $errstr, $errfile, $errline)
{
  $errors = array(E_USER_NOTICE => "E_USER_NOTICE",E_USER_WARNING => "E_USER_WARNING",
                  E_USER_NOTICE => "E_USER_NOTICE",E_ERROR => "E_ERROR",
                  E_WARNING => "E_WARNING",E_NOTICE => "E_NOTICE",E_STRICT => "E_STRICT");

  /*
   1 E_ERROR, 2 E_WARNING, 4 E_PARSE, 8 E_NOTICE, 16  E_CORE_ERROR, 
   32 E_CORE_WARNING, 64 E_COMPILE_ERROR, 128 E_COMPILE_WARNING,
   256 E_USER_ERROR, 512 E_USER_WARNING, 1024 E_USER_NOTICE, 6143 E_ALL
   2048 E_STRICT, 4096 E_RECOVERABLE_ERROR
  */ 

  $el = error_reporting();
  $doIt = (($el & $errno) > 0);
  if ($doIt && isset($errors[$errno]) )
  {
    // suppress some kind of errors
    // strftime(),strtotime(),date()
    // work in block just to make copy and paste easier
    // Block 1 - errstr
    // Block 2 - errfile
    // 
    if( ($errno == E_NOTICE && strpos($errstr,"unserialize()") !== false) ||
        ($errno == E_NOTICE && strpos($errstr,"ob_end_clean()") !== false) ||
        ($errno == E_STRICT && strpos($errstr,"strftime()") !== false) ||
        ($errno == E_STRICT && strpos($errstr,"mktime()") !== false) ||
        ($errno == E_STRICT && strpos($errstr,"date()") !== false) ||
        ($errno == E_STRICT && strpos($errstr,"strtotime()") !== false) ||
        ($errno == E_WARNING && strpos($errstr,"filemtime") !== false) ||
        ($errno == E_STRICT && strpos($errfile,"xmlrpc.inc") !== false) ||
        ($errno == E_STRICT && strpos($errfile,"xmlrpcs.inc") !== false) ||
        ($errno == E_STRICT && strpos($errfile,"xmlrpc_wrappers.inc") !== false) ||
        ($errno == E_NOTICE && strpos($errfile,"Config_File.class.php") !== false) ||
        ($errno == E_WARNING && strpos($errfile,"smarty_internal_write_file.php") !== false) ||
        (strpos($errfile,"Smarty_Compiler.class.php") !== false)
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
  {
    $g_tlLogger->endTransaction();
  }
}


// --------------------------------------------------------------------------------------
// EXECUTED ON INCLUDE
// create the global TestLink Logger, and open the initial default transaction
global $g_loggerCfg;
$g_tlLogger = tlLogger::create($db);
if( !is_null($g_loggerCfg) )
{
  foreach($g_loggerCfg as $loggerKey => $cfgValue)
  {
    $pfn = $cfgValue['enable'] ? 'enableLogging' : 'disableLogging';
    $g_tlLogger->$pfn($loggerKey);
  }
}

if( !is_null(config_get('loggerFilter')) )
{
  $g_tlLogger->setLogLevelFilterFromVerbose(config_get('loggerFilter'));
}

$g_tlLogger->startTransaction();
set_error_handler("watchPHPErrors");