<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: logger.class.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2008/01/15 21:28:08 $
 *
 * @author Martin Havlat
 *
 * Log Functions
 *
 * A great way to debug is through logging. It's even easier if you can leave 
 * the log messages through your code and turn them on and off with a single command. 
 * To facilitate this we will create a number of logging functions.
 *
 * @author Andreas Morsing: added new loglevel for inlining the log messages 
**/
require_once("object.class.php");

class tlLogger extends tlObject
{
	//loglevels
	/* There are 5 logging levels available. Log messages will only be displayed 
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
		
	//the one and only logger of TesTLink	
	private static $s_instance;
	//all transactions, at the moment there is only one transaction supported, 
	//could be extended if we need more
	protected $transactions = null;
	protected $loggers = null;
	//log only event which pass the filter, 
	//SCHLUNDUS: should use $g_log_level
	protected $logLevelFilter = null;
	
	public function __construct(&$db)
	{
		parent::__construct();
		
		$this->loggers[] = new tlDBLogger(&$db);
		
		$fileName = $this->getLogFileName();
		$auditFileName = $this->getAuditLogFileName();
		$this->loggers[] = new tlFileLogger($fileName,$auditFileName);
		
		$this->setLogLevel(self::ERROR | self::WARNING | self::AUDIT);
	}
	public function __destruct()
	{
		foreach($this->transactions as $name => $t)
		{
			$this->endTransaction($name);
		}
		$this->transactions = null;
		parent::__destruct();
	}
	public function setLogLevel($level)
	{
		$this->logLevelFilter = $level;
		for($i = 0;$i < sizeof($this->loggers);$i++)
		{
			$this->loggers[$i]->setLogLevel($level);
		}
	}
	public function getTransaction($name = "DEFAULT")
	{
		if (isset($this->transactions[$name]))
			return $this->transactions[$name];
		return null;
	}
	
    public static function create(&$db) 
    {
        if (!isset(self::$s_instance))
		{
			self::$logLevels = array (self::DEBUG => "DEBUG",
							 self::INFO => "INFO",
							 self::WARNING => "WARNING",
							 self::ERROR => "ERROR",
							self::AUDIT => "AUDIT",
							);
			self::$revertedLogLevels = array_flip(self::$logLevels);
            $c = __CLASS__;
            self::$s_instance = new $c($db);
        }

        return self::$s_instance;
    }
	
	public function startTransaction($name = "DEFAULT",$entryPoint = null,$userID = null)
	{
		if (isset($transactions[$name]))
			return tl::ERROR;
		if (is_null($entryPoint))
			$entryPoint = $_SERVER['SCRIPT_NAME'];
		if (is_null($userID))
			$userID = isset($_SESSION['currentUser']) ? $_SESSION['currentUser']->dbID : 0;
		$sessionID = null;
		if ($userID)	
			$sessionID = session_id();
		$this->transactions[$name] = new tlTransaction($this->loggers,$entryPoint,$name,$userID,$sessionID);
		return $this->transactions[$name];
	}
	
	public function endTransaction($name = "DEFAULT")
	{
		if (!isset($this->transactions[$name]))
			return tl::ERROR;
		$this->transactions[$name]->close();
		unset($this->transactions[$name]);
	}
	
	/**
	 * the logfilename is dynamic and depends of the user and its session
	 *
	 * @return string returns the name of the logfile
	 **/
	static public function getLogFileName()
	{
		global $g_log_path;
		$uID = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
			
		return $g_log_path . DIRECTORY_SEPARATOR . 'userlog' . $uID . ".log";
	}
	static public function getAuditLogFileName()
	{
		global $g_log_path;
		
		return $g_log_path . DIRECTORY_SEPARATOR . "audits.log";
	}
	/*
	* You can empty the log at any time with:
	*  resetLogFile
	* @author Andreas Morsing - logfilenames are dynamic
	*/
	static public function resetLogFile() 
	{
		@unlink($this->getLogFileName());
	}
	//todo: watch the logfile size, display warning / shrink it,....
}
//the transaction class
class tlTransaction extends tlObject
{
	protected $loggers = null;
	public $name = null;
	public $entryPoint = null;
	public $startDate = null;
	public $endDate = null;
	protected $userID = null;
	protected $sessionID = null;
	protected $events = null;
	public $duration = null;
	
