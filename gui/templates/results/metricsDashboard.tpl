{* 
Testlink Open Source Project - http://testlink.sourceforge.net/ 
@filesource  metricsDashboard.tpl
@internal revisions
@since 1.9.10                
*}
{lang_get var="labels"
          s="generated_by_TestLink_on,testproject,test_plan,platform,show_only_active,
             info_metrics_dashboard,test_plan_progress,project_progress, info_metrics_dashboard_progress"}

{include file="inc_head.tpl" openHead='yes'}
{include file="inc_ext_js.tpl" bResetEXTCss=1}
{foreach from=$gui->tableSet key=idx item=matrix name="initializer"}
  {if $smarty.foreach.initializer.first}
    {$matrix->renderCommonGlobals()}
    {if $matrix instanceof tlExtTable}
        {include file="inc_ext_table.tpl"}
    {/if}
  {/if}
  {$matrix->renderHeadSection()}
{/foreach}

{$tplan_metric=$gui->tplan_metrics}
<script type="text/javascript">
Ext.onReady(function() {ldelim}
	{foreach key="key" item="value" from=$gui->project_metrics}
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
{$tlImages.toggle_direct_link} &nbsp;&nbsp;
<div class="direct_link" style='display:none'>
{if $gui->direct_link_ok}  
  <a href="{$gui->direct_link}" target="_blank">{$gui->direct_link}</a>
{else}
  {$gui->direct_link}
{/if}
</div>
<p><form method="post">
<input type="checkbox" name="show_only_active" value="show_only_active"
       {if $gui->show_only_active} checked="checked" {/if}
       onclick="this.form.submit();" /> {$labels.show_only_active}
<input type="hidden"  name="show_only_active_hidden"  value="{$gui->show_only_active}" />

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
		{$tableID="table_$idx"}
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