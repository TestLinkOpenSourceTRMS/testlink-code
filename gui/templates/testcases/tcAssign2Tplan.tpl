{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: tcAssign2Tplan.tpl,v 1.8 2010/11/06 11:42:47 amkhullar Exp $
Purpose: manage assignment of A test case version to N test plans 
         while working on test specification 
 
rev: BUGID 2378
    
*}
{lang_get var='labels' 
          s='testproject,test_plan,th_id,please_select_one_testplan,platform,btn_cancel,
             cancel,warning,version,btn_add,testplan_usage,no_test_plans,design,
             execution_history'}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_jsCheckboxes.tpl"}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
//BUGID 3943: Escape all messages (string)
	var check_msg="{$labels.please_select_one_testplan|escape:'javascript'}";
	var alert_box_title = "{$labels.warning|escape:'javascript'}";
{literal}

function check_action_precondition(container_id,action)
{
	if(checkbox_count_checked(container_id) <= 0)
	{
		alert_message(alert_box_title,check_msg);
		return false;
	}
	return true;
}
</script>
{/literal}


</head>
<body>

<h1 class="title"> {$gui->pageTitle|escape} 
	{*  {include file="inc_help.tpl" helptopic="hlp_planTcModified" show_help_icon=true} *}
</h1>

<div class="workBack">
<h1 class="title">{$gui->mainDescription}</h1>

{if $gui->tplans}
<form method="post" action="lib/testcases/tcEdit.php?testcase_id={$gui->tcase_id}&tcversion_id={$gui->tcversion_id}">

<br />
<img class="clickable" src="{$smarty.const.TL_THEME_IMG_DIR}/history_small.png"
      onclick="javascript:openExecHistoryWindow({$gui->tcase_id});"
      title="{$labels.execution_history}" />
<img class="clickable" src="{$smarty.const.TL_THEME_IMG_DIR}/edit_icon.png"
     onclick="javascript:openTCaseWindow({$gui->tcase_id});"
     title="{$labels.design}" />
{$gui->tcaseIdentity|escape}
<br /><br />
{$labels.testplan_usage}:
<div id='checkboxes'>
<table class="simple_tableruler" style="width:50%">
  <th>&nbsp;</th><th>{$labels.version}</th><th>{$labels.test_plan}</th><th>{$labels.platform}</th>
  {foreach from=$gui->tplans item=link2tplan_platform}
    {foreach from=$link2tplan_platform item=link2tplan key=platform_id}
      <tr>
      <td class="clickable_icon">
          <input type="checkbox" id="add2tplanid[{$link2tplan.id}][{$platform_id}]" 
                                 name="add2tplanid[{$link2tplan.id}][{$platform_id}]"
          {if !$link2tplan.draw_checkbox} checked='checked' disabled='disabled' {/if} > 
      </td>
      <td style="width:10%;text-align:center;">{$link2tplan.version}</td>
      <td>{$link2tplan.name|escape}</td>
      <td>{$link2tplan.platform|escape}</td>
      </tr>
    {/foreach}

  {/foreach}
</table>
</div>

{if $gui->can_do}
<input type="hidden" id="doAction" name="doAction" value="doAdd2testplan" />
<input type="submit" id="add2testplan"  name="add2testplan" value="{$labels.btn_add}"       
       onclick="return check_action_precondition('checkboxes','default');" />
{/if}
<input type="button" name="cancel" value="{$labels.btn_cancel}" 
  			                   onclick="javascript:history.back();" />  
</form>
{else}
  {$labels.no_test_plans}
{/if}
</div>