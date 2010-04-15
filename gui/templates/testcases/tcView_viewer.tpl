{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tcView_viewer.tpl,v 1.62 2010/04/15 20:06:13 franciscom Exp $
viewer for test case in test specification

rev:
    20100415 - franciscom - move compare version feature out of edit control, because seems OK
                            that no user right is needed to compare.
    20100327 - franciscom - fixed problem with goback from create step
    20100301 - franciscom - BUGID 3181
    20100125 - franciscom - added check to display info about steps only if test case has steps
    20100124 - franciscom - fixed problem on display of test case version assignemt 
                            to different test plans + add table sorting
    20100123 - franciscom - BUGID 0003086: After execution of testcase, 
                                           a new version should be created before editing test steps 
    20090831 - franciscom - preconditions
    20090418 - franciscom - BUGID 2364 - added fine grain control of button enable/disable
    20090414 - franciscom - BUGID 2378 - check for active test plan existence to display btn_add_to_testplan
    20090308 - franciscom - added logic to display button that allow assign test case version 
                            to test plans. 
    20090215 - franciscom - BUGID - show info about links to test plans
*}
{lang_get var="labels"
          s="requirement_spec,Requirements,tcversion_is_inactive_msg,
             btn_edit,btn_delete,btn_mv_cp,btn_del_this_version,btn_new_version,
             btn_export,btn_execute_automatic_testcase,version,testplan_usage,
             testproject,testsuite,title_test_case,summary,steps,btn_add_to_testplans,
             title_last_mod,title_created,by,expected_results,keywords,
             btn_create_step,step_number,btn_reorder_steps,step_actions,
             execution_type_short_descr,delete_step,show_hide_reorder,
             test_plan,platform,
             execution_type,test_importance,none,preconditions,btn_compare_versions"}

{lang_get s='warning_delete_step' var="warning_msg" }
{lang_get s='delete' var="del_msgbox_title" }

{* will be useful in future to semplify changes *}
{assign var="tableColspan" value=$gui->tableColspan} 
{assign var="addInfoDivStyle" value='style="padding: 5px 3px 4px 10px;"'}


{assign var="module" value='lib/testcases/'}
{assign var="tcase_id" value=$args_testcase.testcase_id}
{assign var="tcversion_id" value=$args_testcase.id}

{* Used on several operations to implement goback *}
{assign var="tcViewAction" value="lib/testcases/archiveData.php?tcase_id=$tcase_id"}
             
{assign var="hrefReqSpecMgmt" value="lib/general/frmWorkArea.php?feature=reqSpecMgmt"}
{assign var="hrefReqSpecMgmt" value=$basehref$hrefReqSpecMgmt}

{assign var="hrefReqMgmt" value="lib/requirements/reqView.php?showReqSpecTitle=1&requirement_id="}
{assign var="hrefReqMgmt" value=$basehref$hrefReqMgmt}

{assign var="url_args" value="tcAssign2Tplan.php?tcase_id=$tcase_id&tcversion_id=$tcversion_id"}
{assign var="hrefAddTc2Tplan"  value="$basehref$module$url_args"}

{assign var="url_args" value="tcEdit.php?doAction=editStep&testcase_id=$tcase_id&tcversion_id=$tcversion_id"}
{assign var="url_args" value="$url_args&goback_url=$basehref$tcViewAction&step_id="}
{assign var="hrefEditStep"  value="$basehref$module$url_args"}

{assign var="tcExportAction" value="lib/testcases/tcExport.php?goback_url="}
{assign var="exportTestCaseAction" value="$basehref$tcExportAction$basehref$tcViewAction"}


{assign var="author_userinfo" value=$args_users[$args_testcase.author_id]}
{assign var="updater_userinfo" value=""}
{if $args_testcase.updater_id != ''}
  {assign var="updater_userinfo" value=$args_users[$args_testcase.updater_id]}
{/if}

{if $args_show_title == "yes"}
    {if $args_tproject_name != ''}
     <h2>{$labels.testproject} {$args_tproject_name|escape} </h2>
    {/if}
    {if $args_tsuite_name != ''}
     <h2>{$labels.testsuite} {$args_tsuite_name|escape} </h2>
    {/if}
	  <h2>{$labels.title_test_case} {$args_testcase.name|escape} </h2>
{/if}
{assign var="warning_edit_msg" value=""}

{if $args_can_do->edit == "yes" }

  {assign var="edit_enabled" value=0}
  {* 20070628 - franciscom - Seems logical you can disable some you have executed before *}
  {assign var="active_status_op_enabled" value=1}
  {assign var="has_been_executed" value=0}
  {lang_get s='can_not_edit_tc' var="warning_edit_msg"}
  {if $args_status_quo == null || $args_status_quo[$args_testcase.id].executed == null}
      {assign var="edit_enabled" value=1}
      {assign var="warning_edit_msg" value=""}
  {else} 
    {if isset($args_tcase_cfg) && $args_tcase_cfg->can_edit_executed eq 1}
      {assign var="edit_enabled" value=1} 
      {assign var="has_been_executed"  value=1} 
      {lang_get s='warning_editing_executed_tc' var="warning_edit_msg"}
    {/if} 
  {/if}

  <div class="groupBtn">
	<div style="margin-bottom: 5px;">
	<span style="float: left">
	  <form id="topControls" name="topControls" method="post" action="lib/testcases/tcEdit.php">
	  <input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
	  <input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />
	  <input type="hidden" name="has_been_executed" value="{$has_been_executed}" />
	  <input type="hidden" name="doAction" value="" />
	  <input type="hidden" name="show_mode" value="{$gui->show_mode}" />


	  {assign var="go_newline" value=""}
	  {if $edit_enabled}
	 	    <input type="submit" name="edit_tc" 
	 	           onclick="doAction.value='edit';{$gui->submitCode}" value="{$labels.btn_edit}" />
	  {/if}
	
	  {* Double condition because for test case versions WE DO NOT DISPLAY this
	     button, using $args_can_delete_testcase='no'
	  *}
		{if $args_can_do->delete_testcase == "yes" &&  $args_can_delete_testcase == "yes"}
			<input type="submit" name="delete_tc" value="{$labels.btn_delete}" />
	  {/if}
	
	  {* Double condition because for test case versions WE DO NOT DISPLAY this
	     button, using $args_can_move_copy='no'
	  *}
	  {if $args_can_do->copy == "yes" && $args_can_move_copy == "yes" }
	   		<input type="submit" name="move_copy_tc"   value="{$labels.btn_mv_cp}" />
	     	{assign var="go_newline" value="<br />"}
	  {/if}
	
	 	{if $args_can_do->delete_version == "yes" && $args_can_delete_version == "yes"}
			 <input type="submit" name="delete_tc_version" value="{$labels.btn_del_this_version}" />
	  {/if}

	 	{if $args_can_do->create_new_version == "yes" }
  		<input type="submit" name="do_create_new_version"   value="{$labels.btn_new_version}" />
	  {/if}

	
		{* --------------------------------------------------------------------------------------- *}
		{if $active_status_op_enabled eq 1 && $args_can_do->deactivate=='yes'}
	        {if $args_testcase.active eq 0}
				      {assign var="act_deact_btn" value="activate_this_tcversion"}
				      {assign var="act_deact_value" value="activate_this_tcversion"}
				      {assign var="version_title_class" value="inactivate_version"}
	      	{else}
				      {assign var="act_deact_btn" value="deactivate_this_tcversion"}
				      {assign var="act_deact_value" value="deactivate_this_tcversion"}
				      {assign var="version_title_class" value="activate_version"}
	      	{/if}
	      	<input type="submit" name="{$act_deact_btn}"
	                           value="{lang_get s=$act_deact_value}" />
	  {/if}

  {* 20090306 - franciscom*}
  {if $args_can_do->add2tplan == "yes" && $args_has_testplans}
  <input type="button" id="addTc2Tplan_{$args_testcase.id}"  name="addTc2Tplan_{$args_testcase.id}" 
         value="{$labels.btn_add_to_testplans}" onclick="location='{$hrefAddTc2Tplan}'" />

  {/if}
	</form>
	</span>

	<span>
	<form id="tcexport" name="tcexport" method="post" action="{$exportTestCaseAction}" >
		<input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
		<input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />
		<input type="submit" name="export_tc" style="margin-left: 3px;" value="{$labels.btn_export}" />
		{* 20071102 - franciscom *}
		{*
		<input type="button" name="tstButton" value="{$labels.btn_execute_automatic_testcase}"
		       onclick="javascript: startExecution({$args_testcase.testcase_id},'testcase');" />
		*}
	</form>
	</span>
	
	</div>
{/if} {* user can edit *}

	<div>
	<span>
	{* compare versions *}
	{if $args_testcase.version > 1}
	  <form id="version_compare" name="version_compare" method="post" action="lib/testcases/tcCompareVersions.php">
	  		<input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
	  		<input type="submit" name="compare_versions" value="{$labels.btn_compare_versions}" />
	  </form>
	{/if}
	</span>
  </div> {* class="groupBtn" *}



{* --------------------------------------------------------------------------------------- *}
  {if $args_testcase.active eq 0}
    <br /><div class="messages" align="center">{$labels.tcversion_is_inactive_msg}</div>
  {/if}
 	{if $warning_edit_msg neq ""}
 	    <br /><div class="messages" align="center">{$warning_edit_msg}</div>
 	{/if}

<form id="stepsControls" name="stepsControls" method="post" action="lib/testcases/tcEdit.php">
<input type="hidden" name="goback_url" value="{$basehref}{$tcViewAction}" />
<table class="simple">
  {if $args_show_title == "yes"}
	<tr>
		<th colspan="{$tableColspan}">
		{$args_testcase.tc_external_id}{$smarty.const.TITLE_SEP}{$args_testcase.name|escape}</th>
	</tr>
  {/if}

  {if $args_show_version == "yes"}
	  <tr>
	  	<td class="bold" colspan="{$tableColspan}">{$labels.version}
	  	{$args_testcase.version|escape}
	  	</td>
	  </tr>
	{/if}

	<tr class="time_stamp_creation">
  		<td colspan="{$tableColspan}">
      		{$labels.title_created}&nbsp;{localize_timestamp ts=$args_testcase.creation_ts }&nbsp;
      		{$labels.by}&nbsp;{$author_userinfo->getDisplayName()|escape}
  		</td>
  </tr>

 {if $args_testcase.updater_last_name != "" || $args_testcase.updater_first_name != ""}
	<tr class="time_stamp_creation">
  		<td colspan="{$tableColspan}">
    		{$labels.title_last_mod}&nbsp;{localize_timestamp ts=$args_testcase.modification_ts}
		  	&nbsp;{$labels.by}&nbsp;{$updater_userinfo->getDisplayName()|escape}
    	</td>
  </tr>
 {/if}
 


	<tr>
		<td class="bold" colspan="{$tableColspan}">{$labels.summary}</td>
	</tr>
	<tr>
		<td colspan="{$tableColspan}">{$args_testcase.summary}</td>
	</tr>

	<tr>
		<td class="bold" colspan="{$tableColspan}">{$labels.preconditions}</td>
	</tr>
	<tr>
		<td colspan="{$tableColspan}">{$args_testcase.preconditions}</td>
	</tr>

	{* 20090718 - franciscom *}
	{if $args_cf.before_steps_results neq ''}
	<tr>
	  <td>
    {$args_cf.before_steps_results}
    </td>
	</tr>
	{/if}

{* OLD STYLE *}
{*	<tr>                                               *}
{*		<th width="50%">{$labels.steps}</th>             *}
{*		<th width="50%">{$labels.expected_results}</th>  *}
{*	</tr>                                              *}
{*	<tr>                                               *}
{*		<td>{$args_testcase.steps}</td>                  *}
{*		<td>{$args_testcase.expected_results}</td>       *}
{*	</tr>                                              *}
	
	{if $args_testcase.steps != ''}
	<tr>
		<th width="{$tableColspan}">
    {if $edit_enabled && $args_testcase.steps != ''}
		<img src="{$tlImages.reorder}" align="left" title="{$labels.show_hide_reorder}" 
		    onclick="showHideByClass('span','order_info');event.stopPropagation();">
    {/if}
		{$labels.step_number}</th>
		<th>{$labels.step_actions}</th>
		<th>{$labels.expected_results}</th>
		<th width="25">{$labels.execution_type_short_descr}</th>
    {if $edit_enabled}
		  <th>&nbsp;</th>
    {/if}
	</tr>
  {/if}
	{if $args_testcase.steps != ''}
 	{foreach from=$args_testcase.steps item=step_info }
	<tr>
		<td style="text-align:right;"><span class="order_info" style='display:none'>
		<input type="text" name="step_set[{$step_info.id}]" id="step_set_{$step_info.id}"
		       value="{$step_info.step_number}" 
			     size="{#STEP_NUMBER_SIZE#}" 	maxlength="{#STEP_NUMBER_MAXLEN#}"
  	{include file="error_icon.tpl" field="step_number"}
		</span>{if $edit_enabled}<a href="{$hrefEditStep}{$step_info.id}">{/if}{$step_info.step_number}</a></td>
		<td >{if $edit_enabled}<a href="{$hrefEditStep}{$step_info.id}">{/if}{$step_info.actions}</a></td>
		<td >{$step_info.expected_results}</td>
		<td>{$gui->execution_types[$step_info.execution_type]}</td>

    {if $edit_enabled}
		<td class="clickable_icon">
       <img style="border:none;cursor: pointer;" 
            title="{$labels.delete_step}"  alt="{$labels.delete_step}" 
 					  onclick="delete_confirmation({$step_info.id},'{$step_info.step_number|escape:'javascript'|escape}',
 					                               '{$del_msgbox_title}','{$warning_msg}');"
  				  src="{$delete_img}"/>
  	</td>
  	{/if}
	</tr>
  {/foreach}	
	{/if}
