{* 
 Testlink Open Source Project - http://testlink.sourceforge.net/ 
 $Id: metricsDashboard.tpl,v 1.19 2010/10/17 17:44:24 mx-julian Exp $     
 Purpose: smarty template - main page / site map                 

 rev:
  20110303 - Julian - added more information to test project report
  20101014 - Julian - BUGID 3893 - Extended metrics dashboard
  20101012 - Julian - show "show metrics only for active test plans" checkbox even if there is no resultset. 
                      This is required if there are no active test plans at all
  20100917 - Julian - BUGID 3724 - checkbox to show all/active test plans
                                 - use of exttable
  20090919 - franciscom - added plaftorm information
*}
{lang_get var="labels"
          s="generated_by_TestLink_on,testproject,test_plan,platform,show_only_active,
             info_metrics_dashboard,test_plan_progress,project_progress, info_metrics_dashboard_progress"}
{include file="inc_head.tpl" openHead='yes'}
{include file="inc_ext_js.tpl" bResetEXTCss=1}
{foreach from=$gui->tableSet key=idx item=matrix name="initializer"}
  {assign var=tableID value=$matrix->tableID}
  {if $smarty.foreach.initializer.first}
    {$matrix->renderCommonGlobals()}
    {if $matrix instanceof tlExtTable}
        {include file="inc_ext_table.tpl"}
    {/if}
  {/if}
  {$matrix->renderHeadSection()}
{/foreach}

{assign var=tplan_metric value=$gui->tplan_metrics}
<script type="text/javascript">
Ext.onReady(function() {ldelim}
	{foreach key=key item=value from=$gui->project_metrics}
    new Ext.ProgressBar({ldelim}
        text:'&nbsp;&nbsp;{lang_get s=$value.label_key}: {$value.value}% [{$tplan_metric.total.$key}/{$tplan_metric.total.active}]',
        width:'400',
        cls:'left-align',
        renderTo:'{$key}',
        value:'{$value.value/100}'
    {rdelim});
    {/foreach}
{rdelim});
</script>

</head>

<body>
<h1 class="title">{$labels.testproject} {$smarty.const.TITLE_SEP} {$gui->tproject_name|escape}</h1>
<div class="workBack">

<p><form method="post">
<input type="checkbox" name="show_only_active" value="show_only_active"
       {if $gui->show_only_active} checked="checked" {/if}
       onclick="this.form.submit();" /> {$labels.show_only_active}
<input type="hidden"
       name="show_only_active_hidden"
       value="{$gui->show_only_active}" />
</form></p><br/>

{if $gui->warning_msg == ''}
	<h2>{$labels.project_progress}</h2>
	<br>
	{foreach from=$gui->project_metrics key=key item=metric}
		<div id="{$key}"></div>
		{if $key == "executed"}
		<br />
		{/if}
	{/foreach}
	<br />
	<p class="italic">{$labels.info_metrics_dashboard_progress}</p>
	<br />
	
	<h2>{$labels.test_plan_progress}</h2>
	<br />
	{foreach from=$gui->tableSet key=idx item=matrix}
		{assign var=tableID value=table_$idx}
   		{$matrix->renderBodySection($tableID)}
	{/foreach}
	<br />
	<p class="italic">{$labels.info_metrics_dashboard}</p>
	<br />
	{$labels.generated_by_TestLink_on} {$smarty.now|date_format:$gsmarty_timestamp_format}
{else}
	<div class="user_feedback">
    {$gui->warning_msg}
    </div>
{/if}
</div> 
</body>
</html>
