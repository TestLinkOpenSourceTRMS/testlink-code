{* 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * $Id: reqOverview.tpl,v 1.8 2010/10/11 07:57:12 mx-julian Exp $
 *
 * Purpose: List requirements with (or without) Custom Fields Data in an ExtJS Table.
 * See BUGID 3227 for a more detailed description of this feature.
 * 
 * @internal revisions
 *
 *}

{lang_get var="labels" 
          s='testproject_has_no_reqspec, testproject_has_no_requirements, generated_by_TestLink_on,
             all_versions_displayed, latest_version_displayed, show_all_versions_btn, 
             dont_show_all_versions_btn, notes_req_overview, hlp_req_coverage_table'}

{include file="inc_head.tpl" openHead="yes"}

{foreach from=$gui->tableSet key=idx item=matrix name="initializer"}
  {$tableID="table_$idx"}
  {if $smarty.foreach.initializer.first}
    {$matrix->renderCommonGlobals()}
    {if $matrix instanceof tlExtTable}
      {include file="inc_ext_js.tpl" bResetEXTCss=1}
      {include file="inc_ext_table.tpl"}
    {/if}
  {/if}
  {$matrix->renderHeadSection($tableID)}
{/foreach}
</head>

<body>

<h1 class="title">{$gui->pageTitle|escape}</h1>

<div class="workBack" style="overflow-y: auto;">

{if $gui->warning_msg == ''}
  <p>{$labels.generated_by_TestLink_on} {$smarty.now|date_format:$gsmarty_timestamp_format} 
     {if $gui->elapsedSeconds >0} ({$gui->elapsedSeconds} sec) {/if}</p>
  <p><form method="post">
  <input type="checkbox" name="all_versions" value="all_versions"
    {if $gui->all_versions} checked="checked" {/if}
     onclick="this.form.submit();" /> {$labels.show_all_versions_btn}
  <input type="hidden"
         name="all_versions_hidden"
         value="{$gui->all_versions}" />
  </form></p><br/>
  
  {foreach from=$gui->tableSet key=idx item=matrix}
    {$tableID="table_$idx"}
      {$matrix->renderBodySection($tableID)}
  {/foreach}
  
  <br/>
  
  <p>{$labels.notes_req_overview}</p>
  <br/>
  <p>{$labels.hlp_req_coverage_table}</p>
  <br/><br/>
{else}
  <div class="user_feedback">
    {$gui->warning_msg}
    </div>
{/if}

</div>

</body>

</html>
