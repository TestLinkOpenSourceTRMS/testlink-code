{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tcEdit_New_viewer.tpl,v 1.27 2010/10/11 18:11:29 franciscom Exp $
Purpose: smarty template - create new testcase

@internal Revisions:
  20101011 - franciscom - equalizing ID for DIV that function as CF container
  20101010 - franciscom - refactoring of BUGID 3062 -> call to checkTCaseDuplicateName() without global coupling
	20100306 - eloff - BUGID 3062 - Check for duplicate name via JAXA using checkTCaseDuplicateName()
	20090831 - franciscom - preconditions
	20090718 - franciscom - added management of custom field location
	20061231 - franciscom - viewer for tcEdit.tpl and tcNew.tpl
*}

{* ---------------------------------------------------------------- *}
{lang_get var='labels' 
          s='tc_title,alt_add_tc_name,summary,steps,expected_results,
             preconditions,
             execution_type,test_importance,tc_keywords,assign_requirements'}

{* Steps and results Layout management *}
{assign var="layout1" value="<br />"}
{assign var="layout2" value="<br />"}
{assign var="layout3" value="<br />"}

{if $gsmarty_spec_cfg->steps_results_layout == 'horizontal'}
	{assign var="layout1" value='<br /><table width="100%"><tr><td width="50%">'}
	{assign var="layout2" value='</td><td width="50%">'}
	{assign var="layout3" value="</td></tr></table><br />"}
{/if}
{* ---------------------------------------------------------------- *}
	<p />
	<div class="labelHolder"><label for="testcase_name">{$labels.tc_title}</label></div>
	<div>	
		<input type="text" name="testcase_name" id="testcase_name"
			size="{#TESTCASE_NAME_SIZE#}" 
			maxlength="{#TESTCASE_NAME_MAXLEN#}"
			onchange="content_modified = true"
			onkeypress="content_modified = true"
			onkeyup="javascript:checkTCaseDuplicateName($('testcase_id').value,$('testcase_name').value,
			                                            $('testsuite_id').value,'testcase_name_warning')"
			{if isset($gui->tc.name)}
		       value="{$gui->tc.name|escape}"
			{else}
		   		value=""
		   	{/if}
			title="{$labels.alt_add_tc_name}"/>
  			{include file="error_icon.tpl" field="testcase_name"}
			<span id="testcase_name_warning" class="warning"></span>
		<p />

		<div class="labelHolder">{$labels.summary}</div>
		<div>{$summary}</div>
    <br />

		<div class="labelHolder">{$labels.preconditions}</div>
		<div>{$preconditions}</div>
    
	  {* Custom fields - with before steps & results location - 20090718 - franciscom *}
    <br />
	  {if $gui->cf.before_steps_results neq ""}
	       <br/>
	       {* ID is important because is used on validationForm to get custom field container
	          that's how have to have SAME ID that other div below on page.
	          NOTICE that only one of this div are active, if will not be the case we will
	          have a problem because ID has to be UNIQUE
	        *}
	       <div id="cfields_design_time" class="custom_field_container">
	       {$gui->cf.before_steps_results}
	       </div>
	       
	  {/if}
		{$layout1}

{* Multiple Test Case Steps Feature                              *}   
{* <div class="labelHolder">{$labels.steps}</div>                *}
{* <div>{$steps}</div>                                           *}
{* {$layout2}                                                    *} 
{* <div class="labelHolder">{$labels.expected_results}</div>     *}
{* <div>{$expected_results}</div>                                *} 
{* {$layout3}                                                    *}

		{if $session['testprojectOptions']->automationEnabled}
			<div class="labelHolder">{$labels.execution_type}
			<select name="exec_type" onchange="content_modified = true">
    	  	{html_options options=$gui->execution_types selected=$gui->tc.execution_type}
	    	</select>
			</div>
    	{/if}

	    {if $session['testprojectOptions']->testPriorityEnabled}
		   	<div>
			<span class="labelHolder">{$labels.test_importance}</span>
			<select name="importance" onchange="content_modified = true">
    	  	{html_options options=$gsmarty_option_importance selected=$gui->tc.importance}
	    	</select>
			</div>
		{/if}
    	
    </div>

	{* Custom fields - with standard location - 20090718 - franciscom *}
	{if $gui->cf.standard_location neq ""}
	     <br/>
	     <div id="cfields_design_time" class="custom_field_container">
	     {$gui->cf.standard_location}
	     </div>
	{/if}

	<div>
	<a href={$gsmarty_href_keywordsView}>{$labels.tc_keywords}</a>
	{include file="opt_transfer.inc.tpl" option_transfer=$gui->opt_cfg}
	</div>
	
	{if $gui->opt_requirements==TRUE && $gui->grants->requirement_mgmt=='yes' && isset($gui->tc.testcase_id)}
		<div>
		<a href="javascript:openReqWindow({$gui->tc.testcase_id})">{$labels.assign_requirements}</a>    
		</div>
	{/if}
