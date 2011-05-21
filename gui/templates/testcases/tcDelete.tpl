{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource	tcDelete.tpl
Purpose: smarty template - delete test case in test specification

@internal revisions
20100908 - franciscom - improvements on platform name display
20100808 - franciscom - typo error refresh_tree -> refreshTree
*}
{lang_get var="labels"
          s='btn_yes_iw2del,btn_no,th_version,th_linked_to_tplan,title_delete_testcases,
             th_executed,question_del_tc,platform'}
{include file="inc_head.tpl"}

<body>
<h1 class="title">{$gui->main_descr|escape}</h1>

<div class="workBack">
{include file="inc_update.tpl" user_feedback=$gui->user_feedback 
         result=$gui->sqlResult action=$gui->action item="test case"
         refresh=$gui->refreshTree}

{if $gui->sqlResult == ''}
	{if $gui->delete_mode == 'single'}
	  {if $gui->exec_status_quo != ''}
	      <table class="simple" >
	  		<tr>
	  			<th>{$labels.th_version}</th>
	  			<th>{$labels.th_linked_to_tplan}</th>
	  			{if $gui->display_platform}<th>{$labels.platform}</th> {/if}
	  			<th>{$labels.th_executed}</th>
	  			</tr>
	  		{foreach from=$gui->exec_status_quo key=testcase_version_id item=on_tplan_status}
	  			{foreach from=$on_tplan_status key=tplan_id item=status_on_platform}
	  				{foreach from=$status_on_platform key=platform_id item=status}
	  			    <tr>
	  				    <td style="width:4%;text-align:right;">{$status.version}</td>
	  				    <td align="left">{$status.tplan_name|escape}</td>
	  			      {if $gui->display_platform}
	  			        <td align="left">{$status.platform_name|escape}</td>
	  			      {/if}
	  				    <td style="width:4%;text-align:center;">{if $status.executed != ""}<img src="{$smarty.const.TL_THEME_IMG_DIR}/apply_f2_16.png" />{/if}</td>
	  				  </tr>
	  			  {/foreach}
	  			{/foreach}
	  		{/foreach}
	      </table>
      	{$gui->delete_message}
    {/if}
    
	  <p>{$labels.question_del_tc}</p>
	  <form method="post" 
	        action="lib/testcases/tcEdit.php?tproject_id={$gui->tproject_id}&testcase_id={$gui->testcase_id}&tcversion_id={$gui->tcversion_id}">
	  	<input type="submit" id="do_delete" name="do_delete" value="{$labels.btn_yes_iw2del}" />
	  	<input type="button" name="cancel_delete"
	  	       onclick='javascript: location.href=fRoot+"lib/testcases/archiveData.php?tproject_id={$gui->tproject_id}&version_id=undefined&edit=testcase&id={$gui->testcase_id}";'
	  	       value="{$labels.btn_no}" />
	  </form>
  
  
  {else}
	  {if $gui->exec_status_quo != ''}
	      <table class="simple" >
	  		<tr>
	  			<th>&nbsp;</th>
	  			<th>{$labels.th_version}</th>
	  			<th>{$labels.th_linked_to_tplan}</th>
	  			<th>{$labels.th_executed}</th>
	  			</tr>
	  		{foreach from=$gui->exec_status_quo key=testcase_version_id item=on_tplan_status}
	  			{foreach from=$on_tplan_status key=tplan_id item=status_on_platform}
	  				{foreach from=$status_on_platform key=platform_id item=status}
	  			    <tr>
	  				    <td align="right">{$status.version}</td>
	  				    <td align="left">{$status.tplan_name|escape}</td>
	  				    <td align="center">{if $status.executed neq ""}<img src="{$smarty.const.TL_THEME_IMG_DIR}/apply_f2_16.png" />{/if}</td>
	  				  </tr>
	  			  {/foreach}
	  			{/foreach}
	  		{/foreach}
	      </table>
      	{$gui->delete_message}
    {/if}
    
	  <p>{$labels.question_del_tc}</p>
	  <form method="post" 
	        action="lib/testcases/tcEdit.php?tproject_id={$gui->tproject_id}&testcase_id={$gui->testcase_id}&tcversion_id={$gui->tcversion_id}">
	  	<input type="submit" id="do_delete" name="do_delete" value="{$labels.btn_yes_iw2del}" />
	  	<input type="button" name="cancel_delete"
	  	       onclick='javascript: location.href=fRoot+"lib/testcases/archiveData.php?tproject_id={$gui->tproject_id}&version_id=undefined&edit=testcase&id={$gui->testcase_id}";'
               value="{$labels.btn_no}" />
	  </form>
  
  {/if}	  
{/if}
</div>
</body>
</html>