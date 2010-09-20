{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
Purpose: show results for requirement specification search.

rev:
  20100920 - Julian - BUGID 3793 - use exttable to display search results
*}

{include file="inc_head.tpl" openHead='yes'}
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
<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>
{include file="inc_ext_js.tpl" css_only=1}

</head>

{assign var=this_template_dir value=$smarty.template|dirname}
{lang_get var='labels' 
          s='no_records_found,other_versions,version,generated_by_TestLink_on'}

<body onLoad="viewElement(document.getElementById('other_versions'),false)">
<h1 class="title">{$gui->pageTitle}</h1>

<div class="workBack">
{if $gui->warning_msg == ''}
  {foreach from=$gui->tableSet key=idx item=matrix}
    {assign var=tableID value=table_$idx}
    {$matrix->renderBodySection($tableID)}
  {/foreach}
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