</table>

<div {$addInfoDivStyle}>
  <input type="hidden" name="doAction" value="" />
  <input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
  <input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />
  <input type="hidden" name="has_been_executed" value="{$has_been_executed}" />
  {if $edit_enabled}
  <input type="submit" name="create_step" 
  	 	   onclick="doAction.value='createStep';{$gui->submitCode}" value="{$labels.btn_create_step}" />

  <span class="order_info" style='display:none'>
  <input type="submit" name="renumber_step" 
  	 	   onclick="doAction.value='doReorderSteps';{$gui->submitCode}validateStepsReorder('stepsControls');" 
  	 	   value="{$labels.btn_reorder_steps}" />
  </span>
  {/if}
</div>
</form>

{if $session['testprojectOptions']->automationEnabled}
  <div {$addInfoDivStyle}>
		<span class="labelHolder">{$labels.execution_type} {$smarty.const.TITLE_SEP}</span>
		{$gui->execution_types[$args_testcase.execution_type]}
	</div>
{/if}

{if $session['testprojectOptions']->testPriorityEnabled}
   <div {$addInfoDivStyle}>
		<span class="labelHolder">{$labels.test_importance} {$smarty.const.TITLE_SEP}</span>
		{$gsmarty_option_importance[$args_testcase.importance]}
	</div>
{/if}

  {* 20090718 - franciscom *}
	{if $args_cf.standard_location neq ''}
	<div {$addInfoDivStyle}>
        <div id="cfields_design_time" class="custom_field_container">{$args_cf.standard_location}</div>
	</div>
	{/if}

	<div {$addInfoDivStyle}>
		<table cellpadding="0" cellspacing="0" style="font-size:100%;">
			    <tr>
			     	<td width="35%" style="vertical-align:top;"><a href={$gsmarty_href_keywordsView}>{$labels.keywords}</a>: &nbsp;
					</td>
				 	<td style="vertical-align:top;">
				 	  	{foreach item=keyword_item from=$args_keywords_map}
						    {$keyword_item.keyword|escape}
						    <br />
	      				{foreachelse}
    	  					{$labels.none}
						{/foreach}
					</td>
				</tr>
				</table>
	</div>

	{if $gui->opt_requirements == TRUE && $gui->view_req_rights == "yes"}
	<div {$addInfoDivStyle}>
		<table cellpadding="0" cellspacing="0" style="font-size:100%;">
     			  <tr>
       			  <td colspan="{$tableColspan}" style="vertical-align:text-top;"><span><a title="{$labels.requirement_spec}" href="{$hrefReqSpecMgmt}"
      				target="mainframe" class="bold">{$labels.Requirements}</a>
      				: &nbsp;</span>
      			  </td>
      			  <td>
      				{section name=item loop=$args_reqs}
      					<span onclick="javascript: open_top('{$hrefReqMgmt}{$args_reqs[item].id}');"
      					style="cursor:  pointer;  color: #0000ff; ">[{$args_reqs[item].req_spec_title|escape}]&nbsp;{$args_reqs[item].req_doc_id|escape}:{$args_reqs[item].title|escape}</span>
      					{if !$smarty.section.item.last}<br />{/if}
      				{sectionelse}
      					{$labels.none}
      				{/section}
      			  </td>
    		    </tr>
	  </table>
	</div>
	{/if}
	
{if $args_linked_versions != null }
  {* Test Case version Test Plan Assignment *}
  <br />
	<div {$addInfoDivStyle}>
	  {$labels.testplan_usage}
		<table class="simple sortable">
    <th>{$labels.version}</th>
    <th>{$sortHintIcon}{$labels.test_plan}</th>
    <th>{$sortHintIcon}{$labels.platform}</th>
    {foreach from=$args_linked_versions item=link2tplan_platform}
      {foreach from=$link2tplan_platform item=link2platform key=tplan_id}
        {foreach from=$link2platform item=version_info}
          <tr>
          <td style="width:10%;text-align:center;">{$version_info.version}</td>
          <td>{$version_info.tplan_name|escape}</td>
          <td>
          {* BUGID 3181 *}
          {if $version_info.platform_id > 0}
            {$gui->platforms[$version_info.platform_id]|escape}
          {/if}          
          </td>
          </tr>
        {/foreach}
      {/foreach}
    {/foreach}
	  </table>
	</div>
{/if}	