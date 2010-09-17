{* 
 Testlink Open Source Project - http://testlink.sourceforge.net/ 
 $Id: metricsDashboard.tpl,v 1.11 2010/09/17 13:21:43 mx-julian Exp $     
 Purpose: smarty template - main page / site map                 

 rev:
  20100917 - Julian - BUGID 3724 - checkbox to show all/active test plans
                                 - use of exttable
  20090919 - franciscom - added plaftorm information
*}
{lang_get var="labels"
          s="generated_by_TestLink_on,testproject,test_plan,th_total_tc,th_active_tc,th_executed_tc,
             th_executed_vs_active,th_executed_vs_total,platform,show_only_active"}
{include file="inc_head.tpl"}
{foreach from=$gui->tableSet key=idx item=matrix name="initializer"}
  {assign var=tableID value=$matrix->tableID}
  {if $smarty.foreach.initializer.first}
    {$matrix->renderCommonGlobals()}
    {if $matrix instanceof tlExtTable}
        {include file="inc_ext_js.tpl" bResetEXTCss=1}
        {include file="inc_ext_table.tpl"}
    {/if}
  {/if}
  {$matrix->renderHeadSection()}
{/foreach}

<body>
<div class="workBack">
<h1 class="title">{$labels.testproject} {$smarty.const.TITLE_SEP} {$gui->tproject_name|escape}</h1>

{if $gui->warning_msg == ''}
	<p><form method="post">
	<input type="checkbox" name="show_only_active" value="show_only_active"
	       {if $gui->show_only_active} checked="checked" {/if}
	       onclick="this.form.submit();" /> {$labels.show_only_active}
	<input type="hidden"
	       name="show_only_active_hidden"
	       value="{$gui->show_only_active}" />
	</form></p><br/>
	
	{foreach from=$gui->tableSet key=idx item=matrix}
		{assign var=tableID value=table_$idx}
   		{$matrix->renderBodySection($tableID)}
	{/foreach}
{else}
	<div class="user_feedback">
    {$gui->warning_msg}
    </div>
{/if}

<br />
{$labels.generated_by_TestLink_on} {$smarty.now|date_format:$gsmarty_timestamp_format}
</div> 
</body>
</html>
