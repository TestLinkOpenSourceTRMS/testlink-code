{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
searchResults.tpl
*}

{include file="inc_head.tpl" openHead='yes'}
{include file="inc_jsCheckboxes.tpl"}
{include file="inc_ext_js.tpl" bResetEXTCss=1}

{foreach from=$gui->tableSet key=idx item=matrix name="initializer"}
  {$tableID="$matrix->tableID"}
  {if $smarty.foreach.initializer.first}
    {$matrix->renderCommonGlobals()}
    {if $matrix instanceof tlExtTable}
        {include file="inc_ext_js.tpl" bResetEXTCss=1}
        {include file="inc_ext_table.tpl"}
    {/if}
  {/if}
  {$matrix->renderHeadSection()}
{/foreach}

</head>

<body>
<h1 class="title">{$gui->pageTitle}</h1>
{include file="search/searchGUI.inc.tpl"}

{if $gui->doSearch}
  <div class="workBack">
  {if $gui->warning_msg == ''}
    {foreach from=$gui->tableSet key=idx item=matrix}
      {$tableIDe="table_$idx"}
      {$matrix->renderBodySection($tableID)}
    {/foreach}
    <br />
    {lang_get s='generated_by_TestLink_on'} {$smarty.now|date_format:$gsmarty_timestamp_format}
  {else}
    <div class="user_feedback">
    <br />
    {$gui->warning_msg}
    </div>
  {/if}
{/if}    
</div>
</body>
</html>