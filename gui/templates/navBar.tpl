{*
	Testlink Open Source Project - http://testlink.sourceforge.net/
	$Id: navBar.tpl,v 1.51 2010/01/31 09:50:44 franciscom Exp $
	Purpose: smarty template - title bar + menu

	rev :
	  20100131 - franciscom - moved get_docs() to javascript library
		20090902 - timeout warning 
		20080504 - access to local documentation
		20080211 - changes action for user management
		20070331 - BUGID 760 - added truncate to fix
* ----------------------------------------------------------------- *}
{lang_get var="labels"
          s="title_events,event_viewer,home,testproject,title_specification,title_execute,
             title_edit_personal_data,th_tcid,link_logout,title_admin,
             search_testcase,title_results,title_user_mgmt, warn_session_timeout"}
{assign var="cfg_section" value=$smarty.template|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl" openHead="yes"}
{literal}
<script type="text/javascript">
// -------- Session Timeout Warning functions -------
/** 
 * Session Timeout Warning functions: timeoutDisplay()
 * @return string time for display
 * @used function timeoutDown()
 */
function timeoutDisplay(min, sec) 
{
	var disp = "";
	if (min <= 9) 
		disp = " 0";
	else
		disp = " ";
	disp += min + ":";
	if (sec <= 9) 
		disp += "0" + sec;
	else 
		disp += sec;
	
	return disp;
}

/** 
 * Session Timeout Warning functions: timeoutDown() 
 * decrease timer value, diplay it and warn
 * @used function timeoutInit()
 */
function timeoutDown() 
{
	timeoutSec--;
	if (timeoutSec == -1) 
	{ 
		timeoutSec = 59; 
		timeoutMin--; 
	}
	if (timeoutMin < 5) 
	{
		timerObject.innerHTML = timeoutDisplay(timeoutMin, timeoutSec);
	}
	if (timeoutMin == 0 && timeoutSec == 0) 
	{
		alert(timerWarning);
	}
	else
	{ 
		setTimeout("timeoutDown()", 1000);
	}
}

/* 
 * Session Timeout Warning functions: timeoutInit()
 * @used HTML: 
 * <body onload="timeIt(document.getElementById('clockan'),'{$labels.warn_session_timeout}')">
 * ...
 *	<form name="timerform">
 *	<input type="text" name="clock" size="7" value="0:10"><p>
 *	</form>
 */
function timeoutInit(displayedTimer,sessionWarning) 
{
	timeoutMin = sessionDurationMin;
	timeoutSec = sessionDurationSec;
	timerObject = displayedTimer;
	timerWarning = sessionWarning;
	timeoutDown();
}
</script>
{/literal}
</head>
<body style="min-width: 800px;" onload="timeoutInit(document.getElementById('clockan'),'{$labels.warn_session_timeout}')">
<div style="float:left; height: 100%;">
	<a href="index.php" target="_parent">
	<img alt="Company logo"	title="logo" style="width: 115px; height: 53px;" 
	src="{$smarty.const.TL_THEME_IMG_DIR}{$tlCfg->company_logo}" /></a>
</div>
	
<div class="menu_title">

	{if $gui->TestProjects != ""}
	<div style="float: right; padding: 2px;">
		<form name="productForm" action="lib/general/navBar.php" method="get">
			<span style="font-size: 80%">{$labels.testproject}</span>
			<select class="menu_combo" name="testproject" onchange="this.form.submit();">
	      	{foreach key=tproject_id item=tproject_name from=$gui->TestProjects}
	  		  <option value="{$tproject_id}" title="{$tproject_name|escape}"
	  		    {if $tproject_id == $gui->tprojectID} selected="selected" {/if}>
	  		    {$tproject_name|truncate:#TESTPROJECT_TRUNCATE_SIZE#|escape}</option>
	  		{/foreach}
			</select>
		</form>
	</div>
	{/if}

	<span class="bold">TestLink {$tlVersion|escape} : {$gui->whoami|escape}</span>

</div>

<div class="menu_bar" style="margin: 0px 5px 0px 135px;">

	<span style="float: right;">
   		<a href='lib/usermanagement/userInfo.php' target="mainframe" accesskey="i"
      		tabindex="6">{$labels.title_edit_personal_data}</a>
	 | 	<a href="logout.php" target="_parent" accesskey="q">{$labels.link_logout}
		<span id="clockan"></span></a>
	</span>

	{$session.testprojectTopMenu}

{if $gui->tprojectID}

	{if $gui->grants->view_testcase_spec == "yes"}
		<form style="display:inline" target="mainframe" name="searchTC" id="searchTC"
		      action="lib/testcases/archiveData.php" method="get">
		<span style="font-size: 80%">{$labels.th_tcid}: </span>
		<input style="font-size: 80%;" type="text" size="{$gui->searchSize}"
		       title="{$labels.search_testcase}" name="targetTestCase" value="{$gui->tcasePrefix}" />
    	{* useful to avoid a call to method to get test case prefix in called page*}
		<input type="hidden" id="tcasePrefix" name="tcasePrefix" value="{$gui->tcasePrefix}" />
		<img src="{$smarty.const.TL_THEME_IMG_DIR}/magnifier.png"
		     title="{$labels.search_testcase}" alt="{$labels.search_testcase}"
		     onclick="document.getElementById('searchTC').submit()" class="clickable" />
		<input type="hidden" name="edit" value="testcase"/>
		<input type="hidden" name="allow_edit" value="0"/>
		</form>
	{/if}
{/if}
</div>

{if $gui->updateMainPage == 1}
{literal}
<script type="text/javascript">
	parent.mainframe.location = "{/literal}{$basehref}{literal}lib/general/mainPage.php";
</script>
{/literal}
{/if}

</body>
</html>
