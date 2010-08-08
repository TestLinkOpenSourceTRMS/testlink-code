{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tcDelete.tpl,v 1.11 2010/08/08 18:37:42 franciscom Exp $
Purpose: smarty template - delete test case in test specification

rev :
    20100808 - franciscom - typo error refresh_tree -> refreshTree
    20080701 - franciscom - Found bug related to javascript:history.go(-1)
                            1. create a new tcversion
                            2. click on delete
                            3. click on no
                            4. A new version is created due to re-post of old data
                            
                            Till a good solution is found -> cancel button removed
    
    20070502 - franciscom - solved problems on delete due to name of local variable
                            equal to name of variable assigned on php page.
    
    20070213 - franciscom - BUGID 0000629: Test Case/Suite - Delete confirmation without Cancel or No option

*}
{lang_get var="labels"
          s='btn_yes_iw2del,btn_no,th_version,th_linked_to_tplan,th_executed,question_del_tc'}
{include file="inc_head.tpl"}

<body>
<h1 class="title">{$gui->main_descr|escape}</h1>

<div class="workBack">
{include file="inc_update.tpl" user_feedback=$gui->user_feedback 
         result=$gui->sqlResult action=$gui->action item="test case"
         refresh=$gui->refreshTree}

{if $gui->sqlResult == ''}
	{if $gui->exec_status_quo != ''}
	    <table class="link_and_exec" >
			<tr>
				<th>{$labels.th_version}</th>
				<th>{$labels.th_linked_to_tplan}</th>
				<th>{$labels.th_executed}</th>
				</tr>
			{foreach from=$gui->exec_status_quo key=testcase_version_id item=on_tplan_status}
				{foreach from=$on_tplan_status key=tplan_id item=status_on_platform}
					{foreach from=$status_on_platform key=platform_id item=status}
				    <tr>
					    <td align="right">{$status.version}</td>
					    <td align="right">{$status.tplan_name|escape}</td>
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
	      action="lib/testcases/tcEdit.php?testcase_id={$gui->testcase_id}&tcversion_id={$gui->tcversion_id}">
		<input type="submit" id="do_delete" name="do_delete" value="{$labels.btn_yes_iw2del}" />
		<input type="button" name="cancel_delete"
		                     onclick='javascript: location.href=fRoot+"lib/testcases/archiveData.php?version_id=undefined&edit=testcase&id={$gui->testcase_id}";'
		                     value="{$labels.btn_no}" />
	</form>
{/if}
</div>
</body>
</html>