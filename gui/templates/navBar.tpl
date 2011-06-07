{*
Testlink Open Source Project - http://testlink.sourceforge.net/

@filesource	navBar.tpl
Purpose: smarty template - title bar + menu

@internal revisions
20110605 - franciscom - added target to form in order to control on what frame is opened
						TICKET 4565: Current Test Plan resets every time portal page is loaded
*}
{lang_get var="labels"
          s="testproject,title_edit_personal_data,link_logout,search_testcase"}
{$cfg_section=$smarty.template|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}
{include file="inc_head.tpl" openHead="yes"}
</head>
<body style="min-width: 800px;">
<div style="float:left; height: 100%;">
	<a href="index.php?tproject_id={$gui->tprojectID}&tplan_id={$gui->tplanID}" target="_parent">
	<img alt="Company logo"	title="logo" style="width: 115px; height: 53px;" 
	src="{$smarty.const.TL_THEME_IMG_DIR}{$tlCfg->company_logo}" /></a>
</div>
	
<div class="menu_title">
	<span class="bold">TestLink {$tlVersion|escape} : {$gui->whoami|escape}</span>
		<span>[ <a href='lib/usermanagement/userInfo.php?tproject_id={$gui->tprojectID}' 
		target="mainframe" accesskey="i"
      		tabindex="6">{$labels.title_edit_personal_data}</a>
	 | 	<a href="logout.php" target="_parent" accesskey="q">{$labels.link_logout}</a> ]
	</span>

</div>
<div class="menu_bar" style="margin: 0px 5px 0px 135px;">
{$gui->topMenu}

{if $gui->tprojectID}
	{if $gui->grants->view_testcase_spec == "yes"}
		<form style="display:inline" target="mainframe" name="searchTC" id="searchTC"
		      action="lib/testcases/archiveData.php?tproject_id={$gui->tprojectID}" method="get">
		<input style="font-size: 80%; position:relative; top:-1px;" type="text" size="{$gui->searchSize}"
		       title="{$labels.search_testcase}" name="targetTestCase" value="{$gui->tcasePrefix}" />
    	{* useful to avoid a call to method to get test case prefix in called page*}
		<input type="hidden" id="tcasePrefix" name="tcasePrefix" value="{$gui->tcasePrefix}" />
		<img src="{$tlImages.search}"
		     title="{$labels.search_testcase}" alt="{$labels.search_testcase}"
		     onclick="document.getElementById('searchTC').submit()" class="clickable" 
		     style="position:relative; top:2px;" />
		<input type="hidden" name="edit" value="testcase"/>
		<input type="hidden" name="allow_edit" value="0"/>
		</form>
	{/if}
{/if}

{if $gui->tprojectSet != ""}
	<div style="display: inline; float: right;">
		<form style="display:inline" name="tprojectChoice" target="titlebar"
			  action="lib/general/navBar.php" method="get">
			 {$labels.testproject}
			<select style="font-size: 80%;position:relative; top:-1px;" name="tprojectIDNavBar" 
					onchange="this.form.submit();">
	      	{foreach key=tproject_id item=tproject_name from=$gui->tprojectSet}
	  		  <option value="{$tproject_id}" title="{$tproject_name|escape}"
	  		    {if $tproject_id == $gui->tprojectID} selected="selected" {/if}>
	  		    {$tproject_name|truncate:#TESTPROJECT_TRUNCATE_SIZE#|escape}</option>
	  		{/foreach}
			</select>
		</form>
	</div>
{/if}

</div>

 
{if $gui->updateMainPage == 1}
<script type="text/javascript">
	parent.mainframe.location = "{$basehref}lib/general/mainPage.php?tproject_id={$gui->tprojectID}&tplan_id={$gui->tplanID}";
</script>
{/if}

</body>
</html>