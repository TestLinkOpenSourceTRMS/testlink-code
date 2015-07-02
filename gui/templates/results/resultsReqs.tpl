{* 
TestLink Open Source Project - http://testlink.sourceforge.net/

Purpose: report REQ coverage 
Author : Martin Havlat 

@filesource resultsReqs.tpl


@internal revisions
@since 1.9.13

*}
{lang_get var='labels'
          s='title_result_req_testplan, show_only_finished_reqs, caption_nav_settings,
             generated_by_TestLink_on, info_resultsReqs, platform, status, btn_apply,build,
             info_resultsReqsProgress, title_resultsReqsProgress, title_resultsReqs'}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_ext_js.tpl" bResetEXTCss=1}

{foreach from=$gui->tableSet key=idx item=matrix name="initializer"}
  {$tableID="table_$idx"}
  {if $smarty.foreach.initializer.first}
    {$matrix->renderCommonGlobals()}
    {if $matrix instanceof tlExtTable}
      {include file="inc_ext_table.tpl"}
    {/if}
  {/if}
  {$matrix->renderHeadSection($tableID)}
{/foreach}

{$total_reqs=$gui->total_reqs}

<script type="text/javascript">
Ext.onReady(function() {
  {foreach key=key item=value from=$gui->summary}
    {$label=$value.label}
    {$count=$value.count}
    {* only show progress bar if at least 1 item exists for this status *}
    {if $count != 0}
        new Ext.ProgressBar({
            text:'&nbsp;&nbsp;{$label}: {$count} of {$total_reqs}',
            width:'400',
            cls:'left-align',
            renderTo:'{$key}',
            value:'{$count/$total_reqs}'
            });
    {/if}
  {/foreach}
});
</script>

</head>

<body>
<h1 class="title">{$gui->pageTitle|escape}</h1>
<div class="workBack" style="overflow-y: auto;">
{include file="inc_result_tproject_tplan.tpl" 
         arg_tproject_name=$gui->tproject_name arg_tplan_name=$gui->tplan_name}
<br /><p>
<br />
<p><form method="post">
<table>
  {if $gui->platforms}
  <tr>
    <td>
    {$labels.platform}
    </td>
    <td>
    <select name="platform"> {* onchange="this.form.submit()"> *}
    {html_options options=$gui->platforms
                  selected=$gui->selected_platform}
    </select>
    </td>
  </tr>
  {/if}

  {if $gui->builds}
  <tr>
    <td>
    {$labels.build}
    </td>
    <td>
    <select name="build"> {*  onchange="this.form.submit()"> *}
    {html_options options=$gui->builds
                  selected=$gui->selected_build}
    </select>
    </td>
  </tr>
  {/if}

  <tr>
    <td>{$labels.status}</td>
    <td> <select id="states_to_show" name="states_to_show[]"  multiple="multiple" size="4" >
         {html_options options=$gui->states_to_show->items  selected=$gui->states_to_show->selected}
         </select>
    </td>
  </tr>
  <tr>
    <td>
      <input type="submit" name="send_states_to_show"  value="{$labels.btn_apply}" />       
    </td>
  </tr>
</table>
</form></p><br/>

{if $gui->warning_msg == ''}

  <h2>{$labels.title_resultsReqsProgress}</h2>
  <br />

  {foreach from=$gui->summary key=key item=metric}
    <div id="{$key}"></div>
  {/foreach}

  <br />
    <p class="italic">{$labels.info_resultsReqsProgress}</p>
  <br />
  
  <h2>{$labels.title_resultsReqs}</h2>
  <br />
  {foreach from=$gui->tableSet key=idx item=matrix}
    {$tableID="table_$idx"}
    {$matrix->renderBodySection($tableID)}
  {/foreach}
  
  <br />
    <p class="italic">{$labels.info_resultsReqs}</p>
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