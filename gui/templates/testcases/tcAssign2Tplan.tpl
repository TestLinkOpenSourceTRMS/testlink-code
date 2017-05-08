{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource  tcAssign2Tplan.tpl
Purpose: manage assignment of A test case version to N test plans 
         while working on test specification 
 
    
*}
{lang_get var='labels' 
          s='testproject,test_plan,th_id,please_select_one_testplan,platform,btn_cancel,
             cancel,warning,version,btn_add,testplan_usage,no_test_plans,design,
             execution_history'}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_jsCheckboxes.tpl"}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
var check_msg="{$labels.please_select_one_testplan|escape:'javascript'}";
var alert_box_title = "{$labels.warning|escape:'javascript'}";

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

{$ll = '[25, 50, 75, -1], [25, 50, 75, "All"]'}
{include file="DataTables.inc.tpl" DataTablesOID="item_view" DataTableslengthMenu=$ll}

</head>
<body>

<h1 class="title"> {$gui->pageTitle|escape}</h1>

<div class="workBack">
<h1 class="title">{$gui->mainDescription}</h1>

{if $gui->tplans}
<form method="post" action="{$basehref}lib/testcases/tcEdit.php?testcase_id={$gui->tcase_id}&tcversion_id={$gui->tcversion_id}">
  <div>
  <img class="clickable" src="{$tlImages.history_small}" onclick="javascript:openExecHistoryWindow({$gui->tcase_id});"
        title="{$labels.execution_history}" />
  <img class="clickable" src="{$tlImages.edit_icon}" onclick="javascript:openTCaseWindow({$gui->tcase_id});"
       title="{$labels.design}" />
  {$gui->tcaseIdentity|escape}
  </div>
<br>
<h1>{$labels.testplan_usage}</h1>

<div id='checkboxes'>
 <table id="item_view" class="simple_tableruler sortable">
  <tr>
  <th>&nbsp;</th><th>{$labels.version}</th><th>{$labels.test_plan}</th><th>{$labels.platform}</th>
  </tr>
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


<input type="button" name="cancel" value="{$labels.btn_cancel}" onclick="javascript:{$gui->cancelActionJS};" />  
</form>

{else}
  {$labels.no_test_plans}
{/if}
</div>