<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @package 	TestLink
 * @copyright 	2007-2009, TestLink community 
 * @version    	CVS: $Id: pagestatistics.class.php,v 1.3 2009/06/16 10:49:04 havlat Exp $
 * @link 		http://www.teamst.org/index.php
 * @since 		1.9 - Jun, 2009
 *
 * @internal Revisions:
 * 	None
 **/


/**
 * Class which handles the "performance" footer on the end of each page, can also be used
 * to collect some performance related things
 * 
 * @package TestLink
 * @author 	Andreas Morsing
 * @since 	1.9 - Jun, 2009
 */
class tlPageStatistics extends tlObjectWithDB
{
	/**
	 * @var array array of tlPerformanceCounters
	 */
	protected $performanceCounters;
	
	/**
	 * Class constructor
	 * 
	 * @param resource &$db reference to resource of the database connection
	 */
	function __construct(&$db)
	{
		parent::__construct($db);
		$this->initialize();		
	}
	
	/**
	 * initializes the page statistics, by starting the "OVERALL"  Counter
	 */
	protected function initialize()
	{
		$this->startPerformanceCounter("OVERALL",tlPerformanceCounter::TYPE_ALL);
	}
	
	/**
	 * starts a new performance counter with the given title and type
	 * 
	 * @param string $title the title of the performance counter
	 * @param integer $type the type of the performance Counter, any combination of 
	 * 				tlPerformanceCounter::TYPE_ Flags
	 */
	public function startPerformanceCounter($title,$type)
	{
		$this->performanceCounters[$title] = new tlPerformanceCounter($this->db,$type);
	}
	
	/** 
	 * Class destructor, echoes the contents of the counter 
	 */
	public function __destruct()
	{
		echo (string) $this;
	}
	
	/** 
	 * Magic function called by php whenever a tlPageStatistics should be used as string
	 * 
	 * @return string returns a string representation of the counter
	 */
	public function __toString()
	{
		$output = "<div style=\"border:1px solid black;color:red;font-weight:bold\">";
		$output .= "Performance counters: \n<br/>";	
		foreach($this->performanceCounters as $title => $counter)
		{
			$output .= "{$title}\n<br/>";
			$output .= "&nbsp;&nbsp;&nbsp;&nbsp;{$counter}";
		}
		$output .= "</div>";
		
		return $output;
	
	}
}


/** 
 * @package TestLink
 * @author Andreas Morsing
 * @since 1.9 - Jun, 2009
 */ 
class tlPerformanceCounter extends tlObjectWithDB
{
	const TYPE_MEMORY = 1;
	const TYPE_TIME = 2;
	const TYPE_SQL = 4;
	const TYPE_ALL = 0xFFFF;	
	
	private $counterType = self::TYPE_ALL;
	private $memoryPeak = 0;
	private $memoryStart = 0;
	private $memoryEnd = 0;
	private $echoOnDestruct = false;
	private $initialStart = 0;
	private $duration = 0;
	private $initialQueries = 0;
	private $initialOverall = 0;
	private $sqlQueries = 0;
	private $sqlOverall = 0;
	
	function __construct(&$db,$type,$echoOnDestruct = false)
	{
		parent::__construct($db);
		$this->counterType = $type;
		$this->reset();
		$this->echoOnDestruct = $echoOnDestruct;
	}
	public function __destruct()
	{
		if ($this->echoOnDestruct)
		{
			$this->stop();
			echo $this;
		}
	}
	
	public function __toString()
	{
		$output = null;
		if ($this->counterType & self::TYPE_MEMORY)
		{
			$this->updateMemory();
			$output .= "MEMORY: {$this->memoryStart} to {$this->memoryEnd} (max. Peak {$this->memoryPeak});\n";
		}
		if ($this->counterType & self::TYPE_TIME)
		{
			$duration = $this->getDuration();
			$output .= "DURATION: {$duration} secs;\n";
		}
		if ($this->counterType & self::TYPE_SQL)
		{
			$this->updateSQL();
			$output .= "SQL queries: ".($this->sqlQueries).";\n";			
			$output .= "took ".$this->sqlOverall." secs;\n";
		}
		return $output;
	}
	
	public function getDuration()
	{
		$current = $this->getmicrotime();
		return ($current - $this->initialStart);
	}
	
	public function reset()
	{
		$this->resetTimer();
		$this->resetMemory();
		$this->resetSQL();
	}
	
	public function resetTimer()
	{
		if ($this->counterType & self::TYPE_TIME)
		{
			$this->initialStart = $this->getmicrotime();
			$this->duration = 0;
		}
	}	
	
	public function resetMemory()
	{
		if ($this->counterType & self::TYPE_MEMORY)
		{
			$this->memoryStart = memory_get_usage(true);	
			$this->memoryEnd = 0;
			$this->memoryPeak = memory_get_peak_usage(true);
		}
	}
	
	public function resetSQL()
	{
		if ($this->counterType & self::TYPE_SQL)
		{
			$this->initialOverall = $this->db->overallDuration;
			$this->initialQueries = $this->db->nQuery;		
		}
	}
	
	public function stop()
	{
		$this->stopTimer();
		$this->updateMemory();
		$this->updateSQL();
	}
	
	protected function updateMemory()
	{ 
		if ($this->counterType & self::TYPE_MEMORY)
		{
			$this->memoryEnd = memory_get_usage(true);
			$this->memoryPeak = memory_get_peak_usage(true);
		}
	}
	
	protected function updateSQL()
	{ 
		if ($this->counterType & self::TYPE_SQL)
		{
			$this->sqlOverall = $this->db->overallDuration - $this->initialOverall;
			$this->sqlQueries = $this->db->nQuery - $this->initialQueries;
		}
	}
	
	public function stopTimer()
	{
		if ($this->counterType & self::TYPE_TIME)
		{
			$current = $this->getmicrotime();
			$this->duration = ($current - $this->initialStart);
		}
	}
	
	protected function getmicrotime()
	{
		$t = microtime();
		$t = explode(' ',$t);
		return (float)$t[1]+ (float)$t[0];
	}
}
?>