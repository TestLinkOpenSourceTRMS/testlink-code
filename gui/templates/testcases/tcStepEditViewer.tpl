{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tcStepEditViewer.tpl,v 1.7 2010/03/26 22:50:42 franciscom Exp $
Purpose: test case step edit/create viewer

Rev:
 20100326 - franciscom - step number can not be edited anymore
 20100111 - eloff - BUGID 2036 - Check modified content before exit
*}

{* ---------------------------------------------------------------- *}
{lang_get var='labels' 
          s='tc_title,alt_add_tc_name,expected_results,step_details, 
             step_number_verbose,execution_type,step_number,execution_type_short_descr'}


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
	<div class="labelHolder"><label for="step_number">{$labels.step_number_verbose}:</label>{$gui->step_number}</div>
	<div>
  <input type="hidden" name="step_number" id="step_number"  value="{$gui->step_number}">
	{*	
		<input type="text" name="step_number" id="step_number" readonly="readonly"
			     value="{$gui->step_number}" size="{#STEP_NUMBER_SIZE#}"
			     maxlength="{#STEP_NUMBER_MAXLEN#}"
			     onchange="content_modified = true"	onkeypress="content_modified = true"/>
  	       {include file="error_icon.tpl" field="step_number"}
		<p />
  *}
		{$layout1}

		<div class="labelHolder">{$labels.step_details}</div>
		<div>{$steps}</div>
		{$layout2}
		<div class="labelHolder">{$labels.expected_results}</div>
		<div>{$expected_results}</div>
		{$layout3}

		{if $session['testprojectOptions']->automationEnabled}
			<div class="labelHolder">{$labels.execution_type}
			<select name="exec_type" onchange="content_modified = true">
    	  	{html_options options=$gui->execution_types selected=$gui->step_exec_type}
	    	</select>
			</div>
    	{/if}
    </div>
