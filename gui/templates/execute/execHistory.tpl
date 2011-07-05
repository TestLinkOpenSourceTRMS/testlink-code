{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filsesource execHistory.tpl
*}	
{lang_get var='labels' 
          s='title_test_case,th_test_case_id,version,date_time_run,platform,test_exec_by,
             exec_status,testcaseversion,attachment_mgmt,deleted_user,build,testplan,
             execution_type_manual,execution_type_auto,run_mode'}


<html>
{include file="inc_head.tpl"}
</head>

{assign var="attachment_model" value=$gui->exec_cfg->att_model}
{assign var="my_colspan" value=$attachment_model->num_cols+2}

<body onUnload="storeWindowSize('execHistoryPopup')">
{if $gui->main_descr != ''}
{$gui->main_descr|escape}<br>
{$gui->detailed_descr|escape}<br>
{/if}

<table cellspacing="0" class="exec_history">
	{* Table Header *} 
	<tr>
		<th style="text-align:left">{$labels.date_time_run}</th>
		<th style="text-align:left">{$labels.testplan}</th>
		<th style="text-align:left">{$labels.build}</th>
		{if $gui->displayPlatformCol}
			{assign var="my_colspan" value=$my_colspan+1}
			<th style="text-align:left">{$labels.platform}</th>
		{/if}
		<th style="text-align:left">{$labels.test_exec_by}</th>
		<th style="text-align:center">{$labels.exec_status}</th>
		<th style="text-align:center">{$labels.testcaseversion}</th>
        <th style="text-align:left">{$labels.run_mode}</th>
	</tr>

	{* Table data *}
 	{foreach item=tcv_exec_set from=$gui->execSet}
 		{foreach item=tcv_exec from=$tcv_exec_set}
			{cycle values='#eeeeee,#d0d0d0' assign="bg_color"}
			<tr style="border-top:1px solid black; background-color: {$bg_color}">
				<td>{localize_timestamp ts=$tcv_exec.execution_ts}</td>
				<td>{$tcv_exec.testplan_name|escape}</td>
				<td>
				{if !$tcv_exec.build_is_open}
					<img src="{$smarty.const.TL_THEME_IMG_DIR}/lock.png" title="{$labels.closed_build}">
				{/if}
				{$tcv_exec.build_name|escape}
  				</td>
				{if $gui->displayPlatformCol}<td>{$tcv_exec.platform_name}</td>{/if}
				<td title="{$tcv_exec.tester_first_name|escape} {$tcv_exec.tester_last_name|escape}">
				{$tcv_exec.tester_login|escape}
  				</td>
				{assign var="tc_status_code" value=$tcv_exec.status}
  				<td class="{$tlCfg->results.code_status.$tc_status_code}" style="text-align:center">
  				    {localize_tc_status s=$tcv_exec.status}
  				</td>
				<td  style="text-align:center">{$tcv_exec.tcversion_number}</td>

				<td class="icon_cell" align="center">
				{if $tcv_exec.execution_run_type == $smarty.const.TESTCASE_EXECUTION_TYPE_MANUAL}
      		    <img src="{$smarty.const.TL_THEME_IMG_DIR}/user.png" title="{$labels.execution_type_manual}"
      		             style="border:none" />
       		  	{else}
      		    <img src="{$smarty.const.TL_THEME_IMG_DIR}/bullet_wrench.png" title="{$labels.execution_type_auto}"
      		            style="border:none" />
       		  	{/if}
				</td>
			</tr>

			<tr style="background-color: {$bg_color}">
  			<td colspan="{$my_colspan}">
  				{assign var="cf_value_info" value=$gui->cfexec[$tcv_exec.execution_id]}
          		{$cf_value_info}
  			</td>
  			</tr>

  			{* Attachments *}
			<tr style="background-color: {$bg_color}">
  			<td colspan="{$my_colspan}">
  				{assign var="attach_info" value=$gui->attachments[$tcv_exec.execution_id]}
  				{include file="inc_attachments.tpl"
  				         attach_attachmentInfos=$attach_info
  				         attach_id=$tcv_exec.execution_id
  				         attach_tableName="executions"
  				         attach_show_upload_btn=$attachment_model->show_upload_btn
  				         attach_show_title=$attachment_model->show_title
  				         attach_downloadOnly=1 
  				         attach_tableClassName=null
                   		 attach_inheritStyle=0
                   		 attach_tableStyles=null}
  			</td>
  			</tr>


			
			{if isset($gui->bugs[$tcv_exec.execution_id])}
				<tr style="background-color: {$bg_color}">
	   			<td colspan="{$my_colspan}">
	   				{include file="inc_show_bug_table.tpl"
	   			         bugs_map=$gui->bugs[$tcv_exec.execution_id] can_delete=0 exec_id=$tcv_exec.execution_id}
	   			</td>
	   			</tr>
			{/if}
		{/foreach}
	{/foreach}


</table>
</body>
</html>