	public function __construct(&$logger,$entryPoint,$name,$userID,$sessionID)
	{
		parent::__construct();
		$this->loggers = $logger;
		$this->name = $name;
		$this->entryPoint = $entryPoint;
		$this->startDate = gmmktime();
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
	
	public function close()
	{
		$this->endDate = gmmktime();
		tlTimingStop($this->name);
		$this->duration = tlTimingCurrent($this->name);
		$this->writeTransaction($this);
		$this->name = null;
	}

	//add an event to the transaction the last arguments are proposed for holding information about the objects 
	//SCHLUNDUS: toDO
	public function add($logLevel,$description,$source = null,$activityCode = null,$objectID = null,$objectType = null)
	{
		//if the event has no source defined, we use the entrypoint of the transaction
		if (is_null($source))
			$source = str_replace(TL_BASE_HREF,'',$this->entryPoint);
			
		$e = new tlEvent($this->userID,$this->sessionID,$logLevel,$description,$source,$activityCode = null,$objectID = null,$objectType = null);
		$this->writeEvent($e);
		$this->events[] = $e;
			
		return tl::OK;
	}
	
	protected function writeEvent(&$e)
	{
		for($i = 0;$i < sizeof($this->loggers);$i++)
		{
			$this->loggers[$i]->writeEvent($e);
		}
		return tl::OK;
	}
	
	protected function writeTransaction(&$t)
	{
		for($i = 0;$i < sizeof($this->loggers);$i++)
		{
			$this->loggers[$i]->writeTransaction($t);
		}
		return tl::OK;
	}
}
//the event class
class tlEvent extends tlObject
{
	public $logLevel = null;
	public $description = null;
	public $source = null;
	public $timestamp = null;
	public $userID = null;
	public $sessionID = null;
	
	public function __construct($userID,$sessionID,$logLevel,$description,$source = null,$activityCode = null,$objectID = null,$objectType = null)
	{
		parent::__construct();
		$this->timestamp = gmmktime();
		$this->userID = $userID;
		$this->sessionID = $sessionID;
		$this->logLevel = $logLevel;
		$this->description = $description;
		$this->source = $source;
		$this->activityCode = $activityCode;
		$this->objectID = $objectID;
		$this->objectType = $objectType;
	}
}

//class for logging events to datebase event tables 
//SCHLUNDUS: toDO
class tlDBLogger extends tlObjectWithDB
{
	public function __construct(&$db)
	{
		parent::__construct($db);
	}
	
	public function writeTransaction(&$t)
	{
	
	}
	public function writeEvent(&$e)
	{
	}
	public function setLogLevel($level)
	{
	}
}
//class for logging events to file
class tlFileLogger extends tlObject
{
	static protected $eventFormatString = "\t[%timestamp][%errorlevel][%sessionid][%source]\n\t\t%description\n";
	static protected $openTransactionFormatString = "[%prefix][%transactionID][%name][%entryPoint][%startDate]\n";
	static protected $closedTransactionFormatString = "[%prefix][%transactionID][%name][%entryPoint][%startDate][%endDate][took %duration secs]\n";
	protected $logLevelFilter = null;
	
		public function __construct()
	{
		parent::__construct();
	}
	//SCHLUNDUS: maybe i dont' write the transaction stuff to the file?
	public function writeTransaction(&$t)
	{
		if (!$this->logLevelFilter)
			return;
		//build the logfile entry	
		$subjects = array("%prefix","%transactionID","%name","%entryPoint","%startDate","%endDate","%duration");
		$bFinished = $t->endDate ? 1 : 0; 
		$formatString = $bFinished ? self::$closedTransactionFormatString : self::$openTransactionFormatString;
		$replacements = array($bFinished ? "<<" :">>",
							$t->getObjectID(),
							$t->name,
							$t->entryPoint,
							gmdate("y/M/j H:i:s",$t->startDate),
							$bFinished ? gmdate("y/M/j H:i:s",$t->endDate) : null,
							$t->duration,
						);
		$line = str_replace($subjects,$replacements,$formatString);
		return $this->writeEntry(tlLogger::getLogFileName(),$line);
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
		
		$this->writeEntry(tlLogger::getLogFileName(),$line);
		//audits are also logged to a global audits logfile
		if ($e->logLevel == tlLogger::AUDIT)
			$this->writeEntry(tlLogger::getAuditLogFileName(),$line);
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
	
	public function setLogLevel($level)
	{
		$this->logLevelFilter = $level;
	}
}

//SCHLUNDUS: idea of a debug "to screen logger", to be defined,
class tlHTMLLogger
{

}
//create the global TestLink Logger, and open the initial default transaction
$g_tlLogger = tlLogger::create($db);
$g_tlLogger->startTransaction();
?>