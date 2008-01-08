<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: logging.inc.php,v $
 *
 * @version $Revision: 1.14 $
 * @modified $Date: 2008/01/08 19:50:44 $
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
/** Set default logging level */
// 20061203 - franciscom
if( !defined('TL_LOG_LEVEL_DEFAULT') )
{
  define('TL_LOG_LEVEL_DEFAULT','NONE');
}
tlLogSetLevel(TL_LOG_LEVEL_DEFAULT);

$tl_log_levels = array(
    'NONE'  => 0,
    'ERROR' => 1,
    'INFO'  => 2,
    'DEBUG' => 3,
    'EXTENDED' => 4,
	);

/**
* There are 4 logging levels available. Log messages will only be displayed 
* if they are at a level less verbose than that currently set. So, we can turn 
* on logging with the following command:
*
*    tlLogSetLevel('INFO');
*/
function tlLogSetLevel ($level = ERROR) 
{
    global $tl_log_level;
    $tl_log_level = $level;
}

/**
* Now any log messages from the levels ERROR or INFO will be recorded. 
* DEBUG messages will be ignored. We can have as many log entries as we like. 
* They take the form:
*
*    tLog("testing level ERROR", 'ERROR');
*    tLog("testing level INFO", 'INFO');
*    tLog("testing level DEBUG");
*
* This will add the following entries to the log:
*
* [05/Jan/27 13:05:56][INFO][guest] - Login ok. (Timing: 0.000763)
* [05/Jan/27 13:06:03][DEBUG][havlatm] - User id = 10, Rights = admin
*
* @author Andreas Morsing : changed to format of log entries
* @author Andreas Morsing : errors in extended level will be shown in red instead of
* 							inlined as comments
*/
function tLog ($message, $level = 'DEBUG') 
{
    global $tl_log_level, $tl_log_levels;
    if ($tl_log_levels[$tl_log_level] < $tl_log_levels[$level])
        return false;
    else
	{
		$sID = isset($_SESSION) ? session_id() : "<nosession>";
        $fd = fopen(tlGetLogFileName(),'a+');
		if ($fd)
		{
			$userName = isset($_SESSION['currentUser']->login) ? $_SESSION['currentUser']->login : "<unknown>";
	    	fputs($fd,'['.date("y/M/j H:i:s"). ']['. $level . '][' . $_SERVER['SCRIPT_NAME'] . ']['. $userName .'][' . $sID . "]\n\t". $message. "\n");
	    	fclose($fd);
		}
		$bExtendedLogLevel = ($tl_log_levels[$tl_log_level] >= $tl_log_levels['EXTENDED']);
		if ($bExtendedLogLevel)
		{
			if ($level == 'ERROR')
				echo "<pre style=\"color:red\">";
			else 
				echo "\n<!--\n";
			echo $message;
			if ($level == 'ERROR')
				echo "</pre>";
			else 
				echo "\n-->\n";
		}
    	return true;
    }
}

/**
 * the logfilename is dynamic and depends of the user and its session
 *
 * @return string returns the name of the logfile
 *
 * @author Andreas Morsing
 * 20050821 - scs - used directory_separator instead of slash
 **/
function tlGetLogFileName()
{
	global $g_log_path; 
	$uID = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
		
	return $g_log_path . DIRECTORY_SEPARATOR . 'userlog' . $uID . ".log";
}
/**
* You can empty the log at any time with:
*   tlLogReset();
* @author Andreas Morsing - logfilenames are dynamic
*/
function tlLogReset() 
{
    @unlink(tlGetLogFileName());
}


/** 
* Optimization 
*
* We need a way to test the execution speed of our code before we can easily 
* perform optimizations. A set of timing functions that utilize microtime() is 
* the easiest method:
*/
function tlTimingStart ($name = 'default') 
{
    global $tlTimingStart;
    $tlTimingStart[$name] = explode(' ', microtime());
}

function tlTimingStop ($name = 'default') 
{
    global $tlTimingStop;
    $tlTimingStop[$name] = explode(' ', microtime());
}

function tlTimingCurrent ($name = 'default') 
{
    global $tlTimingStart, $tlTimingStop;
    if (!isset($tlTimingStart[$name])) {
        return 0;
    }
    if (!isset($tlTimingStop[$name])) {
        $stopTime = explode(' ', microtime());
    }
    else {
        $stopTime = $tlTimingStop[$name];
    }
    // do the big numbers first so the small ones aren't lost
    $current = $stopTime[1] - $tlTimingStart[$name][1];
    $current += $stopTime[0] - $tlTimingStart[$name][0];
    return $current;
}
/**
* Now we can check the execution time of any code very easily. We can even run 
* a number of execution time checks simultaneously because we have established 
* named timers.
*
* See the optimizations section below for the examination of echo versus 
* inline coding for an example of the use of these functions.
*/
?>