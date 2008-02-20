<?php
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
/*
	This function fires audit events

	@param string $message the message which describes the event in a human readable way (best a tlMetaString) is used
	@param string $activityCode and shorthand activity code describing the event
	@param int $objectID the id of the object to which the event refers to
	@param string $objectType the type of the object the event refers to (this should be the name of the database table the objet is stored

	@return int return tl::OK if all is OK, tl::ERROR else
*/
function logAuditEvent($message,$activityCode = null,$objectID = null,$objectType = null)
{
	return tLog($message,"AUDIT","GUI",$objectID,$objectType,$activityCode);
}
function logWarningEvent($message,$activityCode = null,$objectID = null,$objectType = null)
{
	return tLog($message,"WARNING","GUI",$objectID,$objectType,$activityCode);
}

function tLog($message, $level = 'DEBUG', $source = "GUI",$objectID = null,$objectType = null, $activityCode = null)
{
	global $g_tlLogger;
	if (!$g_tlLogger)
		return tl::ERROR;
	$t = $g_tlLogger->getTransaction();
	if (!$t)
		return tl::ERROR;
	//to avoid transforming old code, we check if we have old string-like logLevel or new tlLogger-LogLevel
	$logLevel = is_string($level) ? tlLogger::$revertedLogLevels[$level] : $level;
	$t->add($logLevel,$message,$source,$activityCode,$objectID,$objectType);

	return tl::OK;
	/*
		//SCHLUNDUS: could be a special "to page" logger?
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
		*/
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